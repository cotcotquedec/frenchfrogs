<?php namespace FrenchFrogs\App\Models\Db\Schedule;

use Carbon\Carbon;
use FrenchFrogs\Laravel\Database\Eloquent\Model;

/**
 * Class Log
 *
 *
 * @property Carbon created_at
 * @property Carbon updated_at
 * @package FrenchFrogs\App\Models\Db\Schedule
 */
class Log extends Model {

    protected $primaryKey = 'schedule_log_id';
    protected $table = 'schedule_log';
    public $primaryUuid = true;


    /**
     *
     *
     */
    protected $dates = [
        "created_at",
        "updated_at"
    ];


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
        $this->duration = $this->created_at->diffInSeconds(Carbon::now());
        $this->finished_at = Carbon::now();
        return $this;
    }
}