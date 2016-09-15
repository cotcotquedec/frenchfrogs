<?php namespace FrenchFrogs\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model  implements \Illuminate\Contracts\Auth\Authenticatable  {
    use Authenticatable;
    use SoftDeletes;

    public $primaryUuid = true;
    protected $primaryKey  = 'user_id';
    protected $table = 'user';
    protected $hidden = ['password', 'remember_token'];


    /**
     * Permission liée a l'utilisateur
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(PermissionUser::class,'user_id',  'user_id');
    }

    /**
     * Groupe lié à l'utilisateur
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->hasMany(GroupUser::class, 'user_id', 'user_id');
    }
}

