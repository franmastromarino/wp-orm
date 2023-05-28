<?php

namespace QuadLayers\WP_Orm\Tests;

class Settings extends \QuadLayers\WP_Orm\Entity\Single
{
    // public string $key1 = 'default_value_1';
    // public string $key2 = 'default_value_2';

    public function getSchema(): array
    {
        return [
            'key1' => [
                'type' => 'string',
                'default' => 'default_value_1',
            ],
            'key2' => [
                'type' => 'string',
                'default' => 'default_value_2',
            ],
        ];
    }
}
