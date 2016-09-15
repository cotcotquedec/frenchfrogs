<?php namespace FrenchFrogs\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'user_group_id';
    public $timestamps = false;
    public $primaryUuid = true;
    protected $table = 'user_group';


    /**
     * Renvoie les utilisateurs associés au groupe
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_group_user', 'user_group_id', 'user_id');
    }
}