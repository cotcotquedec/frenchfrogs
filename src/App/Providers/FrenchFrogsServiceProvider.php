<?php namespace FrenchFrogs\App\Providers;

use FrenchFrogs;
use FrenchFrogs\Laravel\Database\Schema\MySqlGrammar;
use Illuminate\Database\Query\Grammars\MySqlGrammar as IlluminateMySqlGrammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Response;

class FrenchFrogsServiceProvider extends ServiceProvider
{

    /**
     * Boot principale du service
     *
     */
    public function boot()
    {
        $this->bootDb();
        $this->bootModal();
        $this->bootConfig();
        $this->extendQuerybuilder();
        $this->extendUrlGenerator();
    }


    /**
     *
     * Chargement de la configuration de frenchefrogs
     *
     */
    public function bootConfig()
    {

        // Gestion de la configuration
//        $config = config_path('frenchfrogs.php');
//        if (file_exists($config)) {
//            $config = include_once $config;
//
//            // on charge le namespace
//            $config = $config['namespaces'][$config['default']];
//            config()->set('frenchfrogs', $config);
//        }

        // Frenchfrogs
        app()->singleton('frenchfrogs', function () {

            return new class()
            {

                protected $namespace;

                public function __construct()
                {
                    $default = config('frenchfrogs.default');
                    $this->namespace = 'frenchfrogs.namespaces.' . $default . '.';
                }


                /**
                 * @param $index
                 * @param null $default
                 * @return \Illuminate\Config\Repository|mixed
                 */
                function get($index, $default = null)
                {
                    return config($this->namespace . $index, $default);
                }

                /**
                 * @param $index
                 * @param null $default
                 * @param array $params
                 * @return object
                 * @throws \Throwable
                 */
                function build($index, $default = null, $params = [])
                {
                    // Recuperationd e la class
                    $class = $this->get($index, $default);

                    // Si on ne trouve pas la class, on envoie une exeception
                    throw_unless(class_exists($class), new \Exception('Class doesn\'t exist for the index : ' . $class));

                    return (new \ReflectionClass($class))->newInstanceArgs($params);
                }
            };
        });
    }


    /**
     *
     * Surcharge de la base de donnée
     *
     */
    public function bootDb()
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = app('db')->connection();

        // ON recupere la confiuguration existante
        $queryGrammar = $connection->getQueryGrammar();
        $queryGrammarClass = get_class($queryGrammar);


        // Si pas mysql on pousse une erreur
        if (!in_array($queryGrammarClass, [
            IlluminateMySqlGrammar::class,
        ])) {
            throw new \Exception("There current grammar `$queryGrammarClass` doesn't support binary uuids. Only  MySql and SQLite connections are supported.");
        }

        // Upgrade
        $connection->setSchemaGrammar(new MySqlGrammar());
    }

    /**
     * Get the URL generator request rebinder.
     *
     * @return \Closure
     */
    protected function requestRebinder()
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }


    /**
     * Extend url generator
     */
    public function extendUrlGenerator()
    {
        app()->singleton('url', function ($app) {

            $routes = $app['router']->getRoutes();

            $url = new FrenchFrogs\Laravel\Routing\UrlGenerator(
                $routes, $app->rebinding(
                'request', $this->requestRebinder()
            )
            );

            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            // If the route collection is "rebound", for example, when the routes stay
            // cached for the application, we will need to rebind the routes on the
            // URL generator instance so it has the latest version of the routes.
            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
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
                $renderer = ff()->get('form.renderer_modal');
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
//        // Ajout du count
//        Builder::macro('addSelectCount', function ($expression) {
//
//            $alias = null;
//
//            if (strpos($expression, ' as ')) {
//                list($expression, $alias) = explode(' as ', $expression);
//            }
//
//            $raw = sprintf('COUNT(%s)', $expression);
//
//            if (!is_null($alias)) {
//                $raw .= ' as ' . $this->grammar->wrap($alias);
//            }
//
//            return $this->selectRaw($raw);
//        });
//
//        // Ajout du de la somme
//        Builder::macro('addSelectSum', function ($expression) {
//
//            $alias = null;
//
//            if (strpos($expression, ' as ')) {
//                list($expression, $alias) = explode(' as ', $expression);
//            }
//
//            $raw = sprintf('SUM(%s)', $expression);
//
//            if (!is_null($alias)) {
//                $raw .= ' as ' . $this->grammar->wrap($alias);
//            }
//
//            return $this->selectRaw($raw);
//        });
//
//        // Ajout du de la somme
//        Builder::macro('addSelectHex', function ($expression) {
//
//            $alias = null;
//
//            if (strpos($expression, ' as ')) {
//                list($expression, $alias) = explode(' as ', $expression);
//            }
//
//            $raw = sprintf('HEX(%s)', $expression);
//            $raw .= ' as ' . $this->grammar->wrap($alias);
//
//            return $this->selectRaw($raw);
//        });


        // Ajout du de la somme
        Builder::macro('leftJoinQuery', function (Builder $sub, $alias, $first, $operator = null, $second = null) {
            return $this->leftJoin(raw("({$sub->toSql()}) as " . $alias), $first, $operator, $second)->mergeBindings($sub);
        });


        // Ajout du de la somme
        Builder::macro('dd', function () {

            /**@var $this Builder */
            echo \SqlFormatter::format($this->toSql());
            dd($this->getBindings(), $this);
        });
    }

}