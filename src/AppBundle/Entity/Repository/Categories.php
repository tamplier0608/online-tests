<?php

namespace AppBundle\Entity\Repository;

use CoreBundle\Db\Repository;

class Categories extends Repository
{
    protected static $table = 'categories';
    protected static $rowClass = 'AppBundle\Entity\Category';
}