<?php

namespace YourNamespace\Entity;

class Single implements SingleInterface
{
    private array $properties = ['key1' => 'value1', 'key2' => 'value2'];

    public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {
            if (array_key_exists($key, $this->properties)) {
                $this->properties[$key] = $value;
            }
        }
    }

    public function __get($key): string
    {
        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key];
        }

        throw new \InvalidArgumentException("Property '{$key}' does not exist.");
    }

    public function __set($key, $value): void
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Value must be a string.");
        }

        $this->properties[$key] = $value;
    }

    /**
     * TODO: remove magic method
     */
    public function __call($name, $arguments)
    {
        // Get the first 3 characters of the method name
        $methodType = substr($name, 0, 3);
        // Get the property name by removing the first 3 characters from the method name
        $propertyName = lcfirst(substr($name, 3));

        if ($methodType === 'get') {
            return $this->__get($propertyName);
        } elseif ($methodType === 'set') {
            return $this->__set($propertyName, $arguments[0]);
        } else {
            throw new \Exception("Method $name does not exist");
        }
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}
