<?php
namespace Muffin\Sti\TestApp\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;

class Chef extends Entity
{
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
