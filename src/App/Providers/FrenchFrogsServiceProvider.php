<?php namespace FrenchFrogs\App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\ServiceProvider;
use Illuminate\Mail;
use FrenchFrogs;
use Response, Request, Route, Input, Blade, Auth;

class FrenchFrogsServiceProvider extends ServiceProvider
{


    /**
     * Boot principale du service
     *
     */
    public function boot()
    {
        $this->bootModal();
        $this->extendQuerybuilder();
        $this->extendUrlGenerator();
    }

    /**
     * Extend url generator
     */
    public function extendUrlGenerator()
    {
        \App::bind('url', function() {
            return new FrenchFrogs\Laravel\Routing\UrlGenerator(
                \App::make('router')->getRoutes(),
                \App::make('request')
            );
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        foreach ((array) config('frenchfrogs') as $namespace => $config) {
            configurator($namespace)->merge($config);
        }
    }

    /**
     * Modal Manager
     *
     * Gestion des reponse ajax qui s'affiche dans une modal
     *
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

            $modal .= '<script>jQuery(function() {' . js('onload') . '});</script>';

            return $modal;
        });
    }

    /**
     * Ajout de macro au constructeur de requete
     *
     */
    public function extendQuerybuilder()
    {
        // Ajout du count
        Builder::macro('addSelectCount', function($expression) {

            $alias = null;

            if (strpos($expression, ' as ')) {
                list($expression, $alias) = explode(' as ', $expression);
            }

            $raw = sprintf('COUNT(%s)', $expression);

            if (!is_null($alias)) {
                $raw .= ' as ' . $this->grammar->wrap($alias);
            }

            return $this->selectRaw($raw);
        });

        // Ajout du de la somme
        Builder::macro('addSelectSum', function($expression) {

            $alias = null;

            if (strpos($expression, ' as ')) {
                list($expression, $alias) = explode(' as ', $expression);
            }

            $raw = sprintf('SUM(%s)', $expression);

            if (!is_null($alias)) {
                $raw .= ' as ' . $this->grammar->wrap($alias);
            }

            return $this->selectRaw($raw);
        });

        // Ajout du de la somme
        Builder::macro('addSelectHex', function($expression) {

            $alias = null;

            if (strpos($expression, ' as ')) {
                list($expression, $alias) = explode(' as ', $expression);
            }

            $raw = sprintf('HEX(%s)', $expression);
            $raw .= ' as ' . $this->grammar->wrap($alias);

            return $this->selectRaw($raw);
        });


        // Ajout du de la somme
        Builder::macro('leftJoinQuery', function(Builder $sub, $alias, $first, $operator = null, $second = null) {
            return $this->leftJoin(raw("({$sub->toSql()}) as " . $alias), $first, $operator, $second)->mergeBindings($sub);
        });


        // Ajout du de la somme
        Builder::macro('dd', function() {

            /**@var $this Builder*/
            echo \SqlFormatter::format($this->toSql());
            dd($this->getBindings(), $this);
        });
    }

}