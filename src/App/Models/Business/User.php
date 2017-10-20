<?php namespace FrenchFrogs\App\Models\Business;

use FrenchFrogs\App\Models\Acl;
use FrenchFrogs\App\Models\Db;
use FrenchFrogs\Business\Business;
use Webpatser\Uuid\Uuid;


/**
 * Class User
 *
 *
 * @package FrenchFrogs\App\Models\Business
 */
class User extends Business
{
    static protected $modelClass = Db\User\User::class;

    /**
     * Nombre de caractère minimum pour la generation de mot de pass
     */
    const PASSWORD_MIN_LENGTH = 8;


    /**
     * Generation d'un mot passe
     *
     * @return string
     */
    static public function generateRandomPassword($size = 0)
    {
        $size = intval($size);
        return str_random($size < static::PASSWORD_MIN_LENGTH ? static::PASSWORD_MIN_LENGTH : $size);
    }

    /**
     * Change le mot de passe d'un utilisateur
     *
     * @param $password
     * @return $this
     */
    public function changePassword($password)
    {
        $this->getModel()->update(['password' => \Hash::make($password)]);
        return $this;
    }

    /**
     * CReation d'un utilisatreur depuis les valeur de base
     *
     * @param $email
     * @param $password
     * @param $name
     * @param null $interface
     * @return $this
     * @throws \Exception
     */
    static public function init($email, $password, $interface, $name, $is_admin = false)
    {
        // recuperation de la classe de model principale
        $class = static::$modelClass;

        // on valide que l'utilisateur n'existe pas déjà
        $user = $class::firstOrNew([
            'email' => $email,
            'interface_rid' => $interface
        ]);

        // si l'utilisateur existe déjà on coupe le script
        if ($user->exists) {
            throw new \Exception('L\'utilisateur "' . $email . '"" pour l\'interface "' . $interface. '"" existe déjà');
        }

        // création de l'utilisateur
        $user->password = bcrypt($password);
        $user->name = $name;
        $user->save();

        // si admin on donne accès a tout
        if ($is_admin) {
            $permission = Db\User\Permission::where('interface_rid', $interface)->pluck('user_permission_id');
            User::get($user->getKey())->setPermissions($permission->toArray());
        }

        return static::get($user->user_id);
    }

	/**
	 * return groups
	 *
	 * @return array
	 */
    public function getGroups()
    {
        return \query('user_group_user', ['user_group_id'])->where('user_id', $this->getId())->pluck('user_group_id')->toArray();
    }

	/**
	 * Set groups for an user
	 *
	 * @param $groups
	 * @return $this
	 */
    public function setGroups($groups)
    {
        // permission actuelles
        $current = $this->getGroups();

        // gestion des nouvelles permissions
        $insert = [];
        foreach(array_diff($groups, $current) as $g) {
            if (empty($g)) {continue;}
            $insert[] = ['user_group_user_id' => \uuid()->bytes, 'user_id' => $this->getId(), 'user_group_id' => $g];
        }

        $this->getModel()->groups()->insert($insert);

        // gestion des permissions a supprimer
        $this->getModel()->groups()->whereIn('user_group_id', array_diff($current, $groups))->delete();

        return $this;
    }

    /**
     * Return list of user permission
     *
     * @return array
     */
    public function getPermissions()
    {
        return \query('user_permission_user', ['user_permission_id'])->where('user_id', $this->getId())->pluck('user_permission_id')->toArray();
    }

    /**
     * Synchronyse user permissions
     *
     * @param $permissions
     * @return $this
     */
    public function setPermissions($permissions)
    {
        // permission actuelles
        $current = $this->getPermissions();

        // gestion des nouvelles permissions
        $insert = [];
        foreach(array_diff($permissions, $current) as $p) {
            if (empty($p)) {continue;}
            $insert[] = ['user_permission_user_id' => \uuid()->bytes, 'user_id' => $this->getId(), 'user_permission_id' => $p];
        }

        $this->getModel()->permissions()->insert($insert);

        // gestion des permissions a supprimer
        $this->getModel()->permissions()->whereIn('user_permission_id', array_diff($current, $permissions))->delete();

        return $this;
    }


    /**
     * Add a single permission to the user
     *
     * @param $permission
     * @return $this
     */
    public function addPermission($permission)
    {
        $current = $this->getPermissions();

        // if user do'nt have persmission, we add it
        if (array_search($permission, $current) === false) {
            $this->getModel()->permissions()->insert(
                [
                    'user_id' => $this->getId(),
                    'user_permission_id' => $permission
                ]
            );
        }

        return $this;
    }

    /**
     * Remove a single permission from the user
     *
     * @param $permission
     * @return $this
     */
    public function removePermission($permission)
    {
        $this->getModel()->permissions()->where('user_permission_id', $permission)->delete();
        return $this;
    }


    /**
     * Setter for $parameters
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->getModel()->update(['parameters' => \json_encode($parameters)]);
        return $this;
    }

    /**
     * Gertter for $parameters
     *
     * @return array
     */
    public function getParameters()
    {
        $parameters = $this->getModel()->parameters;
        return (array) \json_decode($parameters, JSON_OBJECT_AS_ARRAY);
    }

    /**
     * Getter for a single parameter
     *
     * @param $key
     * @return mixed|null
     */
    public function getParameter($key)
    {
        $parameters = $this->getParameters();
        return isset($parameters[$key]) ? $parameters[$key] : null;
    }

    /**
     * Add a single parameter
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function addParameter($key, $value)
    {
        $parameters = $this->getParameters();
        $parameters[$key] = $value;
        $this->setParameters($parameters);
        return $this;
    }


    /**
     * Remove a single parameter
     *
     * @param $key
     * @return $this
     */
    public function removeParameter($key)
    {
        $parameters = $this->getParameters();
        unset($parameters[$key]);
        $this->setParameters($parameters);
        return $this;
    }
}