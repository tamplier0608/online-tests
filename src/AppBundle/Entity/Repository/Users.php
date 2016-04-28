<?php

namespace AppBundle\Entity\Repository;

use CoreBundle\Db\Repository;

class Users extends Repository
{
    protected static $table = 'users';
    protected static $rowClass = 'AppBundle\Entity\User';
}