<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\App\Models\Acl;
use Illuminate\Console\Command;
use App\Models\Db\Users\Interfaces;
use App\Models\Db\Users\Users;

class ChangeUserPasswordCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:password 
                                {email : Email de l\'utilisateur} 
                                {--interface= : Interface de l\'utilisateur}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Génération d'un password pour un utilisateur";

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
        $password = str_random(8);

        // interface
        $interface = $this->hasArgument('interface') ? $this->argument('interface') : Acl::INTERFACE_DEFAULT;

        // on valide que l'utilisateur n'existe pas déjà
        $user = Users::where('email', $email)
            ->where('interface_sid', $interface)
            ->firstOrFail();

        // création de l'utilisateur
        $user->password = bcrypt($password);
        $user->save();

        // affichage du mot de passe
        $this->info('Le mot de passe généré est : ' . $password );
    }
}
