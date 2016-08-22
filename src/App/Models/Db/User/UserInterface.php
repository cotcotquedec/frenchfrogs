<?php namespace FrenchFrogs\Models\Db\User;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInterface extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'user_interface_id';
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'user_interface';


    public function navigations()
    {
        return $this->hasMany(Navigation::class, 'user_interface_id', 'user_interface_id');
    }
}