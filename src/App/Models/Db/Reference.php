<?php

namespace FrenchFrogs\App\Models\Db;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reference extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'reference_id';
    public $incrementing = false;
    protected $table = 'reference';
}
