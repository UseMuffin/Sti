<?php
declare(strict_types=1);

namespace Muffin\Sti\Model\Entity;

trait StiAwareTrait
{
    /**
     * Returns a copy of the entity
     *
     * @return array Properties for copying
     */
    public function forCopy()
    {
        return $this->_fields;
    }
}
