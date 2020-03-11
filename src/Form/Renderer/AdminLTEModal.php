<?php namespace FrenchFrogs\Form\Renderer;

use FrenchFrogs\Renderer;
use FrenchFrogs\Form;
use FrenchFrogs\Renderer\Style\Style;


class AdminLTEModal extends AdminLTE
{


    function form(Form\Form\Form $form)
    {
        $html = '';
        $form->addAttribute('role', 'form');
        $form->addClass('form-horizontal');

        // Elements
        if ($form->hasCsrfToken()) {
            $html .= csrf_field();
        }

        if ($form->hasLegend()) {
            $html .= html('h4', ['class' => 'modal-title'], $form->getLegend());
        }

        $html .= html('button', ['type' => 'button', 'class' => 'close', 'data-dismiss' => 'modal'], '<span aria-hidden="true">&times;</span>');


        $html = html('div', ['class' => 'modal-header'], $html);

        $body = '';
        foreach ($form->getElements() as $e) {
            /** @var $e \FrenchFrogs\Form\Element\Element */
            $body .= $e->render();
        }

        // body
        $html .= html('div', ['class' => 'modal-body' . ' form-body'], $body);

        // Actions
        if ($form->hasActions()) {
            $actions = '';
            foreach ($form->getActions() as $e) {
                $actions .= $e->render();
            }
            $html .= html('div', ['class' => 'modal-footer'], $actions);
        }

        if ($form->isRemote()) {
            $form->addClass('form-remote');
        }

        $html = html('form', $form->getAttributes(), $html);

        return $html;
    }
}
