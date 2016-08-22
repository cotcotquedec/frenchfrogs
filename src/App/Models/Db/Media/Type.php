<?php namespace FrenchFrogs\Models\Db\Media;


use FrenchFrogs\Laravel\Database\Eloquent\Model;

class Type extends Model
{

    protected $table = 'media_type';
    protected $primaryKey = 'media_type_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['media_type_id', 'name'];

}