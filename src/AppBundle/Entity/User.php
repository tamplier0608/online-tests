<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Repository\Orders;
use AppBundle\Entity\Repository\User\PassedTests;
use CoreBundle\Db\Entity;

class User extends Entity
{
    protected static $table = 'users';
    protected static $avoidSaving = array('testResults', 'orders');

    protected $testResults = array();
    protected $orders = array();

    public function getPassedTests($force = false)
    {
        if (0 === count($this->testResults) || $force) {
            $testResultsRepository = new PassedTests();
            $this->testResults = $testResultsRepository->findBy(array('user_id=?'), array($this->id));
        }

        return $this->testResults;
    }

    public function getOrders($force = false)
    {
        if (0 === count($this->orders) || $force) {
            $ordersRepository = new Orders();
            $this->comments = $ordersRepository->findBy(array('customer_id=?'), array($this->id));
        }

        return $this->comments;
    }

    public function isTestPassed($testId)
    {
        $testResultsRepository = new PassedTests();
        $passedTest = $testResultsRepository->findBy(array('test_id=?', 'user_id=?'), array($testId, $this->id));

        return count($passedTest) > 0;
    }

}