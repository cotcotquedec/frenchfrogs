<?php

namespace FrenchFrogs\App\Console;

use FrenchFrogs\App\Models\Acl;
use Illuminate\Console\Command;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create 
                                {email : Email de l\'utilisateur} 
                                {--pass= : Mot de passe}
                                {--name= : Nom complet de l\'utilisateur}
                                {--interface= : Interface de l\'utilisateur}
                                {--admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Création d'un utilisateur";

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

        // email
        $email = $this->argument('email');

        // génération automatique de l'email
        $password = $this->option('pass');
        if (empty($password)) {
            $password = \Models\Business\User::generateRandomPassword();
            $password = $this->ask('Mot de passe', $password);
        }

        // nom complet
        $name = $this->option('name');
        if (empty($name)) {
            $name = $this->ask('Nom complet');
        }

        // interface
        $interface = $this->option('interface');
        if (empty($interface)) {
            $interface = $this->ask('Interface', Acl::INTERFACE_DEFAULT);
        }

        // generation de l'utilisateur
        \Models\Business\User::init($email, $password, $interface, $name, $this->option('admin'));

        // affichage du mot de passe
        $this->info(sprintf('Le mot de passe de l\'utilisateur "%s" [%s] est : %s', $name, $email, $password));
    }
}
