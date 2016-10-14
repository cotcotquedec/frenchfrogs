<?php

namespace FrenchFrogs\Form\Element;

class File extends Text
{
    /**
     * Constructror.
     *
     * @param $name
     * @param string $label
     * @param array  $attr
     */
    public function __construct($name, $label = '', $attr = [])
    {
        $this->setAttributes($attr);
        $this->setName($name);
        $this->setLabel($label);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $render = '';
        try {
            $render = $this->getRenderer()->render('file', $this);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return $render;
    }
}
