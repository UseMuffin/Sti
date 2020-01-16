<?php
namespace Muffin\Sti\Test\TestCase\Model\Behavior;

use Cake\ORM\Locator\TableLocator;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Muffin\Sti\TestApp\Model\Entity\Baker;
use Muffin\Sti\TestApp\Model\Table\CooksTable;

class StiBehaviorTest extends TestCase
{
    /**
     * @var \Muffin\Sti\TestApp\Model\Table\CooksTable|null
     */
    public $Table = null;
    public $fixtures = [
        'plugin.Muffin/Sti.Cooks',
        'plugin.Muffin/Sti.Utensils',
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Table = TableRegistry::getTableLocator()->get('Cooks', ['className' => CooksTable::class]);
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::getTableLocator()->clear();
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
        $this->assertEquals('Bakers', $results[1]->getSource());
    }

    public function testValidation()
    {
        $expected = ['name' => ['_empty' => 'chef']];

        /** @var \Muffin\Sti\TestApp\Model\Entity\Chef $entity */
        $entity = $this->Table->newChef(['name' => null]);
        $this->assertEquals($expected, $entity->getErrors());

        $entity = $this->Table->newEntity(['name' => null, 'type' => 'chef']);
        $this->assertEquals($expected, $entity->getErrors());

        TableRegistry::getTableLocator()->clear();
        $table = TableRegistry::getTableLocator()->get('Chefs', ['className' => 'Muffin\Sti\TestApp\Model\Table\ChefsTable']);
        $entity = $table->newEntity(['name' => null]);
        $this->assertEquals($expected, $entity->getErrors());
    }

    public function testFindWithAssociation()
    {
        $results = $this->Table->find()->contain('Utensils')->toArray();
        $this->assertArrayNotHasKey('age', $results[0]->toArray());
        $this->assertEquals('Chef', $results[0]->role);
        $this->assertInstanceOf('Muffin\Sti\TestApp\Model\Entity\Spoon', $results[0]['utensils'][0]);
        $this->assertInstanceOf('Muffin\Sti\TestApp\Model\Entity\Electronic', $results[0]['utensils'][1]);
    }

    public function testFindWithHydrateFalse()
    {
        $results = $this->Table->find()->contain('Utensils')->enableHydration(false)->toArray();
        $this->assertTrue(is_array($results[0]));
        $this->assertTrue(is_array($results[0]['utensils'][0]));
    }
}
