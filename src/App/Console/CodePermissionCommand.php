<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\App\Models\Db\User\Permission;
use FrenchFrogs\Maker\Maker;
use FrenchFrogs\App\Models\Acl;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class CodePermissionCommand extends CodeCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:permission {permission? : id of the permission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ajoute une permission';


    /**
     * @var
     */
    protected $permission;


    /**
     * Rulker contenant les permissions
     *
     * @var Maker
     */
    protected $ruler;


    /**
     * @var Maker
     */
    protected $migration;

    /**
     * Setter for $migration
     *
     * @param Maker $migration
     * @return $this
     */
    public function setMigration(Maker $migration)
    {
        $this->migration = $migration;
        return $this;
    }

    /**
     * Getter for $migration
     *
     * @return Maker
     */
    public function getMigration()
    {
        return $this->migration;
    }


    /**
     * Getter for $ruler
     *
     * @return Maker
     */
    public function getRuler()
    {
        return $this->ruler;
    }

    /**
     * Setter for ruler
     *
     * @param Maker $ruler
     * @return $this
     */
    public function setRuler(Maker $ruler)
    {
        $this->ruler = $ruler;
        return $this;
    }

    /**
     * Getter for $permission
     *
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Setter for $permission
     *
     * @param $permission
     * @return $this
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
        return $this;
    }


    /**
     *  CReation de la migration
     *
     * @param $filesystem
     * @param $name
     * @return Maker
     */
    public function migration($filesystem, $name)
    {
        // Creation de la migration
        $this->info('Creation de la migration');

        $filename = 'create_permission_' . $name . '_' . \uuid()->hex;
        $path = $this->laravel->databasePath() . '/migrations';
        $filepath = $this->laravel['migration.creator']->create($filename, $path);

        // CLASS
        $class = Maker::findClass($filepath);

        // On charge la migration
        require_once $filepath;

        // Cratiuon de la migration
        $migration = Maker::load($class);
        $migration->addAlias('Acl', $this->getRuler()->getClass()->getName());
        $migration->addAlias('Migration', Migration::class);
        $migration->setParent(Migration::class);
        $migration->setSummary('Migration pour l\'ajout de la permission "' . $this->getPermission() . '" en base de donnée');

        // METHOD
        $migration->addMethod('up');

        $this->setMigration($migration);

        return $migration;
    }


    /**
     * Choix du group
     *
     */
    public function group()
    {
        // RULER
        $ruler = $this->getRuler();

        // GROUP
        do {
            $groups = $ruler->getPermissionsGroupsConstants();
            $values = array_values($groups);
            array_unshift($values, static::CHOICE_NEW);
            $group = $this->choice('A quel groupe voulez vous rattacher cette permission?', $values, 0);

            if ($group == static::CHOICE_NEW) {
                $name = $this->ask('Comment voulez vous nommer le groupe?', $this->getPermission());
                $group = 'PERMISSION_GROUP_' . strtoupper(str_replace('.', '_', $name));
                if ($this->confirm('Créer le groupe ' . $group . '?', true)) {

                    // ajout de la constant
                    $label = $this->ask('Quel est le libélé du groupe?', ucfirst($name));
                    $ruler->addConstant($group, $name);

                    // ajout de la création du groupe a la migration
                    $up = $this->getMigration()->getMethod('up');
                    $up->appendBody(sprintf('Acl::createDatabasePermissionGroup(Acl::%s, \'%s\');', $group, $label));
                } else {
                    $group = false;
                }
            }
        } while (empty($group));


        $this->warn('Groupe ok : ' . $group);

        return $group;
    }

    /**
     * Execute the console command.
     *
     * @param Filesystem $filesystem
     * @param Composer $composer
     */
    public function handle(Filesystem $filesystem, Composer $composer)
    {
        // Recupération du paamètre
        $permission = $this->argument('permission');

        //PERMISSION
        do {
            if (empty($permission)) {
                $permission = $this->ask('Quel est le nom de la permission?');
            }

            // validation declaration
            $validator = \Validator::make(
                ['permission' => $permission],
                ['permission' => 'required|unique:user_permission,user_permission_id']
            );

            // check if argument are valid
            if ($validator->fails()) {
                $this->error($validator->getMessageBag()->toJson());
                $permission = false;
            }
        } while (empty($permission));

        $this->setPermission($permission);

        // RULER
        $rulers = Maker::findClasses(app_path('Models/Acl'));
        $rulerClass = $this->choice('Quelle est la classe de gestion des Acl?', $rulers, array_search('\\' . configurator()->get('ruler.class'), $rulers));
        $ruler = Maker::load($rulerClass);
        $this->setRuler($ruler);

        // NOM
        $nice_permission = str_replace('.', '_', $permission);
        $constant = $this->ask('Nom de la constante?', 'PERMISSION_' . strtoupper($nice_permission));
        $constant = strtoupper($constant);

        // MIGRATION : INIT
        $migration = $this->migration($filesystem, $nice_permission);

        // ANALYSE DES CONSTANTES
        foreach ($ruler->getConstants() as $name => $value) {
            if ($value == $permission) {
                // si on remarque que la permission existe deja
                exc('La permission "' . $permission . '" existe déjà avec le nom : ' . $name);
            } elseif ($name == $constant) {
                // si on remarque que la permission existe deja
                exc('Le nom "' . $permission . '" existe déjà avec la permission : ' . $value);
            }
        }

        // LABEL
        $label = strrpos($permission, '.');
        $label = $label ? substr($permission, $label + 1) : $permission;
        $label = $this->ask('Quelle est le libellé de cette Permission?', ucfirst($label));

        // création de la constante
        $ruler->addConstant($constant, $permission);

        // INTERFACE
        $interfaces = ref('interfaces')->pairs();
        $interface = $this->choice('A quelle interface voulez vous rattacher cette permission?', array_values($interfaces), 0);
        $interface = array_search($interface, $interfaces);

        // GROUPE
        $group = $this->group();

        // MAJ RULER
        $ruler->write();
        $migration->addAlias( 'Permission', Permission::class);

        // Inscirption de la migration
        $up = $migration->getMethod('up');
        $up->appendBody(<<<PHP
            
        Permission::create([
            'user_permission_id' => '$constant',
            'user_permission_group_id' => '$group',
            'interface_rid' => '$interface',
            'name' => '$label',
        ]);
             
PHP
);
        $migration->write();

        // RELOAD COMPOSER
        $composer->dumpAutoloads();

        $this->info('Have fun!!');
    }
}
