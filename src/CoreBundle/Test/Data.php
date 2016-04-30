<?php

namespace CoreBundle\Test;

class Data implements \Serializable
{
    private $result;
    private $currentQuestion;
    private $answers;
    private $isCompleted;
    private $userId;
    private $testId;
    protected $defaultSessionData = array(
        'result' => 0,
        'current_question' => 1,
        'answers' => array(),
        'completed' => false,
        'user_id' => false,
        'test_id' => false
    );

    public function __construct(array $data = array())
    {
        $this->setData($data);
    }

    /**
     * @return bool|mixed
     */
    public function getTestId()
    {
        return $this->testId;
    }

    /**
     * @param bool|mixed $testId
     */
    public function setTestId($testId)
    {
        $this->testId = $testId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function isEmpty()
    {
        return $this->getData() === $this->defaultSessionData;
    }
    
    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getCurrentQuestion()
    {
        return $this->currentQuestion;
    }

    /**
     * @param mixed $currentQuestion
     */
    public function setCurrentQuestion($currentQuestion)
    {
        $this->currentQuestion = (int) $currentQuestion;
    }

    /**
     * @return mixed
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param mixed $answers
     */
    public function setAnswers(array $answers)
    {
        $this->answers = $answers;
    }

    public function saveAnswer($number, $value)
    {
        $this->answers[$number] = $value;
    }

    public function hasAnswer($number)
    {
        return array_key_exists($number, $this->getAnswers());
    }

    /**
     * @return mixed
     */
    public function isCompleted()
    {
        return $this->isCompleted;
    }

    /**
     * @param mixed $isCompleted
     */
    public function setCompleted($isCompleted)
    {
        $this->isCompleted = (bool) $isCompleted;
    }

    public function getData()
    {
        return array(
            'result' => $this->getResult(),
            'current_question' => $this->getCurrentQuestion(),
            'answers' => $this->getAnswers(),
            'completed' => $this->isCompleted(),
            'user_id' => $this->getUserId(),
            'test_id' => $this->getTestId()
        );
    }

    /**
     * @param array $data
     */
    protected function setData(array $data)
    {
        $this->result = array_key_exists('result', $data) ? $data['result'] : 0;
        $this->currentQuestion = array_key_exists('current_question', $data) ? $data['current_question'] : 1;
        $this->answers = array_key_exists('answers', $data) ? $data['answers'] : array();
        $this->isCompleted = array_key_exists('completed', $data) ? $data['completed'] : false;
        $this->userId = array_key_exists('user_id', $data) ? $data['user_id'] : false;
        $this->testId = array_key_exists('test_id', $data) ? $data['test_id'] : false;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize($this->getData());
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->setData(unserialize($serialized));
    }


}