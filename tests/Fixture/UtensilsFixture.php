<?php
namespace Muffin\Sti\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UtensilsFixture extends TestFixture
{
    public string $table = 'sti_utensils';

    public array $records = [
        ['sti_cook_id' => 1, 'name' => 'Caviar', 'type' => 'spoon'],
        ['sti_cook_id' => 2, 'name' => 'Dessert', 'type' => 'spoon'],
        ['sti_cook_id' => 3, 'name' => 'Bouillon', 'type' => 'spoon'],
        ['sti_cook_id' => 1, 'name' => 'Stove', 'type' => 'electronic'],
        ['sti_cook_id' => 2, 'name' => 'Stove', 'type' => 'electronic'],
        ['sti_cook_id' => 3, 'name' => 'Stove', 'type' => 'electronic'],
    ];
}
