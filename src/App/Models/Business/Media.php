<?php namespace FrenchFrogs\App\Models\Business;

use FrenchFrogs\App\Models\Db;
use Illuminate\Http\UploadedFile;


class Media extends \FrenchFrogs\Business\Business
{
    static protected $modelClass = Db\Media\Media::class;

    /**
     * @param array $data
     * @return mixed
     */
    static public function create(array $data)
    {
        list($name, $type, $mime, $content) = $data;
        $media = parent::create(['media_type_rid' => $type, 'hash_md5' => md5($content)]);
        $media->getModel()->attachment()->create(['uuid' => $media->getId(), 'name' => $name, 'content' => $content, 'size' => strlen($content), 'mime' => $mime]);
        return $media;
    }

    /**
     * update file
     *
     * @param $name
     * @param $mime
     * @param $content
     * @return $this
     */
    public function update($name = null, $mime = null, $content = null)
    {

        $media = $this->getModel();
        $attachment = $media->attachment()->findOrFail($this->getId());

        // if content is set
        if (!is_null($content)) {
            $media->hash_md5 = md5($content);
            $media->save();
            $attachment->content = $content;
            $attachment->size = strlen($content);
        }

        //if name is set
        if (!is_null($name)) {
            $attachment->name = $name;
        }

        //if mime is set
        if (!is_null($mime)) {
            $attachment->mime = $mime;
        }

        // save
        $attachment->save();

        return $this;
    }

    /**
     * Add a type in database
     *
     * @param $id
     * @param $name
     */
    static public function createDatabaseType($id, $name)
    {
        Db\Media\Type::create([
            'media_type_id' => $id,
            'name' => $name,
        ]);
    }

    /**remove a type from database
     * @param $id
     */
    static public function removeDatabaseType($id)
    {
        Db\Media\Type::find($id)->delete();
    }


    /**
     *
     *
     * @return mixed
     */
    public function getMd5()
    {
        return $this->getModel()->first()->hash_md5;
    }
}