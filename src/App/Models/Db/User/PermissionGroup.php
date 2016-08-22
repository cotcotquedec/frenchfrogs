<?php namespace FrenchFrogs\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionGroup extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'user_permission_group_id';
    protected $table = 'user_permission_group';
    public $timestamps = false;
    public $incrementing = false;
}