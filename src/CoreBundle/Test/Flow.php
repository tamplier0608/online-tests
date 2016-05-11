<?php

namespace CoreBundle\Test;

use CoreBundle\Test\Data as TestData;
use CoreBundle\Test\Flow\Calculate\Strategy\StrategyInterface;
use CoreBundle\Test\Storage\StorageInterface;

/**
 * Class Flow
 * @package CoreBundle\Test
 * @requires CoreBundle\Test\Data
 */
class Flow
{
    protected $storage;
    protected $testData;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function initTestData($testId, $userId = false)
    {
        $key = $this->getSessionKey();
        $this->storage->save($key, serialize(new TestData(array('test_id' => $testId, 'user_id' => $userId))));
    }

    public function isTestDataInitialized()
    {
        return null !== $this->storage->restore($this->getSessionKey());
    }
    
    public function saveTestData()
    {
        $key = $this->getSessionKey();
        $this->storage->save($key, serialize($this->getTestProgress()));
    }

    public function getTestProgress($force = false)
    {
        if (null === $this->testData || $force) {
            $key = $this->getSessionKey();
            $data = $this->storage->restore($key);

            if (!empty($data)) {
                $data = unserialize($data);
            }

            if (null === $data) {
                $this->testData = new TestData();
            } else {
                $this->testData = $data;
            }
        }
        return $this->testData;
    }

    public function removeTestData()
    {
        $key = $this->getSessionKey();
        $this->storage->remove($key);
    }

    /**
     * @return string
     */
    protected function getSessionKey()
    {
        return 'test_in_progress';
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function getNextQuestionNumber()
    {
        return $this->getTestProgress()->getCurrentQuestion()+1;
    }

    public function calculateResult(StrategyInterface $strategy)
    {
        return $strategy->processData($this->getTestProgress()->getAnswers());
    }
    

}