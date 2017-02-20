<?php namespace FrenchFrogs\App\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;

class GroupUser extends Model
{
    protected $primaryKey = 'user_group_user_id';
    public $timestamps = false;
    public $keyType = Model::BINARY16_UUID;
    protected $table = 'user_group_user';

}