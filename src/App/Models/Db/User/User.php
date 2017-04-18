<?php namespace FrenchFrogs\App\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model  implements \Illuminate\Contracts\Auth\Authenticatable  {
    use Authenticatable;
    use SoftDeletes;

    public $keyType = Model::BINARY16_UUID;
    protected $primaryKey  = 'user_id';
    protected $table = 'user';
    protected $hidden = ['password', 'remember_token'];


    /**
     *
     *
     */
    protected $casts = [
        "user_id" => Model::BINARY16_UUID,
        'media_id' => Model::BINARY16_UUID
    ];

    /**
     * Permission liée a l'utilisateur
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permission_user', 'user_uuid', 'permission_uuid');
    }


    /**
     * Return TRUE has the permission
     *
     * @param $ability
     * @return bool
     */
    public function can($ability)
    {
        return $this->permissions->where('user_permission_id', $ability)->isNotEmpty();
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

