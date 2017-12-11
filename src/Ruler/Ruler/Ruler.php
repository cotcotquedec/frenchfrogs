<?php namespace FrenchFrogs\Ruler\Ruler;

use FrenchFrogs\Container\Container;
use FrenchFrogs\Core;
use FrenchFrogs\Ruler\Page\Page;
use Illuminate\Database\Eloquent\Collection;

class Ruler
{

    use Core\Renderer;
    use Navigation;
    use Permission;

    /**
     * Instances
     *
     * @var Ruler
     */
    static protected $instance;


    /**
     * constructor du singleton
     *
     * @return Container
     */
    static function getInstance() {

        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }


    /**
     *
     *
     * @return mixed|string
     */
    public function renderNavigation()
    {

        if(!$this->hasRenderer()) {
            $renderer = configurator()->build('ruler.renderer.class');
            $this->setRenderer($renderer);
        }

        $render = '';
        try {
            $render = $this->getRenderer()->render('navigation', $this);
        } catch(\Exception $e){
            dd($e->getMessage());//@todo find a good way to warn the developper
        }

        return $render;
    }


    /**
     * Overload parent method for form specification
     *
     * @return string
     *
     */
    public function __toString()
    {
        return $this->render();
    }



    /**
     * Current used interface
     *
     * @var
     */
    static protected $interface;


    /**
     * Name of the default interface
     *
     */
    const INTERFACE_DEFAULT = 'default';


    /**
     * Charge les permissions
     *
     * @return $this
     * @throws \Exception
     */
    public function loadPermissions()
    {
        $interface = static::getInterface();

        // Permission
        if ($user = \Auth::user()) {

            if (method_exists($user, 'permissions')) {
                $permissions = \Auth::user()->permissions()->pluck('user_permission_id');

                if ($permissions instanceof Collection) {
                    $permissions = $permissions->toArray();
                }

                $this->setPermissions($permissions);
            }
        } else {
            // default permission ?
        }

        return $this;
    }

    /**
     * Recupère les information de navigations pour l'utilisateur
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function getNavigations()
    {
        $interface = static::getInterface();

        $model = configurator($interface)->build('navigations.model.class');

        // Navigation
        /**@var \Illuminate\Database\Eloquent\Collection $navigation */
        $navigation = $model->where('interface_sid', $interface)
//            ->whereIn('permission_sid', $this->getPermissions() + [null])
            ->orderBy('parent_sid')
            ->orderBy('name')
            ->get();

        return $navigation;
    }

    /**
     * Overload constructor
     *
     *
     * @throws \Exception
     */
    public function __construct()
    {
        // ojn charge les permissions
        $this->loadPermissions();

        $navigation = $this->getNavigations();

        // COnstruction de la navigation
        while ($page = $navigation->shift()) {

            // page ne niveau 1
            if (is_null($page->parent_sid)) {
                $this->addPage($page->sid, new Page($page->link, $page->name, $page->permission_sid));

                // page de niveau inferieur
            } elseif($this->hasPage($page->parent_id)) {
                $this->getPage($page->parent_sid)->addChild($page->sid, new Page($page->link, $page->name, $page->permission_sid));
            } else {
//                throw new \Exception('We don\'t find parent "' . $page->parent_id . '" for the page "' . $page->user_navigation_id . '"');
            }
        }
    }

    /**
     * Detect current interface
     * => overload this method with your own rules if you need it
     *
     * @return string
     */
    static public function detectInterface()
    {
        return static::INTERFACE_DEFAULT;
    }


    /**
     * Return current interface
     *
     * @return string
     */
    static public function getInterface()
    {

        // if no interface was detected before we try to detect
        if (!isset(static::$interface)) {
            static::$interface = static::detectInterface();
        }

        if (!isset(static::$interface)) {
            throw new \Exception('Error, no interface was detected');
        }

        return static::$interface;
    }

}