<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Repository\User\PassedTests;
use AppBundle\Entity\Repository\Comments;
use AppBundle\Entity\Repository\Orders;
use CoreBundle\Db\Entity;

class User extends Entity
{
    protected static $table = 'users';
    protected static $avoidSaving = array('testResults', 'comments', 'orders');

    protected $testResults = array();
    protected $comments = array();
    protected $orders = array();

    public function getPassedTests($force = false)
    {
        if (empty($this->testResults) || $force) {
            $testResultsRepository = new PassedTests();
            $this->testResults = $testResultsRepository->findBy(array('user_id=?'), array($this->id));
        }

        return $this->testResults;
    }

    public function getComments($force = false)
    {
        if (empty($this->comments) || $force) {
            $commentsRepository = new Comments();
            $this->comments = $commentsRepository->findBy(array('user_id=?'), array($this->id));
        }

        return $this->comments;
    }

    public function getOrders($force = false)
    {
        if (empty($this->orders) || $force) {
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