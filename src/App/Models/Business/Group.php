<?php namespace FrenchFrogs\Models\Business;

use FrenchFrogs\Models\Db;
use FrenchFrogs\Business\Business;

/**
 * @deprecated
 *
 * Class Group
 * @package FrenchFrogs\Models\Business
 */
class Group extends Business
{
    static protected $modelClass = Db\User\Group::class;
}