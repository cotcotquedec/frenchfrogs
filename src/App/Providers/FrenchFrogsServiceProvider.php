<?php

namespace FrenchFrogs\App\Providers;

use FrenchFrogs;
use Illuminate\Support\ServiceProvider;
use Response;

class FrenchFrogsServiceProvider extends ServiceProvider
{
    /**
     * Boot principale du service.
     */
    public function boot()
    {
        $this->bootModal();
        $this->bootValidator();
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        foreach (config('frenchfrogs') as $namespace => $config) {
            configurator($namespace)->merge($config);
        }
    }

    /**
     * Ajoute de nouveau validateur.
     */
    public function bootValidator()
    {
        // notExists (database) renvoie true si l'entrée n'existe pas
        \Validator::extend('not_exists', function ($attribute, $value, $parameters) {
            $row = \DB::table($parameters[0])->where($parameters[1], '=', $value)->first();

            return empty($row);
        });
    }

    /**
     * Modal Manager.
     *
     * Gestion des reponse ajax qui s'affiche dans une modal
     */
    public function bootModal()
    {
        Response::macro('modal', function ($title, $body = '', $actions = []) {
            if ($title instanceof FrenchFrogs\Modal\Modal\Modal) {
                $modal = $title->enableRemote();
            } elseif ($title instanceof FrenchFrogs\Form\Form\Form) {
                $renderer = configurator()->get('form.renderer.modal.class');
                $modal = $title->setRenderer(new $renderer());
            } else {
                $modal = \modal($title, $body, $actions)->enableRemote();
            }

            $modal .= '<script>jQuery(function() {'.js('onload').'});</script>';

            return $modal;
        });
    }
}
