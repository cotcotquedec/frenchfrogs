<?php namespace FrenchFrogs\Form\Element;


class SelectCustom extends Element
{

    /**
     * Valeur pour le select
     *
     *
     * @var array
     */
    protected $options;

    /**
     * @var int
     */
    protected $col_md_label;
    /**
     * @var int
     */
    protected $col_md_select;

    /**
     *
     *
     * @param $name
     * @param string $label
     * @param array $options
     * @param int $col_md_label
     * @param int $col_md_select
     */
    public function __construct($name, $label = '', $options, int $col_md_label, int $col_md_select)
    {
        $this->setName($name);
        $this->setLabel($label);
        $this->setOptions($options);
        $this->setColMdLabel($col_md_label);
        $this->setColMdSelect($col_md_select);
    }

    /**
     * Setter pour les options
     *
     * @param $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param $col_md_label
     * @return $this
     */
    public function setColMdLabel($col_md_label)
    {
        $this->col_md_label = $col_md_label;
        return $this;
    }

    /**
     * @return int
     */
    public function getColMdLabel()
    {
        return $this->col_md_label;
    }

    /**
     * @param $col_md_select
     * @return $this
     */
    public function setColMdSelect($col_md_select)
    {
        $this->col_md_select = $col_md_select;
        return $this;
    }

    /**
     * @return int
     */
    public function getColMdSelect()
    {
        return $this->col_md_select;
    }

    /**
     * Getter pour les options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Setting de la value
     *
     * @param mixed $value
     */
    public function setValue($value)
    {

        if ($this->isMultiple()) {
            $value = (array) $value;
        }

        $this->value = $value;
    }


    /**
     * getter placeholder
     *
     * @return null
     */
    public function getPlaceholder()
    {
        return '-- ' . $this->getAttribute('placeholder') . ' --';
    }
    
    /**
     * @return string
     */
    public function __toString()
    {

        $render = '';
        try {
            $render = $this->getRenderer()->render('selectcustom', $this);
        } catch(\Exception $e){
            dd($e->getTraceAsString());
        }

        return $render;
    }


    /**
     * Set Options from parent selection
     *
     * @param $selector
     * @param $url
     * @return $this
     */
    public function setDependOn($selector, $url)
    {
        return $this->addAttribute('data-parent-url', $url)
                    ->addAttribute('data-parent-selector',  $selector)
                    ->addAttribute('data-populate', function(Element $element){
                        return $element->getValue();
                    })
                    ->addClass('select-remote');
    }


    /**
     * Unlink parent selection for options completion
     *
     * @return $this
     */
    public function removeDependOn()
    {
        return $this->removeAttribute('data-parent-url')
            ->removeAttribute('data-parent-selector')
            ->removeClass('select-remote');
    }
}
