<?php namespace FrenchFrogs\App\Models\Db\Schedule;

use Carbon\Carbon;
use FrenchFrogs\Laravel\Database\Eloquent\Model;

class Log extends Model {

    protected $primaryKey = 'schedule_log_id';
    protected $table = 'schedule_log';
    public $primaryUuid = true;


    /**
     * Fiinish a process
     *
     * @param $is_completed
     * @param null $message
     * @return $this
     */
    public function finish($is_completed)
    {
        $this->is_completed = $is_completed;
        $this->duration = raw('TIMESTAMPDIFF(SECOND, created_at, NOW())');
        $this->finished_at = Carbon::now();
        return $this;
    }

}