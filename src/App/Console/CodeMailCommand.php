<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\Maker\Maker;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Composer;


class CodeMailCommand extends CodeCommand
{

    protected $classNamespace = 'app.mail.';

    protected $viewPath = 'resources/views/';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:mail {name? : Nom du mail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ajoute un mail';

    /**
     * Execute the console command.
     *
     * @param Filesystem $filesystem
     * @param Composer $composer
     */
    public function handle(Filesystem $filesystem, Composer $composer)
    {
        // nom du controller
        $name = $this->argument('name');

        //PERMISSION
        do {
            if (empty($name)) {
                $name = $this->ask('Quel est le nom de la class? (sans le app.name)');
            }
        } while (empty($name));

        // creation de la class
        $class = Maker::initFromShortName($this->classNamespace . $name);
        $class->addAlias('Mailable', Mailable::class);
        $class->setParent(Mailable::class);
        $class->addMethod('build');

        // PARAMS
        if ($this->confirm('cette email a t il des paramètres?', true)) {

            // constructeur
            $method = $class->addMethod('__construct');

            do {
                $param = $this->ask('Quel est le nom de du paramètre àjouter ?', static::CHOICE_NO_MORE);

                if ($param != static::CHOICE_NO_MORE) {
                    $method->addParameter($param);
                    $class->addProperty($param)->enablePublic();
                    $method->appendBody(sprintf('$this->%1$s = $%1$s;', $param));
                }
            } while($param != static::CHOICE_NO_MORE);
        }

        // TEXT
        if ($this->confirm('Dois-je créer une version text du mail?', true)) {

            $text = 'emails.' . $name . '_text';
            $text = $this->ask('Quel est le nom de la vue ?', $text);

            // creation du fichier
            $textFile = app_path('../' . $this->viewPath) .  str_replace('.', DIRECTORY_SEPARATOR, $text) . '.blade.php';
            $this->makeDirectory(dirname($textFile));
            $filesystem->put($textFile, '@todo');

            // ajout de la propriété
            $class->addProperty('textView', $text)->enableProtected();
        }


        // HTML
        if ($this->confirm('Dois-je créer une version html du mail?', true)) {

            $text = 'emails.' . $name;
            $text = $this->ask('Quel est le nom de la vue ?', $text);

            // creation du fichier
            $textFile = app_path('../' . $this->viewPath) .  str_replace('.', DIRECTORY_SEPARATOR, $text) . '.blade.php';
            $this->makeDirectory(dirname($textFile));
            $filesystem->put($textFile, '@todo');

            // ajout de la propriété
            $class->addProperty('view', $text)->enableProtected();
        }

        // SUBJECT


        $class->write();

        dd($class);

/*
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
        $interfaces = $ruler->getInterfacesConstants();
        $interface = $this->choice('A quelle interface voulez vous rattacher cette permission?', array_values($interfaces), 0);
        $interface = array_search($interface, $interfaces);

        // GROUPE
        $group = $this->group();

        // MAJ RULER
        $ruler->write();

        // Inscirption de la migration
        $up = $migration->getMethod('up');
        $up->appendBody(sprintf('Acl::createDatabasePermission(Acl::%s, Acl::%s, Acl::%s, \'%s\');', $constant, $group, $interface, $label));
        $migration->write();

        // RELOAD COMPOSER
        $composer->dumpAutoloads();
*/
        $this->info('Have fun!!');
    }
}
