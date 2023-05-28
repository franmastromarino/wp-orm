<?php

namespace QuadLayers\WP_Orm\Builder;

use QuadLayers\WP_Orm\Entity\CollectionFactory;
use QuadLayers\WP_Orm\Mapper\CollectionMapper;
use QuadLayers\WP_Orm\Repository\CollectionRepository;

class CollectionRepositoryBuilder
{
    private string $table;
    private string $primaryKey;
    private string $entityClass;

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function setEntity(string $entityClass): self
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException("Class '{$entityClass}' does not exist.");
        }

        $this->entityClass = $entityClass;
        return $this;
    }

    public function setPrimaryKey(string $primaryKey): self
    {

        // Check if the entity class has the primaryKey property
        if (!property_exists($this->entityClass, $primaryKey)) {
            throw new \InvalidArgumentException("Class '{$this->entityClass}' does not have the property '{$primaryKey}'.");
        }

        $this->primaryKey = $primaryKey;

        return $this;
    }

    public function getRepository(): CollectionRepository
    {
        $factory = new CollectionFactory($this->entityClass);
        $mapper = new CollectionMapper($factory);
        return new CollectionRepository($mapper, $this->table, $this->primaryKey);
    }
}