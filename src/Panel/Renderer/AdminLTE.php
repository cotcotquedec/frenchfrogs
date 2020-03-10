<?php namespace FrenchFrogs\Panel\Renderer;

use FrenchFrogs\Panel;
use FrenchFrogs\Form\Element;
use FrenchFrogs\Renderer\Style\AdminLTE as Style;

/**
 * Renderer for portlet conquer template
 *
 * Class Conquer
 * @package FrenchFrogs\Panel\Renderer
 */
class AdminLTE extends Bootstrap
{

    /**
     * Main renderer
     *
     * @param \FrenchFrogs\Panel\Panel\Panel $panel
     */
    public function panel(Panel\Panel\Panel $panel)
    {

        $html = '';

        //@todo Action render
        $actions = '';
        foreach($panel->getActions() as $action) {
            $actions .= $action->render() . PHP_EOL;
        }

        $html .= html('div', ['class' => 'card-title'], $panel->getTitle());
        $html .= html('div', ['class' => 'card-tools'], $actions);
        $html = html('div', ['class' => 'card-header'], $html);

        //@todo footer render
        $html .= html('div', ['class' => 'card-body'], $panel->getBody());

        $panel->addClass('card');

        if ($panel->hasContext()) {
            $panel->addClass(constant( Style::class . '::' . $panel->getContext()));
        }

        return html('div', $panel->getAttributes(), $html);
    }

}
