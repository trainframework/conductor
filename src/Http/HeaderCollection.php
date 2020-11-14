<?php
namespace Conductor\Http;

use Doctrine\Common\Collections\ArrayCollection;

class HeaderCollection extends ArrayCollection
{
    /**
     * @param array $elements
     */
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

    /**
     * @inheritDoc
     */
    public function remove($name)
    {
        parent::remove(strtolower($name));
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($name, $value)
    {
        parent::offsetSet(strtolower($name), $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($name)
    {
        return parent::offsetGet(strtolower($name));
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return parent::offsetExists(strtolower($offset));
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        parent::offsetUnset(strtolower($offset));
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name)
    {
        return $this->filter(function ($headerValue, $headerName) use ($name) {
            return (strcasecmp($headerName, $name) == 0);
        });
    }
}
