<?php namespace FrenchFrogs\Form\Renderer;

use FrenchFrogs\Renderer;
use FrenchFrogs\Form;
use FrenchFrogs\Renderer\Style\Style;


class AdminLTE extends Inline
{

    /**
     *
     *
     * @var array
     */
    protected $renderers = [
        'form',
        'modal',
        'text',
        'textarea',
        'submit',
        'checkbox',
        'checkboxmulti',
        'tel',
        'email',
        'hidden',
        'label',
        'label_date',
        'link',
        'image',
        'button',
        'separator',
        'title',
        'content',
        'number',
        'radio',
        'select',
        'password',
        'file',
        'date',
        'date_range',
        'boolean',
        'select2',
        'time',
        'pre',
        'colorpicker',
        'selectcustom'
    ];


    /**
     * Render checkbox multi
     *
     * @param \FrenchFrogs\Form\Element\Checkbox $element
     * @return string
     */
    public function checkbox(Form\Element\Checkbox $element)
    {

        // CLASS
        $class =  Style::FORM_GROUP_CLASS;

        /// ERROR
        if($hasError = $element->fails()){
            $element->addClass('form-error');
            if(empty($element->getAttribute('data-placement'))){$element->addAttribute('data-placement','bottom');}
            $message = '';
            foreach($element->errors() as $error){
                $message .= $error . ' ';
            }
            $element->addAttribute('data-original-title',$message);
            $class .= ' ' .Style::FORM_GROUP_ERROR;
        }

        // LABEL
        $elementLabel = '';
        if ($element->getForm()->hasLabel()) {
            $elementLabel = '<label for="' . $element->getName() . '[]" class="col-md-3 control-label">' . $element->getLabel() . ($element->hasRule('required') ? ' *' : '') . '</label>';
        }

        // OPTIONS
        $options = '';
        foreach($element->getOptions() as $value => $label){
            $opt = '';

            // INPUT
            $attr = ['class' => 'form-check-input', 'type' => 'checkbox', 'name' => $element->getName() . '[]', 'value' => $value];

            // VALUE
            $values = (array) $element->getValue();
            if (!is_null( $element->getValue()) && in_array($value, $values)) {
                $attr['checked'] = 'checked';
            }
            $opt .= html('input', $attr);
            $options .= html('div', ['class' => 'form-check'], $opt . '<label class="form-check-label">' . $label . '</label>');
            $options .=  html('div', ['class' => 'checkbox']);
        }

        // DESCRIPTION
        if ($element->hasDescription()) {
            $options .= html('span', ['class' => 'help-block'], $element->getDescription());
        }

        // INPUT
        $html =  html('div', ['class' => 'form-group'], $options);

        // FINAL CONTAINER
        $html = html('div', ['class' => 'col-md-9'], $html);
        return html('div', compact('class'), $elementLabel . $html);
    }

    /**
     * Render boolean element
     *
     * @param \FrenchFrogs\Form\Element\Boolean $element
     * @return string$
     */
    public function boolean(Form\Element\Boolean $element)
    {
        // CLASS
        $class =  Style::FORM_GROUP_CLASS . ' row';

        // ERROR
        if($hasError = $element->fails()){

            $element->addClass('form-error');
            if(empty($element->getAttribute('data-placement'))){$element->addAttribute('data-placement','bottom');}
            $message = '';
            foreach($element->errors() as $error){
                $message .= $error . ' ';
            }
            $element->addAttribute('data-original-title',$message);
            $class .= ' ' .Style::FORM_GROUP_ERROR;
        }

        // LABEL
        $label = '';
        if ($element->getForm()->hasLabel()) {
            $label = '<label for="' . $element->getName() . '" class="col-md-3 control-label">' . $element->getLabel() . ($element->hasRule('required') ? ' *' : '') . '</label>' . PHP_EOL;
        }

        // INPUT
        $element->addClass('make-switch');
        $element->addAttribute('type', 'checkbox');
        $element->addAttribute('data-toggle', 'toggle');
        $element->addAttribute('data-size', 'sm');
        $element->addAttribute('value', 1);
        $element->addAttribute('id', $element->getName());
        if ($element->getValue()) {
            $element->addAttribute('checked', 'checked');
        }

        $element->addClass(Style::FORM_ELEMENT_CONTROL);
        $html = html('input', $element->getAttributes());

        // DESCRIPTION
        if ($element->hasDescription()) {
            $html .= html('span', ['class' => 'help-block'], $element->getDescription());
        }

        // FINAL CONTAINER
        $html = html('div', ['class' => 'col-md-9'], $html);
        return html('div', compact('class'), $label . $html);
    }
}
