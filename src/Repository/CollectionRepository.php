<?php

namespace QuadLayers\WP_Orm\Repository;

use QuadLayers\WP_Orm\Entity\SingleInterface;
use QuadLayers\WP_Orm\Mapper\CollectionMapperInterface;
use QuadLayers\WP_Orm\Entity\Collection;

class CollectionRepository implements CollectionRepositoryInterface
{
    private CollectionMapperInterface $mapper;
    private string $primaryKey;  // Default primary key
    private string $table;
    /**
     * @var Collection[]
     */
    private ?array $cache = null;

    public function __construct(CollectionMapperInterface $mapper, string $table, string $primaryKey)
    {
        $this->mapper = $mapper;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    private function getPrimaryKeyValue(SingleInterface $entity)
    {
        $primaryKey = $this->primaryKey;

        if (!property_exists($entity, $primaryKey)) {
            throw new \InvalidArgumentException("Primary key '{$primaryKey}' does not exist in the entity.");
        }

        return $entity->$primaryKey;
    }

    private function getAutoIncrement(): int
    {
        $collection = $this->findAll();

        if (empty($collection)) {
            return 0;
        }

        $maxPrimaryKey = max(array_map(fn($entity) => $entity->{$this->primaryKey}, $collection));

        return $maxPrimaryKey + 1;
    }

    private function getEntityIndex($primaryKeyValue): ?int
    {
        $collection = $this->findAll();

        if (!$collection) {
            return null;
        }

        foreach ($collection as $index => $entity) {
            if ($this->getPrimaryKeyValue($entity) === $primaryKeyValue) {
                return $index;
            }
        }

        return null;
    }

    public function findAll(): ?array
    {

        if ($this->cache !== null) {
            return $this->cache;
        }

        $data = get_option($this->table, null);

        $this->cache = $data ? array_map([$this->mapper, 'toEntity'], $data) : null;

        return $this->cache;
    }

    public function saveAll(array $collection): bool
    {
        $this->cache = $collection;
        $data = array_map([$this->mapper, 'toArray'], $collection);
        return update_option($this->table, $data);
    }

    public function deleteAll(): bool
    {
        $this->cache = null;
        return delete_option($this->table);
    }

    public function find($primaryKeyValue): ?SingleInterface
    {
        $index = $this->getEntityIndex($primaryKeyValue);

        if ($index === null) {
            return false;
        }

        $collection = $this->findAll();

        if (!isset($collection[$index])) {
            return false;
        }

        return $collection[$index];
    }

    public function create(array $data): ?SingleInterface
    {

        if (!isset($data[$this->primaryKey])) {
            $data[$this->primaryKey] = $this->getAutoIncrement();
        }

        $entity = $this->mapper->toEntity($data);

        $primaryKeyValue = $this->getPrimaryKeyValue($entity);

        $found = $this->getEntityIndex($primaryKeyValue);

        if ($found !== null) {
            return null;
        }

        // Get the collection
        $collection = $this->findAll() ?? [];

        // Add the entity to the collection
        array_push($collection, $entity);

        if (!$this->saveAll($collection)) {
            return null;
        }

        // Save the updated collection
        return $entity;
    }

    public function update($primaryKeyValue, array $data): ?SingleInterface
    {
        $index = $this->getEntityIndex($primaryKeyValue);

        if ($index === null) {
            return false;
        }

        $collection = $this->findAll();


        if (!isset($collection[$index])) {
            return false;
        }

        $entity = $collection[$index];
        $updatedData = array_merge($entity->getProperties(), $data);
        $updatedEntity = $this->mapper->toEntity($updatedData);

        // Update the entity in the collection
        $collection[$primaryKeyValue] = $updatedEntity;
        // Save the updated collection
        if (!$this->saveAll($collection)) {
            return null;
        }

        return $updatedEntity;
    }

    public function delete($primaryKeyValue): bool
    {
        $index = $this->getEntityIndex($primaryKeyValue);

        if ($index === null) {
            return false;
        }

        $collection = $this->findAll();

        if (!isset($collection[$index])) {
            return false;
        }

        // Remove the entity from the collection
        unset($collection[$index]);
        // Save the updated collection
        return $this->saveAll($collection);
    }
}