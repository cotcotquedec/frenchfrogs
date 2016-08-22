<?php namespace FrenchFrogs\Models\Db\Media;


use FrenchFrogs\Laravel\Database\Eloquent\Model;

class Attachment extends Model
{

    protected $table = 'media_attachment';
    protected $primaryKey = 'media_id';
    public $incrementing = false;
    protected $fillable = ['media_id', 'name', 'content', 'size', 'mime'];
}