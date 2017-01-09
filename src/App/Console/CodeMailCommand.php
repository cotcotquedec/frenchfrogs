<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\Maker\Maker;
use Illuminate\Filesystem\Filesystem;
use FrenchFrogs\Laravel\Mail\Mailable;
use Illuminate\Support\Composer;

/**
 * Ajout d'un email
 *
 * Class CodeMailCommand
 * @package FrenchFrogs\App\Console
 */
class CodeMailCommand extends CodeCommand
{

    /**
     *
     * @var string
     */
    protected $classNamespace = 'app.mail.';

    /**
     *
     * @var string
     */
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
            $class->addProperty('textView', $text)->enablePublic();
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
            $class->addProperty('view', $text)->enablePublic();
        }

        // SUJET
        $subject = $this->ask('Ajouter un sujet?', static::CHOICE_NO_MORE);
        if ($subject != static::CHOICE_NO_MORE) {
            $class->addProperty('subject', $subject)->enablePublic();
        }

        // ecriture de la classe
        $class->write();

        $this->info('Have fun!!');
    }
}
