<?php namespace FrenchFrogs\Form\Element;


class Separator extends Element
{

    /**
     * @return string
     */
    public function __toString()
    {

        $render = '';
        try {
            $render = $this->getRenderer()->render('separator', $this);
        } catch(\Exception $e){
            debugbar()->addThrowable($e);
        }

        return $render;
    }
}