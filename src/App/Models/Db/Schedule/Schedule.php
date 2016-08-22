<?php namespace FrenchFrogs\Models\Db\Schedule;

use FrenchFrogs\Laravel\Database\Eloquent\Model;


/**
 *
 * Class Schedule
 *
 * @method static \Illuminate\Database\Eloquent\Builder active() active() Scope for active schedule
 * @package FrenchFrogs\Models\Db\Schedule
 */
class Schedule extends Model {
    protected $primaryKey = 'schedule_id';
    protected $table = 'schedule';
    public $incrementing = false;


    /**
     * Scope a query to only include active users.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('is_active', 1);
    }

}
