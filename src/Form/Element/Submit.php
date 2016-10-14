<?php

namespace FrenchFrogs\Form\Element;

class Submit extends Button
{
    /**
     * Constructor.
     *
     * @param $name
     * @param string $label
     * @param array  $attr
     */
    public function __construct($name, $attr = [])
    {
        parent::__construct($name, $name, $attr);
        $this->addAttribute('type', 'submit');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $render = '';
        try {
            $render = $this->getRenderer()->render('submit', $this);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return $render;
    }
}
