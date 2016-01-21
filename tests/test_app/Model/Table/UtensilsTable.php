<?php
namespace Muffin\Sti\TestApp\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UtensilsTable extends Table
{
    public function initialize(array $config)
    {
        $this->table('sti_utensils');
        $this->addBehavior('Muffin/Sti.Sti', [
            'typeMap' => [
                'spoon' => 'Muffin\Sti\TestApp\Model\Entity\Spoon',
                'electronic' => 'Muffin\Sti\TestApp\Model\Entity\Electronic',
            ]
        ]);
        $this->entityClass('Muffin\Sti\TestApp\Model\Entity\Spoon');
    }
}
