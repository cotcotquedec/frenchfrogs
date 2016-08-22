<?php namespace FrenchFrogs\Maker\Console;

use FrenchFrogs\Maker\Maker;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class MakePermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:permission {permission : id of the permission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * @var
     */
    protected $permission;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Filesystem $filesystem, Composer $composer)
    {

        // validation declaration
        $validator = \Validator::make(
            ['permission' => $permission = $this->argument('permission')],
            ['permission' => 'required|not_exists:user_permission,user_permission_id']
        );

        // check if argument are valid
        if ($validator->fails()) {
            $this->error($validator->getMessageBag()->toJson());
            return;
        }

        $this->info('Nous allons créer ensemble une migration');


        $rulerClass = $this->ask('Quelle est la classe de gestion des Acl?', configurator()->get('ruler.class'));
        $ruler = Maker::load($rulerClass);

        // NOM
        $nice_permission = str_replace('.', '_', $permission);
        $constant = $this->ask('Nom de la constante?', 'PERMISSION_' . strtoupper($nice_permission));
        $constant = strtoupper($constant);

        // ANALYSE DES CONSTANTES
        $groups = $interfaces = [];
        $interfaces['INTERFACE_DEFAULT'] = $rulerClass::INTERFACE_DEFAULT;
        foreach ($ruler->getConstants() as $name => $value) {
            if (preg_match('#^PERMISSION_GROUP_.+#', $name)) {
                $groups[$name] = $value;
            } else if (preg_match('#^INTERFACE_.+#', $name) && $name != 'INTERFACE_DEFAULT') {
                $interfaces[$name] = $value;
            } elseif ($value == $permission) {
                // si on remarque que la permission existe deja
                exc('La permission "'.$permission.'" existe déjà avec le nom : ' .$name);
            } elseif($name == $constant) {
                // si on remarque que la permission existe deja
                exc('Le nom "'.$permission.'" existe déjà avec la permission : ' . $value);
            }
        }

        $interface = $this->choice('A quelle interface voulez vous rattacher cette permission?', array_values($interfaces), 0);
        $interface = array_search($interface, $interfaces);

        // GROUP
        $group = $this->choice('A quel groupe voulez vous rattacher cette permission?', array_values($groups));
        $group = array_search($group, $groups);

        // création de la constante
        $ruler->addConstant($constant, $permission);
        $ruler->write();

        $label = strrpos($permission, '.');
        $label = $label ? substr($permission, $label + 1) : $permission;
        $label = $this->ask('Quelle est le libellé de cette Permission?', ucfirst($label));

        // Creation de la migration
        $this->info('Creation de la migration');
        $filename = 'create_permission_' . $nice_permission . '_' . \uuid('hex') ;
        $filepath = storage_path('tmp/') . $filename;
        $filesystem->delete($filepath);

        // CLASS
        $maker = Maker::init(camel_case($filename), $filepath);
        $maker->addAlias('Acl', $ruler->getClass()->getName());
        $maker->addAlias('Migration', Migration::class);
        $maker->setParent(Migration::class);
        $maker->setSummary('Migration pour l\'ajout de la permission "'.$permission.'" en base de donnée');

        // METHOD
        $method = $maker->addMethod('up');
        $body = sprintf('Acl::createDatabasePermission(Acl::%s, Acl::%s, Acl::%s, \'%s\');', $constant, $group, $interface, $label);
        $method->setBody($body);

        // FICHIER DE MIGRATION
        $path = $this->laravel->databasePath().'/migrations';
        $fullPath = $this->laravel['migration.creator']->create($filename, $path);

        $filesystem->put($fullPath, '<?php ' . $maker->render());
        $filesystem->delete($filepath);
        $this->info('Migration created successfully!');
        $composer->dumpAutoloads();

        $this->info('Have fun!!');
    }
}
