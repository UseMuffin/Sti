<?php
namespace Muffin\Sti\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Muffin\Sti\TestApp\Model\Entity\Baker;
use Muffin\Sti\TestApp\Model\Table\CooksTable;

class StiBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Muffin/Sti.Cooks',
        'plugin.Muffin/Sti.Utensils',
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Table = TableRegistry::get('Cooks', ['className' => CooksTable::class]);
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
    }

    public function testSave()
    {
        $entity = new Baker(['name' => 'foo']);
        $this->Table->save($entity);
        $this->assertEquals('baker', $entity['type']);
    }

    public function testBeforeMarshal()
    {
        $entity = $this->Table->newEntity(['name' => 'foo', 'type' => 'baker']);
        $this->assertInstanceOf('Muffin\Sti\TestApp\Model\Entity\Baker', $entity);
    }

    public function testBeforeFind()
    {
        $results = $this->Table->find()->toArray();
        $this->assertInstanceOf('Muffin\Sti\TestApp\Model\Entity\Chef', $results[0]);
        $this->assertInstanceOf('Muffin\Sti\TestApp\Model\Entity\Baker', $results[1]);
        $this->assertEquals('Bakers', $results[1]->source());
    }

    public function testValidation()
    {
        $expected = ['name' => ['_empty' => 'chef']];

        $entity = $this->Table->newChef(['name' => null]);
        $this->assertEquals($expected, $entity->errors());

        $entity = $this->Table->newEntity(['name' => null, 'type' => 'chef']);
        $this->assertEquals($expected, $entity->errors());

        TableRegistry::clear();
        $table = TableRegistry::get('Chefs', ['className' => 'Muffin\Sti\TestApp\Model\Table\ChefsTable']);
        $entity = $table->newEntity(['name' => null]);
        $this->assertEquals($expected, $entity->errors());
    }

    public function testFindWithAssociation()
    {
        $results = $this->Table->find()->contain('Utensils')->toArray();
        $this->assertInstanceOf('Muffin\Sti\TestApp\Model\Entity\Spoon', $results[0]['utensils'][0]);
        $this->assertInstanceOf('Muffin\Sti\TestApp\Model\Entity\Electronic', $results[0]['utensils'][1]);
    }
}
