<?php namespace FrenchFrogs\App\Models;


use FrenchFrogs\Ruler\Page\Page;
use FrenchFrogs\Ruler\Ruler\Ruler;
use FrenchFrogs\App\Models\Db;
use Auth;
use Illuminate\Support\Collection;

/**
 * Class Acl
 *
 *
 * @package FrenchFrogs\Acl
 */
class Acl extends Ruler
{

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
        if ($user = Auth::user()) {

            if (method_exists($user, 'permissions')) {
                $permissions = Auth::user()->permissions()->pluck('user_permission_id');

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


        dd('sljhd1');
        $interface = static::getInterface();


        // Navigation
        /**@var \Illuminate\Database\Eloquent\Collection $navigation */
        $navigation = Db\User\Navigation::where('user_interface_id', $interface)
            ->whereIn('user_permission_id', $this->getPermissions())
            ->orderBy('parent_id')
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
        dd('sljhd1');

        // ojn charge les permissions
        $this->loadPermissions();

		$navigation = $this->getNavigations();

        // COnstruction de la navigation
        while ($page = $navigation->shift()) {

            // page ne niveau 1
            if (is_null($page->parent_id)) {
                $this->addPage($page->user_navigation_id, new Page($page->link, $page->name, $page->user_permission_id));

                // page de niveau inferieur
            } elseif($this->hasPage($page->parent_id)) {
                $this->getPage($page->parent_id)->addChild($page->user_navigation_id, new Page($page->link, $page->name, $page->user_permission_id));
            } else {
//                throw new \Exception('We don\'t find parent "' . $page->parent_id . '" for the page "' . $page->user_navigation_id . '"');
            }
        }
    }


    /**
     * Easy validation
     *
     * @param array $permissions
     * @param array $laravelValidator
     * @param bool|false $throwException
     * @return bool
     * @throws \Exception
     */
    public function check($permissions = [], $laravelValidator = [], $request = null, $throwException = true)
    {

        try {
            // permission
            foreach ((array)$permissions as $permission) {
                if (!$this->hasPermission($permission)) {
                    abort(401, 'You don\'t have the right permissions');
                }
            }

            // request validation
            $request = is_null($request) ? request()->all() : $request;

            // check for null value
            foreach($request as $index => $value) {
                if (is_null($value)) {
                    unset($request[$index]);
                }
            }

            $validation = \Validator::make($request, $laravelValidator);
            if ($validation->fails()) {
                abort(404, 'Parameters are not valid');
            }
        }catch (\Exception $e) {
            if ($throwException) {throw $e;}

            return false;
        }

        return true;
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