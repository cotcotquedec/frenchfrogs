<?php namespace FrenchFrogs\App\Console;

use Illuminate\Console\Command;
use PhpParser\Node\Stmt\Foreach_;

class DevContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and replace content instruction in wiew';

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
    public function handle()
    {
        // recupération de fichier de vue
        $files = \glob_recursive(resource_path('views') . '/*.blade.php');

        // traitement pour chaque
        foreach ($files as $file) {

            $content = file_get_contents($file);
            $replace = [];

            $match = [];
            if (!preg_match('#\{\!\!\s*_ff_c\(#', $content, $match)) {
                continue;
            }

            $start = [];
            $position = 0;

            // Recherche du déclencheur
            do {
                if ($position = strpos($content, '{!! _ff_c(', $position + 1)) {
                    $start[] = $position;
                }
            } while ($position);


            // Recherchde la fin de la function
            foreach ($start as $s) {

                $tmp = substr($content, $s);

                // recherche de la fin de la function
                $stop = [];
                $position = 0;

                // recuperation de toute les occurence de cloture
                do {
                    if ($position = strpos($tmp, '!!}', $position + 1)) {
                        $stop[] = $position;
                    }
                } while ($position);

                // ensuite on test pour chaque cloture la viabilité
                foreach($stop as $st) {

                    $function = trim(substr($tmp,3, $st - 3));

                    try {
                        $args = false;
                        eval('$args = ' . $function . ';');

                        if ($args) {
                            $new = str_replace($args[0], $args[1], $function);
                            $new = str_replace('_ff_c', 'c', $new);
                            $replace[] = [$function, $new];
                            continue 2;
                        }

                    } catch (\Exception $e) {
                        $this->error($e->getMessage());
                    }
                }
            }

            // remplacement du contenu
            foreach ($replace as $data) {
                list($old, $new) = $data;
                $content = str_replace($old, $new, $content);
            }

            // inscirption dans le fichier
            file_put_contents($file, $content);
        }
    }
}
