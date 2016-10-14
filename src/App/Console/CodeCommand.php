<?php

namespace FrenchFrogs\App\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

abstract class CodeCommand extends Command
{
    const CHOICE_NEW = ' > Nouveau';
    const CHOICE_NO_MORE = '> Fini';
    const CHOISE_NULL = 'null';

    /**
     * Creation d'un repertoire s'il n'existe pas.
     *
     * @param $dir
     *
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
