<?php namespace FrenchFrogs\Form\Element;


class Markdown extends Textarea
{

    /**
     * Constructror
     *
     * @param $name
     * @param string $label
     * @param array $attr
     */
    public function __construct($name, $label = '', $attr = [] )
    {
        parent::__construct($name, $label, $attr);
        $this->addClass('ff-markdown');
    }
}