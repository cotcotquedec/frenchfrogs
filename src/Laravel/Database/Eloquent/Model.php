<?php

namespace FrenchFrogs\Laravel\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class Model.
 *
 * @method static $this findOrNew() findOrNew($id)
 * @method static $this find() find($id)
 * @method static $this findOrFail() findOrFail($id)
 * @method static $this firstOrCreate() firstOrCreate(array $array)
 * @method static $this firstOrNew() firstOrNew(array $array)
 * @method static Builder orderBy() orderBy(string $column, string $direction = 'asc')
 * @method static Builder where() where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * Desactivate gard.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * primary key generation.
     *
     * @var bool
     */
    public $primaryUuid = false;

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $attributes
     *
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes)
    {
        $keyName = $this->getKeyName();

        // uuid management
        if ($this->primaryUuid) {
            $id = uuid();
            $attributes[$keyName] = $id;
            $query->insert($attributes);

        // auto increment
        } else {
            $id = $query->insertGetId($attributes, $keyName);
        }

        $this->setAttribute($keyName, $id);
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        if ($this->incrementing && !$this->primaryUuid) {
            return array_merge([
                $this->getKeyName() => 'int',
            ], $this->casts);
        }

        return $this->casts;
    }
}
