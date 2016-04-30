<?php

namespace AppBundle\Entity\Test;

use AppBundle\Entity\Repository\Tests;
use CoreBundle\Db\Entity;

class Result extends Entity
{
    protected static $table = 'tests_results';

    protected $test;
    protected static $avoidSaving = array('test');

    public function getTest()
    {
        if (null === $this->test) {
            $testRepository = new Tests();
            $this->test = $testRepository->find($this->test_id);
        }
        return $this->test;
    }
}