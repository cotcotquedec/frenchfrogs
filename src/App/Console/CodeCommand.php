<?php namespace FrenchFrogs\App\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class CodeCommand extends Command
{
    const CHOICE_NEW = ' > Nouveau';
    const CHOICE_NO_MORE = '> Fini';
    const CHOISE_NULL = '__null__';
    const CHOICE_LIST = '> Lister';
    const CHOICE_EMPTY = '> Auncun(e)';


    /**
     * Process
     *
     * @param $command
     */
    public function process($command)
    {

        // creation de la command
        $process = new Process($command);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // gestion des erreur
        if (!empty($error = $process->getErrorOutput())) {
            $this->error($error);
        }

        return $process->getOutput();
    }


    /**
     * Reforemat le code si
     *
     * Installer http://cs.sensiolabs.org/
     *  - $ wget https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v2.3.1/php-cs-fixer.phar -O php-cs-fixer
     *  - $ sudo chmod a+x php-cs-fixer
     *  - $ sudo mv php-cs-fixer /usr/local/bin/php-cs-fixer
     *
     * @see http://cs.sensiolabs.org/
     * @param $filepath
     */
    public function reformat($filepath)
    {
        $this->process('php-cs-fixer fix  --cache-file="/tmp/cs-fixer.cache" ' .  escapeshellarg($filepath));
        return $this;
    }


    /**
     *
     * Ask with a validator
     *
     * @param $question
     * @param null $default
     * @param null $validation
     * @param string $value
     * @return string
     */
    public function askUntilValid($question, $default = CodeCommand::CHOICE_EMPTY, $validation = null, $value = CodeCommand::CHOISE_NULL)
    {

        do {
            // on pose la question
            $value = $this->ask($question, $value == CodeCommand::CHOISE_NULL ? $default : $value);

            // cas de la vlaeur par default absente
            if ($value == CodeCommand::CHOICE_EMPTY){
                $value = '';
            }

            // validation
            if (!is_null($validation)) {
                $validator = \Validator::make([$value], [$validation]);
                if ($validator->fails()) {
                    $value = CodeCommand::CHOISE_NULL;
                    $this->warn($validator->errors()->first());
                }
            }

            // conditrion de boucle
        } while ($value == CodeCommand::CHOISE_NULL);

        return $value;
    }


    /**
     * Creation d'un repertoire s'il n'existe pas
     *
     * @param $dir
     * @return $this
     */
    public function makeDirectory($dir)
    {
        $filesystem = new Filesystem();

        if (!is_dir($dir)) {
            $filesystem->makeDirectory($dir);
        }

        return $this;
    }
}