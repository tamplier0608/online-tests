<?php

namespace AppBundle\Entity\Repository;

use CoreBundle\Db\Repository;

class Tests extends Repository
{
    protected static $table = 'tests';
    protected static $rowClass = 'AppBundle\Entity\Test';
}