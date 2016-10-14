<?php

namespace FrenchFrogs\App\Models\Db\Media;

use FrenchFrogs\Laravel\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'media_attachment';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    public $primaryUuid = true;
}
