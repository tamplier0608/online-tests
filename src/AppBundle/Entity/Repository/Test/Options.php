<?php

namespace AppBundle\Entity\Repository\Test;

use CoreBundle\Db\Repository;

class Options extends Repository
{
    protected static $table = 'test_options';
    protected static $rowClass = 'AppBundle\Entity\Test\Option';
}