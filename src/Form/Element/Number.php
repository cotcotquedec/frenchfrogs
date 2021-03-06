<?php namespace FrenchFrogs\Form\Element;


class Number extends Text
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
            $render = $this->getRenderer()->render('number', $this);
        } catch(\Exception $e){
            debugbar()->addThrowable($e);
        }

        return $render;
    }
}