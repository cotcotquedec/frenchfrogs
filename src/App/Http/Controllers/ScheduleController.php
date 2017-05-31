<?php namespace FrenchFrogs\App\Http\Controllers;

use App\Http\Controllers\Controller;
use FrenchFrogs\App\Models\Db;
use Models\Business\User;
use FrenchFrogs\App\Models\Db\Schedule\Schedule;

trait ScheduleController
{

    /**
     * Permission necessaire pour l'accès a ces methode
     *
     * @var
     */
    protected $permission;

    /**
     * Create table for scheduler list
     *
     * @return \FrenchFrogs\Table\Table\Table
     */
    public static function schedule()
    {
        $query = \query('schedule')->orderBy('schedule_id');
        $table = \table($query);

        $table->setConstructor(static::class, __FUNCTION__)->enableRemote()->enableDatatable();
        $panel = $table->useDefaultPanel('Liste des commandes')->getPanel();
        $panel->addButton('add', 'Ajouter', action_url(static::class, 'postSchedule'))->setOptionAsInfo()->enableRemote();
        $table->addText('schedule_id', 'ID');
        $table->addText('command', 'Commande');
        $table->addText('schedule', 'Fréquence');
        $table->addText('description', 'Description');
        $table->addBoolean('can_overlapping', 'Chevauchement');
        $table->addBoolean('is_active', 'Active');
        $table->setSearch('command');

        $container = $table->addContainer('action', 'Actions')->setWidth('80');
        $container->addButtonCallback('execute', 'Executer', action_url(static::class, 'postExecute', '%s'), 'schedule_id')
            ->setOptionAsWarning()
            ->icon('fa fa-rocket');
        $container->addButtonEdit(action_url(static::class,'postSchedule', '%s'), 'schedule_id');
        $container->addButtonDelete(action_url(static::class,'deleteSchedule', '%s'), 'schedule_id');

        return $table;
    }

    /**
     * List all commands
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        \ruler()->check($this->permission);
        return view('basic', ['title' => 'Programmateur', 'content' => static::schedule()]);
    }


    /**
     * Edit a command
     *
     * @param $id
     * @return mixed
     */
    public function postSchedule($id = null)
    {
        \ruler()->check(
            $this->permission,
            ['id' => 'exists:schedule,schedule_id'],
            ['id' => $id]
        );

        $schedule = Schedule::findOrNew($id);

        // Form
        $form = \form()->enableRemote();
        $form->addText('schedule_id', 'ID')->setFilters('nowp|lower|alpha');
        $form->addText('command', 'Commande');
        $form->addTextarea('description', 'Description', false);
        $form->addText('schedule', 'Fréquence');
        $form->addBoolean('can_overlapping', 'Chevauchement');
        $form->addBoolean('is_active', 'Active');
        $form->addSubmit('Enregistrer');

        // Legende
        $form->setLegend($schedule->exists ? 'Commande : ' . $schedule->command : 'Ajouter une commande : ');

        // Traitement
        if (\request()->has('Enregistrer')) {
            $form->valid(\request()->all());
            if ($form->isValid()) {
                $data = $form->getFilteredValues();
                try {
                    if($schedule->exists) {
                        $schedule->update($data);
                    } else {
                        $schedule::create($data);
                    }
                    \js()->success()->reloadDataTable()->closeRemoteModal();
                } catch(\Exception $e) {
                    \js()->error($e->getMessage());
                }
            }
        } elseif($schedule->exists) {
            $form->populate($schedule->toArray());
        }

        return response()->modal($form);
    }


    /**
     * delete a command
     *
     * @param $id
     * @return mixed
     */
    public function deleteSchedule($id)
    {
        \ruler()->check(
            $this->permission,
            ['id' => 'required|exists:schedule,schedule_id'],
            ['id' => $id]
        );

        // Récuperation du model
        $schedule = Schedule::find($id);

        $modal = \modal(null, 'Etes vous sûr de vouloir supprimer : <b>' . $schedule->schedule_id .'</b>' );
        $button = (new \FrenchFrogs\Form\Element\Button('yes', 'Supprimer !'))
            ->setOptionAsDanger()
            ->enableCallback('delete')
            ->addAttribute('href',  action_url(static::class, __FUNCTION__, $id, ['delete' => true]));
        $modal->appendAction($button);

        // enregistrement
        if (\request()->has('delete')) {
            try {
                $schedule->delete();
                \js()->success()->closeRemoteModal()->reloadDataTable();
            } catch(\Exception $e) {
                \js()->error($e->getMessage());
            }
            return \js();
        }

        return response()->modal($modal);
    }


    /**
     * Execute a command
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function postExecute($id)
    {
        \ruler()->check(
            $this->permission,
            ['id' => 'required|exists:schedule,schedule_id'],
            ['id' => $id]
        );

        try {
            // Récuperation du model
            $schedule = Db\Schedule\Schedule::findOrFail($id);
            \Artisan::call($schedule->command);
            \js()->success()->reloadDataTable();
        } catch(\Exception $e) {
            \js()->error($e->getMessage());
        }
        return \js();
    }
}