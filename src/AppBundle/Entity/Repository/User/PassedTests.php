<?php

namespace AppBundle\Entity\Repository\User;

use CoreBundle\Db\Repository;

class PassedTests extends Repository
{
    protected static $table = 'passed_tests';
    protected static $rowClass = 'AppBundle\Entity\User\PassedTest';
}