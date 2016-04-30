<?php

namespace AppBundle\Entity\User;

use AppBundle\Entity\Repository\Test\Results as TestResults;
use CoreBundle\Db\Entity;

class PassedTest extends Entity
{
    protected static $table = 'passed_tests';

    protected $testResult;
    protected static $avoidSaving = array('testResult');

    public function __construct()
    {
        parent::__construct(null);
        $this->testResult = null;
    }

    public function getTestResult()
    {
        if (null === $this->testResult) {
            $testResultsRepository = new TestResults();
            $this->testResult = $testResultsRepository->find($this->result_id);
        }
        return $this->testResult;
    }
}