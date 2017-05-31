<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\App\Models\Db\Content;
use FrenchFrogs\App\Models\Db\Reference;
use Illuminate\Console\Command;

class ContentBuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Construction des fichier de traduction';

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
        foreach (Reference::where('collection', 'lang')->get() as $lang) {
            $contents = Content::where('lang_sid', $lang->reference_id)->where('is_published', true)->pluck('content', 'content_index');
            file_put_contents( resource_path('/lang/' . $lang->data['file']), '<?php return ' . var_export($contents->toArray(), true) . ';' );
        }
    }
}
