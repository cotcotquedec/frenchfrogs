<?php namespace FrenchFrogs\App\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'user_permission_id';
    protected $table = 'user_permission';
    public $timestamps = false;
    public $incrementing = false;


    /**
     * Renvoie les utilisateurs ayant accès a cette permission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permission_user', 'user_permission_id', 'user_id');
    }
}