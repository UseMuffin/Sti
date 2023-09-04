<?php
namespace Muffin\Sti\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CooksFixture extends TestFixture
{
    public string $table = 'sti_cooks';

    public array $records = [
        ['name' => 'The Chef', 'type' => 'chef', 'age' => 50],
        ['name' => 'The Baker', 'type' => 'baker', 'age' => 40],
        ['name' => 'The Assistant Chef', 'type' => 'assistant_chef', 'age' => 20],
    ];
}
