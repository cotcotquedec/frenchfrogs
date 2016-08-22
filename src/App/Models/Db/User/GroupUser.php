<?php namespace FrenchFrogs\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;

class GroupUser extends Model
{
    protected $primaryKey = 'user_group_user_id';
    public $timestamps = false;
    public $uuid = true;
    protected $table = 'user_group_user';

}