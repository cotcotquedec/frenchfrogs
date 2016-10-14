<?php

namespace FrenchFrogs\App\Models;

use Auth;
use FrenchFrogs\Ruler\Page\Page;
use FrenchFrogs\Ruler\Ruler\Ruler;
use Illuminate\Support\Collection;

/**
 * Class Acl.
 */
class Acl extends Ruler
{
    /**
     * Current used interface.
     *
     * @var
     */
    protected static $interface;


    /**
     * Name of the default interface.
     */
    const INTERFACE_DEFAULT = 'default';

    /**
     * Charge les permissions.
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function loadPermissions()
    {
        $interface = static::getInterface();

        // Permission
        if (Auth::user()) {
            $permissions = \query('user_permission_user AS upu', ['upu.user_permission_id'])
                ->join('user_permission AS p', 'p.user_permission_id', '=', 'upu.user_permission_id')
                ->where('p.user_interface_id', $interface)
                ->where('user_id', user('user_id'))
                ->pluck('user_permission_id');

            if ($permissions instanceof Collection) {
                $permissions = $permissions->toArray();
            }

            $this->setPermissions($permissions);
        } else {
            // default permission ?
        }

        return $this;
    }

    /**
     * Recupère les information de navigations pour l'utilisateur.
     *
     * @throws \Exception
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNavigations()
    {
        $interface = static::getInterface();

        // Navigation
        /** @var \Illuminate\Database\Eloquent\Collection $navigation */
        $navigation = Db\User\Navigation::where('user_interface_id', $interface)
            ->whereIn('user_permission_id', $this->getPermissions())
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        return $navigation;
    }

    /**
     * Overload constructor.
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
            if (is_null($page->parent_id)) {
                $this->addPage($page->user_navigation_id, new Page($page->link, $page->name, $page->user_permission_id));

                // page de niveau inferieur
            } elseif ($this->hasPage($page->parent_id)) {
                $this->getPage($page->parent_id)->addChild($page->user_navigation_id, new Page($page->link, $page->name, $page->user_permission_id));
            } else {
                //                throw new \Exception('We don\'t find parent "' . $page->parent_id . '" for the page "' . $page->user_navigation_id . '"');
            }
        }
    }

    /**
     * Easy validation.
     *
     * @param array      $permissions
     * @param array      $laravelValidator
     * @param bool|false $throwException
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function check($permissions = [], $laravelValidator = [], $request = null, $throwException = true)
    {
        try {
            // permission
            foreach ((array) $permissions as $permission) {
                if (!$this->hasPermission($permission)) {
                    abort(401, 'You don\'t have the right permissions');
                }
            }

            // request validation
            $request = is_null($request) ? request()->all() : $request;

            // check for null value
            foreach ($request as $index => $value) {
                if (is_null($value)) {
                    unset($request[$index]);
                }
            }

            $validation = \Validator::make($request, $laravelValidator);
            if ($validation->fails()) {
                abort(404, 'Parameters are not valid');
            }
        } catch (\Exception $e) {
            if ($throwException) {
                throw $e;
            }

            return false;
        }

        return true;
    }

    /**
     * Detect current interface
     * => overload this method with your own rules if you need it.
     *
     * @return string
     */
    public static function detectInterface()
    {
        return static::INTERFACE_DEFAULT;
    }

    /**
     * Return current interface.
     *
     * @return string
     */
    public static function getInterface()
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

    /**
     * Create a new Navigation.
     *
     * @param $index
     * @param $interface
     * @param $name
     * @param $link
     * @param $permission
     * @param $parent
     */
    public static function createDatatabaseNavigation($index, $interface, $name, $link, $permission, $parent = null)
    {
        Db\User\Navigation::create([
            'user_navigation_id' => $index,
            'user_interface_id'  => $interface,
            'name'               => $name,
            'link'               => $link,
            'user_permission_id' => $permission,
            'parent_id'          => $parent,
        ]);
    }

    /**
     * Remove navigation from database.
     *
     * @param $index
     */
    public static function removeDatatabaseNavigation($index)
    {
        Db\User\Navigation::find($index)->delete();
    }

    /**
     * Insert a new permission in database.
     *
     * @param $index
     * @param $interface
     * @param $name
     */
    public static function createDatabasePermission($index, $group, $interface, $name)
    {
        Db\User\Permission::create([
            'user_permission_id'       => $index,
            'user_permission_group_id' => $group,
            'user_interface_id'        => $interface,
            'name'                     => $name,
        ]);
    }

    /**
     * Remove a permission from the database.
     *
     * @param $index
     */
    public static function removeDatabasePermission($index)
    {
        Db\User\Permission::find($index)->delete();
    }

    /**
     * Insert a interface into the database.
     *
     * @param $index
     * @param $name
     */
    public static function createDatabaseInterface($index, $name)
    {
        Db\User\UserInterface::create([
            'user_interface_id' => $index,
            'name'              => $name,
        ]);
    }

    /**
     * Remove interface from database.
     *
     * @param $index
     */
    public static function removeDatabaseInterface($index)
    {
        Db\User\UserInterface::find($index)->delete();
    }

    /**
     * Insert a new permissiongroup into datatabse.
     *
     * @param $index
     * @param $name
     */
    public static function createDatabasePermissionGroup($index, $name)
    {
        Db\User\PermissionGroup::create([
            'user_permission_group_id' => $index,
            'name'                     => $name,
        ]);
    }

    /**
     *Remove a permission group from the database.
     *
     * @param $index
     */
    public static function removeDatabasePermissionGroup($index)
    {
        Db\User\PermissionGroup::find($index)->delete();
    }
}
