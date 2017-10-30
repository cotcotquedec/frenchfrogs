<?php namespace FrenchFrogs\Table\Column;


class Pre extends Text
{
    /**
     *
     *
     * @return string
     */
    public function render(array $row)
    {
        // Check visibility
        if (!$this->isVisible($row)) {
            return '';
        }

        $render = '';
        try {
            $render = $this->getRenderer()->render('pre', $this, $row);
        } catch(\Exception $e){
            dd($e->getMessage());
        }

        return $render;
    }
}