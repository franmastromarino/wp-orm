<?php

namespace QuadLayers\WP_Orm\Entity;

use QuadLayers\WP_Orm\Validator\SchemaValidator;
use QuadLayers\WP_Orm\Entity\Single;

class SingleFactory
{
    private Single $entity;
    private SchemaValidator $validator;

    public function __construct(string $entityClass)
    {
        $this->entity = new $entityClass();
        $this->validator = new SchemaValidator($this->entity->getSchema());
    }

    public function create(array $data): Single
    {
        $data = $this->validator->getSanitizedData($data);
        $defaults = $this->validator->getDefaults();

        $this->entity->defaults = $defaults;

        // Use reflection to get the properties of the class
        $reflection = new \ReflectionClass($this->entity);

        // Loop through each data item
        foreach ($data as $property => $value) {
            // Check if the entity has the property and if the value is of the same type
            if ($reflection->hasProperty($property) && gettype($value) === gettype($this->entity->$property)) {
                // Set the value of the property
                $this->entity->$property = $value;
            }
        }

        return $this->entity;
    }
}
