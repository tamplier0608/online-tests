<?php

namespace CoreBundle\Test;

use CoreBundle\Test\Storage\StorageInterface;
use CoreBundle\Test\Data as TestData;

/**
 * Class Flow
 * @package CoreBundle\Test
 * @requires CoreBundle\Test\Data
 */
class Flow
{
    protected $storage;
    protected $defaultSessionData = array(
        'result' => 0,
        'next_question' => 1,
        'answers' => array(),
        'completed' => false
    );
    protected $storageKey = 'test-%s';
    protected $testId;
    protected $testData;

    public function __construct($testId, StorageInterface $storage)
    {
        $this->storage = $storage;
        $this->testId = $testId;
    }

    public function initTestData()
    {
        $key = $this->getSessionKey();
        $this->storage->save($key, $this->defaultSessionData);
    }

    /**
     * @return mixed
     */
    public function getTestId()
    {
        return $this->testId;
    }

    /**
     * @param mixed $testId
     */
    public function setTestId($testId)
    {
        $this->testId = $testId;
    }


    public function saveTestData()
    {
        $key = $this->getSessionKey();
        $this->storage->save($key, $this->getTestData()->getData());
    }

    public function getTestData($force = false)
    {
        if (null === $this->testData || $force) {
            $key = $this->getSessionKey();
            $data = $this->storage->restore($key);

            if (null === $data) {
                $data = array();
            }

            $this->testData = new TestData($data);
        }
        return $this->testData;
    }

    public function removeTestData()
    {
        $key = $this->getSessionKey();
        $this->storage->remove($key);
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function getNextQuestionNumber()
    {
        $testData = $this->getTestData();
        $answers = $testData->getAnswers();

        if (!count($answers)) {
            $nextQuestion = 1;
        } else {
            $nextQuestion = count($testData->getAnswers()) + 1;
        }

        return $nextQuestion;
    }

    public function update()
    {
        
    }
    
    /**
     * @param $testId
     * @return mixed
     */
    protected function getSessionKey($testId = null)
    {
        if (null !== $testId) {
            $key = sprintf($this->storageKey, $testId);
        } else {
            $key = sprintf($this->storageKey, $this->getTestId());
        }
        return $key;
    }

}