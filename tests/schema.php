<?php
declare(strict_types=1);

return [
    'sti_cooks' => [
        'columns' => [
            'id' => ['type' => 'integer', 'autoIncrement' => true],
            'name' => ['type' => 'string'],
            'type' => ['type' => 'string'],
            'age' => ['type' => 'integer'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'sti_utensils' => [
        'columns' => [
            'id' => ['type' => 'integer', 'autoIncrement' => true],
            'sti_cook_id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
            'type' => ['type' => 'string'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
];
