<?php

namespace QuadLayers\WP_Orm\Entity;

interface SingleInterface
{
    // public function __construct(array $data);
    public function __get($key);
    public function __set($key, $value);
    public function getProperties(): array;
    public function getDefaults(): array;
    public function getSchema(): array;
}
