<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Repository\Orders;
use AppBundle\Entity\Repository\User\Groups as UserGroups;
use AppBundle\Entity\Repository\User\PassedTests;
use CoreBundle\Db\Entity;

class User extends Entity
{
    const ROLE_STUDENT = 'STUDENT';
    const ROLE_TEACHER = 'TEACHER';

    protected static $table = 'users';
    protected static $avoidSaving = array('testResults', 'orders', 'group');

    protected $testResults = array();
    protected $orders = array();
    protected $group;

    public function hasRole($role)
    {
        return $this->role == $role;
    }

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

    public function getGroup()
    {
        if (null === $this->group) {
            $groupRepository = new UserGroups();
            $this->group = $groupRepository->findBy(array('id = ?'), array($this->group_id))[0];
        }
        return $this->group;
    }

}