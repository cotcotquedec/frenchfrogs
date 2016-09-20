<?php

namespace FrenchFrogs\App\Console;

use FrenchFrogs\App\Models\Business\Mail;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class MailSendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie les emails';



    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        while(Mail::next() === true) {}
    }

}
