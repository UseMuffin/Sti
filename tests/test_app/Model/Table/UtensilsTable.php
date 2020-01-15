<?php
declare(strict_types=1);

namespace Muffin\Sti\TestApp\Model\Table;

use Cake\ORM\Table;

class UtensilsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('sti_utensils');
        $this->addBehavior('Muffin/Sti.Sti', [
            'typeMap' => [
                'spoon' => 'Muffin\Sti\TestApp\Model\Entity\Spoon',
                'electronic' => 'Muffin\Sti\TestApp\Model\Entity\Electronic',
            ],
        ]);
        $this->setEntityClass('Muffin\Sti\TestApp\Model\Entity\Spoon');
    }
}
