<?php

namespace AppBundle\Entity\Repository\User;

use CoreBundle\Db\Repository;

class Groups extends Repository
{
    protected static $table = 'user_groups';
    protected static $rowClass = 'AppBundle\Entity\User\Group';
}