<?php namespace FrenchFrogs\App\Models\Db\Media;

use FrenchFrogs\Laravel\Database\Eloquent\Model;

class Media extends Model
{

    protected $table = 'media';
    protected $primaryKey = 'uuid';
    public $keyType = Model::BINARY16_UUID;

    public function attachment()
    {
        return $this->hasOne(Attachment::class, 'uuid', 'uuid');
    }
}