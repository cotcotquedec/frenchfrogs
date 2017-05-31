<?php namespace FrenchFrogs\App\Http\Controllers;
use FrenchFrogs\App\Models\Db\User\Group;
use FrenchFrogs\App\Models\Db\User\User;
use FrenchFrogs\App\Models\Db\User\UserInterface;

/**
 * Class AclController
 *
 * Gestion des droits
 *
 * @package FrenchFrogs\Acl\Http\Controllers
 */
trait AclController
{

    protected $permission;

    /**
     * Build user table polliwog
     *
     * @return \FrenchFrogs\Table\Table\Table
     */
    static public function user()
    {

        // QUERY
        $query = \query('user as u', [
            raw('HEX(user_id) as user_id'),
            'u.name as name',
            'email',
            'i.name as interface_name',
            'loggedin_at',
            raw('api_token IS NOT NULL as api_acess')
        ])
            ->join('user_interface as i', 'i.user_interface_id', '=', 'u.user_interface_id')
            ->whereNull('u.deleted_at');

        // TABLE
        $table = \table($query);
        $table->setConstructor(static::class, __FUNCTION__)->enableRemote()->enableDatatable();
        $table->useDefaultPanel('Liste des utilisateurs')
            ->getPanel()
            ->addButton('add_user', 'Ajouter', action_url(static::class, 'postUser'))
            ->enableRemote();

        // COLONNES
        $table->addText('name', 'Nom')->setStrainerText('u.name');
        $interfaces = pairs('user_interface', 'user_interface_id', 'name');
        $table->addText('interface_name', 'Interface')->setStrainerSelect($interfaces, 'u.user_interface_id');
        $table->addText('email', 'Email')->setStrainerText('email');
        $table->addBoolean('api_acess', 'API?')->setStrainerBoolean(raw('api_token IS NOT NULL'));
        $table->addDate('loggedin_at', 'Dernière connexion');
        $table->setSearch('email');

        // ACTION
        $container = $table->addContainer('action', 'Actions')->setWidth('200');
//        $container->addButtonRemote('parameters', 'Paramètre', action_url(static::class, 'postParameter', '%s'), 'user_id')->icon('fa fa-cogs');
        $container->addButtonRemote('permission', 'Permissions', action_url(static::class, 'postPermissions', '%s'), 'user_id')->icon('fa fa-gavel');
        $container->addButtonRemote('groups', 'Groupes', action_url(static::class, 'postUserGroup', '%s'), 'user_id')->icon('fa fa-users');
        $container->addButtonRemote('password', 'Mot de passe', action_url(static::class, 'postPassword', '%s'), 'user_id')->icon('fa fa-key')->setOptionAsWarning();
        $container->addButtonRemote('avatar', 'Avatar', action_url(static::class, 'postAvatar', '%s'), 'user_id')->icon('fa fa-image');
        $container->addButtonRemote('api', 'Api', action_url(static::class, 'postApi', '%s'), 'user_id')->icon('fa fa-cloud');
        $container->addButtonEdit(action_url(static::class, 'postUser', '%s'), 'user_id');
        $container->addButtonDelete(action_url(static::class, 'deleteUser', '%s'), 'user_id');
        return $table;
    }


    /**
     *
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function postApi($id)
    {
        \ruler()->check(
            $this->permission,
            ['id' => 'required:exists:user,user_id'],
            ['id' => $uuid = f($id, 'uuid')]
        );

        $user = User::findOrFail($uuid);

        // FORM
        $form = \form()->enableRemote();
        $form->setLegend('Utilisateur : ' . $user->name);
        if ($user->api_token) {
            $form->addLabel('api_token', 'Token')->setValue($user->api_token);
            $form->addSubmit('revoke')->setLabel('Supprimer la clé')->setOptionAsDanger();
        } else {
            $form->addContent('message', '<p class="well">Cet utilisateur n\'a pas encore accès à l\'api.</p>');
        }
        $form->addSubmit('generate')->setLabel('Générer une clé');

        // TRAITEMENT
        if (\request()->has('generate') || \request()->has('revoke')) {
            $form->valid(\request()->all());
            if ($form->isValid()) {
                try {
                    $user->api_token = \request()->has('generate') ? str_random(60) : null;
                    $user->save();

                    \js()->success()->closeRemoteModal()->reloadDataTable();
                } catch(\Exception $e) {
                    \js()->error($e->getMessage());
                }
            }
        }
        return response()->modal($form);
    }

    /**
     *
     *
     * @param $id
     * @return mixed
     */
    public function postPassword($id)
    {
        \ruler()->check(
            $this->permission,
            ['id' => 'required:exists:user,user_id'],
            ['id' => $uuid = f($id, 'uuid')]
        );

        // Recuperation du model
        $user = User::findOrFail($uuid);

        $form = \form()->enableRemote();
        $form->setLegend('Utilisateur : ' . $user->name);
        $form->addText('password', 'Mot de passe');
        $form->addSubmit('Enregistrer');

        // enregistrement
        if (\request()->has('Enregistrer')) {
            $form->valid(\request()->all());
            if ($form->isValid()) {
                $data = $form->getFilteredAliasValues();
                try {
                    \FrenchFrogs\App\Models\Business\User::get($uuid)->changePassword($data['password']);
                    \js()->success()->closeRemoteModal()->reloadDataTable();
                } catch(\Exception $e) {
                    \js()->error($e->getMessage());
                }
            }
        } else {
            $form->populate(['password' => \Models\Business\User::generateRandomPassword()]);
        }

        return response()->modal($form);
    }

    /**
     *
     *
     * @param $id
     * @return mixed
     */
    public function postUser($id = null)
    {
        \ruler()->check(
            $this->permission,
            ['id' => 'exists:user,user_id'],
            ['id' => $uuid = f($id, 'uuid')]
        );

        // Recuperation du model
        $user = User::findOrNew($uuid);

        $form = \form()->enableRemote();
        $form->setLegend('Utilisateur : ' . $user->name);
        $form->addText('name', 'Nom');
        $form->addEmail('email', 'Email');
        if (!$user->exists) {
            $form->addText('password', 'Mot de passe');
        }
        $interface = UserInterface::orderBY('name')->pluck('name', 'user_interface_id');
        $form->addSelect('user_interface_id', 'Interface', $interface);

        $form->addSubmit('Enregistrer');

        // enregistrement
        if (\request()->has('Enregistrer')) {
            $form->valid(\request()->all());
            if ($form->isValid()) {
                $data = $form->getFilteredAliasValues();
                try {

                    if ($user->exists) {
                        $user->email = $data['email'];
                        $user->user_interface_id = $data['user_interface_id'];
                        $user->name = $data['name'];
                        $user->save();
                    } else {
                        \Models\Business\User::init($data['email'], $data['password'], $data['user_interface_id'], $data['name']);
                    }

                    \js()->success()->closeRemoteModal()->reloadDataTable();
                } catch(\Exception $e) {
                    \js()->error($e->getMessage());
                }
            }
        } elseif($user->exists) {
            $form->populate($user->toArray());
        } else {
            $form->populate(['password' => \FrenchFrogs\App\Models\Business\User::generateRandomPassword()]);
        }

        return response()->modal($form);
    }


	/**
	 *
	 *
	 * @param $id
	 * @return mixed
	 */
	public function postUserGroup($id)
	{
		\ruler()->check(
			$this->permission,
			['id' => 'exists:user,user_id'],
			['id' => $uuid = f($id, 'uuid')]
		);

		// Recuperation du model
		$user = \FrenchFrogs\App\Models\Business\User::get($uuid);

		$form = \form()->enableRemote();
		$form->setLegend('Groupes : ' . $user->getModel()->name);
		$groups = \query('user_group',[raw('HEX(user_group_id) as id'), 'name'])->orderBy('name')->pluck('name', 'id');

		$form->addCheckbox('user_group_id', 'Groupes', $groups )->addFilter('uuid', function($data) {
			if (is_array($data)){
				array_walk($data, function (&$v) {
					$v = f($v, 'uuid');
				});
			}
			return $data;
		});

		$form->addSubmit('Enregistrer');

		// enregistrement
		if (\request()->has('Enregistrer')) {
			$form->valid(\request()->all());
			if ($form->isValid()) {
				$data = $form->getFilteredAliasValues();
				try {
					$user->setGroups($data['user_group_id']);
					\js()->success()->closeRemoteModal()->reloadDataTable();
				} catch(\Exception $e) {
					dd($e);
					\js()->error($e->getMessage());
				}
			}
		} else {
			$groups = $user->getGroups();
			array_walk($groups, function(&$v) {$v = f($v,'uuid:hex|upper');});
			$form->populate(['user_group_id' => $groups]);
		}

		return response()->modal($form);
	}



	/**
     * List all users
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        \ruler()->check($this->permission);
        return basic('Utilisateurs', static::user());
    }


    /**
     * Edit user permissions
     *
     * @param $id
     * @return string
     */
    public function postPermissions($id)
    {
        \ruler()->check(
            $this->permission,
            ['id' => 'exists:user,user_id'],
            ['id' => $uuid = f($id, 'uuid')]
        );

        // Récuperation du model
        /** @var \Models\Business\User $user */
        $user = \FrenchFrogs\App\Models\Business\User::get($id);

        $query = \query('user_permission as p', [
            'i.name as interface_name',
            'g.user_permission_group_id',
            'g.name as group_name',
            'p.user_permission_id',
            'p.name'
        ])
            ->join('user_permission_group as g', 'p.user_permission_group_id', '=', 'g.user_permission_group_id')
            ->join('user_interface as i', 'i.user_interface_id', '=', 'p.user_interface_id')
            ->orderBy('i.name')
            ->orderBy('g.name')
            ->orderBy('p.name');

        $groups = [];
        $permissions = [];

        foreach($query->get() as $row) {

            // gestion des interfaces
            if (empty($permissions[$row['interface_name']])) {
                $permissions[$row['interface_name']] = [];
            }

            // gestion des groupes
            if (empty($permissions[$row['interface_name']][$row['user_permission_group_id']])) {
                $groups[$row['user_permission_group_id']] = $row['group_name'];// stackage des groupes
                $permissions[$row['interface_name']][$row['user_permission_group_id']] = [];
            }

            // gestion de la permissions
            $permissions[$row['interface_name']][$row['user_permission_group_id']][$row['user_permission_id']] = $row['name'];
        }
        // Formulaire
        $form = \form()->enableRemote();
        $form->setLegend('Permissions : ' .$user->getModel()->name);
        foreach($permissions as $interface => $group) {
            $form->addTitle($interface);
            foreach($group as $g => $p) {
                $form->addCheckbox(str_replace('.', '_', $g), $groups[$g], $p)->setAlias('user_permission_id');
            }
        }
        $form->addSubmit('Enregistrer');

        // enregistrement
        if (\request()->has('Enregistrer')) {
            $form->valid(\request()->all());
            if ($form->isValid()) {
                $data = $form->getFilteredAliasValues();
                try {
                    transaction(function () use ($user, $data) {
                        $user->setPermissions($data['user_permission_id']);
                    });

                    \js()->success()->closeRemoteModal()->reloadDataTable();
                } catch(\Exception $e) {
                    \js()->error($e->getMessage());
                }
            }
        } else {
            $form->populate(['user_permission_id' => $user->getPermissions()], true);
        }

        return response()->modal($form);
    }


    /**
     * @param $id
     * @throws \Exception
     */
    public function postParameter($id)
    {
        \ruler()->check(
            $this->permission,
            ['id' => 'exists:user,user_id'],
            ['id' => $uuid = f($id, 'uuid')]
        );

        // Recuperation du model
        $user = \FrenchFrogs\App\Models\Business\User::get($uuid);

        $form = \form()->enableRemote();
        $form->setLegend('Paramètres : ' . $user->getModel()->name);
        $form->addContent('??', 'Nothing here!');
        //@todo Make your form!!!!!
//        $form->addSubmit('Enregistrer');


        // enregistrement
        if (\request()->has('Enregistrer')) {
            $form->valid(\request()->all());
            if ($form->isValid()) {
                $data = $form->getFilteredAliasValues();
                try {
                    $user->setParameters($data);
                    \js()->success()->closeRemoteModal()->reloadDataTable();
                } catch(\Exception $e) {
                    \js()->error($e->getMessage());
                }
            }
        } else {
            $form->populate($user->getParameters());
        }

        return response()->modal($form);
    }

	/**
	 *
	 * Table de gestion des groupes
	 *
	 * @return \FrenchFrogs\Table\Table\Table
	 */
	public static function groups()
	{
		// QUERY
		$query = \query('user_group', [
			raw('HEX(id) as id'),
			'name'
		])
			->whereNull('deleted_at')
			->orderBy('name');

		// TABLE
		$table = \table($query);
		$table->setConstructor(static::class, __FUNCTION__)->enableRemote()->enableDatatable();
		$table->useDefaultPanel('Groupes')
			->getPanel()
			->addButton('add', 'Ajouter', action_url(static::class, 'postGroup'))
			->setOptionAsPrimary()
			->enableRemote();

		// COLMUMN
		$table->addText('name', 'Nom')->setStrainerText('name');

		// ACTION
		$action = $table->addContainer('action', 'Action')->right();
		$action->addButtonEdit(action_url(static::class, 'postGroup', '%s'), 'id');
		$action->addButtonDelete(action_url(static::class, 'deleteGroup', '%s'), 'id');
		return $table;
	}


	/**
	 * Accueil de la gestion des groupes
	 *
	 * @return mixed
	 */
	public function getGroups()
	{
		//RULER
		\ruler()->check(
			$this->permission
		);

		return basic('Utilisateurs : Groupes', static::groups());
	}


	/**
	 * Formulaire de modification et d'ajout de groupe
	 *
	 * @param null $id
	 * @return mixed
	 */
    public function postGroup($id = null)
    {
        //RULER
        \ruler()->check(
            $this->permission,
            ['id' => 'exists:user_group,user_group_id'],
            ['id' => $uuid = f($id, 'uuid')]
        );

        // MODEL
        $model = Group::findOrNew($uuid);

        // FORM
        $form = \form()->enableRemote();
        $form->setLegend('Groupes : ' . $model->exists ? $model->name : 'Ajouter');

        // ELEMENT
        $form->addText('name', 'Nom');
        $form->addSubmit('Enregistrer');

        // TRAITEMENT
        if (\request()->has('Enregistrer')) {
            $data = request()->all();
            $form->valid($data);
            if ($form->isValid()) {
                $data = $form->getFilteredValues();
                try {
                    $model->name = $data['name'];
                    $model->save();
                    \js()->success()->closeRemoteModal()->reloadDataTable();
                } catch (\Exception $e) {
                    \js()->error($e->getMessage());
                }
            }
        } else {
            $data = $model->toArray();
            $form->populate($data);
        }

        return response()->modal($form);
    }

	/**
	 * @name Artisan
	 * @generated 2016-07-05 11:13:42
	 * @see php artisan ffmake:action
	 * @param mixed $id
	 */
	public function deleteGroup($id)
	{
		//RULER
		\ruler()->check(
			$this->permission,
			['id' => 'exists:user_group,user_group_id'],
			['id' => $uuid = f($id, 'uuid')]
		);

		// MODEL
		$model = Group::findOrFail($uuid);

		// MODAL
		$modal = \modal(null, 'Etes vous sûr de vouloir supprimer : <b>' . $model->name . '</b>');
		$button = (new \FrenchFrogs\Form\Element\Button('yes', 'Supprimer !'))
			->setOptionAsDanger()
			->enableCallback('delete')
			->addAttribute('href', request()->url() . '?delete=1');
		$modal->appendAction($button);

		// TRAITEMENT
		if (\request()->has('delete')) {
			try {
				$model->delete();
				\js()->success()->closeRemoteModal()->reloadDataTable();
			} catch (\Exception $e) {
				\js()->error($e->getMessage());
			}
			return js();
		}

		return response()->modal($modal);
	}

    /**
     * Suppression d'un utilisateur
     *
     */
    public function deleteUser($id)
    {
        //RULER
        \ruler()->check(
            $this->permission,
            ['id' => 'exists:user,user_id'],
            ['id' => $uuid = f($id, 'uuid')]
        );

        // MODEL
        $model = User::findOrFail($uuid);

        // MODAL
        $modal = \modal(null, 'Etes vous sûr de vouloir supprimer : <b>' . $model->name . '</b>');
        $button = (new \FrenchFrogs\Form\Element\Button('yes', 'Supprimer !'))
            ->setOptionAsDanger()
            ->enableCallback('delete')
            ->addAttribute('href', request()->url() . '?delete=1');
        $modal->appendAction($button);

        // TRAITEMENT
        if (\request()->has('delete')) {
            try {
                $model->delete();
                \js()->success()->closeRemoteModal()->reloadDataTable();
            } catch (\Exception $e) {
                \js()->error($e->getMessage());
            }
            return js();
        }

        return response()->modal($modal);
    }
}