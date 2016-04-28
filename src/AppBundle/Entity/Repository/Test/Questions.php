<?php

namespace AppBundle\Entity\Repository\Test;

use CoreBundle\Db\Repository;

class Questions extends Repository
{
    protected static $table = 'test_questions';
    protected static $rowClass = 'AppBundle\Entity\Test\Question';
}