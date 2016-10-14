<?php

namespace FrenchFrogs\Table\Column;

class Boolean extends Text
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
        parent::__construct($name, $label, $attr);
        $this->center();
    }

    /**
     * @param $row
     *
     * @return bool
     */
    public function getValue($row)
    {
        return isset($row[$this->getName()]) && !empty($row[$this->getName()]);
    }

    /**
     * @return string
     */
    public function render(array $row)
    {
        $render = '';
        try {
            $render = $this->getRenderer()->render('boolean', $this, $row);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return $render;
    }
}
