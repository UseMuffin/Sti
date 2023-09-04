<?php
declare(strict_types=1);

namespace Muffin\Sti\TestApp\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use Muffin\Sti\Model\Entity\StiAwareTrait;

class Chef extends Entity
{
    use StiAwareTrait;

    protected array $_hidden = [
        'age',
    ];

    protected array $_virtual = [
        'role',
    ];

    protected function _getRole(): string
    {
        return Inflector::humanize($this->type);
    }
}
