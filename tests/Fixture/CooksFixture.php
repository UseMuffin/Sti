<?php
namespace Muffin\Sti\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CooksFixture extends TestFixture
{
    public $table = 'sti_cooks';

    public $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => true],
        'name' => ['type' => 'string'],
        'type' => ['type' => 'string'],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ]
    ];

    public $records = [
        ['name' => 'The Chef', 'type' => 'chef'],
        ['name' => 'The Baker', 'type' => 'baker'],
        ['name' => 'The Assistant Chef', 'type' => 'assistant_chef']
    ];
}
