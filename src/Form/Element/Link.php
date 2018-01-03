<?php namespace FrenchFrogs\Form\Element;


class Link extends Label
{
    /**
     * @return string
     */
    public function __toString()
    {

        $render = '';
        try {
            $render = $this->getRenderer()->render('link', $this);
        } catch(\Exception $e){
            debugbar()->addThrowable($e);
        }

        return $render;
    }
}