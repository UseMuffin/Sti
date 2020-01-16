<?php
declare(strict_types=1);

namespace Muffin\Sti\TestApp\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use Muffin\Sti\Model\Entity\StiAwareTrait;

class Chef extends Entity
{
    use StiAwareTrait;

    protected $_hidden = [
        'age',
    ];

    protected $_virtual = [
        'role',
    ];

    protected function _getRole()
    {
        return Inflector::humanize($this->type);
    }
}
