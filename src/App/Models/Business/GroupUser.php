<?php

namespace FrenchFrogs\App\Models\Business;

use FrenchFrogs\App\Models\Db;
use FrenchFrogs\Business\Business;

/**
 * @deprecated
 *
 * Class GroupUser
 */
class GroupUser extends Business
{
    protected static $modelClass = Db\User\GroupUser::class;
}
