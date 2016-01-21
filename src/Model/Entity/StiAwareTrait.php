<?php
namespace Muffin\Sti\Model\Entity;

trait StiAwareTrait
{
    public function forCopy()
    {
        return $this->_properties;
    }
}
