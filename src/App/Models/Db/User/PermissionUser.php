<?php namespace FrenchFrogs\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;

class PermissionUser extends Model
{
    protected $primaryKey = 'user_permission_user_id';
    protected $table = 'user_permission_user';
    public $timestamps = false;
    public $uuid = true;
}