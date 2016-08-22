<?php namespace FrenchFrogs\Models\Db\Media;

use FrenchFrogs\Laravel\Database\Eloquent\Model;

class Media extends Model
{

    protected $table = 'media';
    protected $primaryKey = 'media_id';
    public $uuid = true;
    protected $fillable = ['media_type_id', 'hash_md5'];

    public function attachment()
    {
        return $this->hasOne(Attachment::class, 'media_id', 'media_id');
    }
}