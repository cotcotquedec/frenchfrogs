<?php namespace FrenchFrogs\Table\Column\Strainer;

use FrenchFrogs\Form\Element\Select as FormSelect;
use FrenchFrogs\Table\Column\Column;

class Boolean extends Select
{

    public function __construct(Column $column, $callable = null, $attr = [])
    {
        parent::__construct($column, ["no" => "No", "yes" =>  "Yes"], $callable, $attr);
    }

    /**
     * Overloading
     *
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->getElement()->setValue([$value]);
        return $this;
    }
    /**
     * Get value to strainer element
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->getElement()->getValue();
    }


    /**
     * Execute strainer
     *
     * @param \FrenchFrogs\Table\Table\Table $table
     * @param array ...$params
     * @return $this
     * @throws \Exception
     */
    public function call(\FrenchFrogs\Table\Table\Table $table, ...$params)
    {

        if ($this->hasCallable()) {
            array_unshift($params, $this);
            array_unshift($params, $table);
            call_user_func_array($this->callable, $params);
        } else {

            // verify that source is a query
            if (!$table->isSourceQueryBuilder()) {
                throw new \Exception('Table source is not an instance of query builder');
            }



            // Filtrage des valeur
            $value = $params[0];

            if (!is_null($value)) {

                // cas du NON
                if ($value === 'no') {
                    $value = false;
                }

                // Valeur en boolean
                $value = !empty($value);

                // GEstion du champs
                $this->setValue($value ? 'yes' : 'no');

                $table->getSource()->where($this->getField(), $value);
            }
        }

        return $this;
    }


    /**
     *
     *
     * @return string
     */
    public function render()
    {
        $render = '';
        try {
            $render = $this->getRenderer()->render('strainerBoolean', $this);
        } catch(\Exception $e){
            dd($e->getMessage());
        }

        return $render;
    }
}
