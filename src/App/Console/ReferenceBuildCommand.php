<?php

namespace Frenchfrogs\App\Console;

use FrenchFrogs\Models\Reference;
use Illuminate\Console\Command;

class ReferenceBuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reference:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Création du fichier de constant pour les constants';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Reference::build();
        $this->info('Fichier généré avec succès');
    }
}