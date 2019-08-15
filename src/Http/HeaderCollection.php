<?php
namespace Conductor\Http;

use Doctrine\Common\Collections\ArrayCollection;

class HeaderCollection extends ArrayCollection
{
    public function __construct(array $elements = [])
    {
        if (count($elements) > 0) {
            foreach ($elements as $elementKey => $elementValue) {
                $this->offsetSet($elementKey, $elementValue);
            }
        } else {
            parent::__construct($elements);
        }
    }

    public function remove($name)
    {
        parent::remove(strtolower($name));
    }

    public function offsetSet($name, $value)
    {
        parent::offsetSet(strtolower($name), $value);
    }

    public function offsetGet($name)
    {
        return parent::offsetGet(strtolower($name));
    }

    public function offsetExists($offset)
    {
        return parent::offsetExists(strtolower($offset));
    }

    public function offsetUnset($offset)
    {
        parent::offsetUnset(strtolower($offset));
    }

    public function findByName(string $name)
    {
        return $this->filter(function ($headerValue, $headerName) use ($name) {
            return (strcasecmp($headerName, $name) == 0);
        });
    }
}
