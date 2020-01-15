<?php
declare(strict_types=1);

namespace Muffin\Sti\TestApp\Model\Entity;

use Cake\ORM\Entity;
use Muffin\Sti\Model\Entity\StiAwareTrait;

class Spoon extends Entity
{
    use StiAwareTrait;
}
