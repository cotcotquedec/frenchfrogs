<?php namespace FrenchFrogs\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Navigation extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $primaryKey = 'user_navigation_id';
    protected $table = 'user_navigation';


    /**
     * Renvoie la permissiopn lié aq l'ccès de cette page
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function permission()
    {
        return $this->hasOne(Permission::class, 'user_navigation_id', 'user_navigation_id');
    }

    /**
     * Renvoie l'interface
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userInterface()
    {
        return $this->belongsTo(UserInterface::class, 'user_navigation_id', 'user_navigation_id');
    }
}