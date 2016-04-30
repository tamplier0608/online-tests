<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Repository\Test\Questions as TestQuestions;
use AppBundle\Entity\Repository\Test\Results as TestResults;
use CoreBundle\Db\Entity;

class Test extends Entity
{
    const STRATEGY_NAMESPACE = 'CoreBundle\Test\Flow\Calculate\Strategy\\';
    protected static $table = 'tests';

    protected $questions = array();
    protected $results = array();
    protected static $avoidSaving = array('questions', 'results');

    public function isFree()
    {
        return $this->price == 0;
    }

    public function getQuestions($force = false)
    {
        if (empty($this->questions) || $force) {
            $testOptionsRepository = new TestQuestions();
            $this->questions = $testOptionsRepository->findBy(array('test_id=?'), array($this->id));
        }
        return $this->questions;
    }

    public function getResults($force = false)
    {
        if (empty($this->results) || $force) {
            $testResultsRepository = new TestResults();
            $this->results = $testResultsRepository->findBy(array('test_id=?'), array($this->id));
        }
        return $this->results;
    }

    public function getCalcStrategy()
    {
        $strategyClass = $this->getStrategyClass();
        return new $strategyClass;
    }

    private function getStrategyClass()
    {
        $parts = explode('-', $this->calc_strategy);
        $className = implode('', array_map('ucfirst', $parts));

        return self::STRATEGY_NAMESPACE . $className;
    }
}