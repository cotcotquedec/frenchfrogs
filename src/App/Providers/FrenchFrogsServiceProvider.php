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
        $this->bootValidator();
        $this->extendQuerybuilder();
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
     * Ajoute de nouveau validateur
     *
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