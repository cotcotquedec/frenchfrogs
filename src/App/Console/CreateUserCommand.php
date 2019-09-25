<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\Maker\Maker;

class CreateUserCommand extends CodeCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create 
                                {email? : Email de l\'utilisateur} 
                                {--pass= : Mot de passe}
                                {--name= : Nom complet de l\'utilisateur}
                                {--interface= : Interface de l\'utilisateur}';

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

        $email = $this->argument('email') ?: static::CHOISE_NULL;
        $email = $this->askUntilValid('Quel est l\'email de l\'utilisateur?', null, 'required|min:6|email|unique:users,email', $email);


        // génération automatique de l'email
        $password = $this->option('pass') ?: \Str::random(12);
        $password = $this->askUntilValid('Mot de passe?', null, 'required|min:6', $password);

        // nom complet
        $name = $this->option('name');
        $name = $this->askUntilValid('Nom complet?', null, 'required|min:3', $name);

        // interface
        $interface = $this->choice('Interface?', ref('interfaces')->pairs());

        // Creation de l'utilisateur
        $model = Maker::getModelFromTableName('users');

        $model->create([
            'email' => $email,
            'name' => $name,
            'password' => bcrypt($password),
            'interface_rid' => $interface,
        ]);

        // affichage du mot de passe
        $this->info(sprintf('Le mot de passe de l\'utilisateur "%s" [%s] est : %s', $name, $email, $password));
    }
}
