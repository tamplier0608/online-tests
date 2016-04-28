<?php

namespace CoreBundle\Test;

class Data
{
    private $result;
    private $nextQuestion;
    private $answers;
    private $isCompleted;
    protected $defaultSessionData = array(
        'result' => 0,
        'next_question' => 1,
        'answers' => array(),
        'completed' => false
    );

    public function __construct(array $data = array())
    {
        $this->result = array_key_exists('result', $data) ? $data['result'] : 0;
        $this->nextQuestion = array_key_exists('next_question', $data) ? $data['next_question'] : 1;
        $this->answers = array_key_exists('answers', $data) ? $data['answers'] : array();
        $this->isCompleted = array_key_exists('completed', $data) ? $data['completed'] : false;
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
     * @param bool $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getNextQuestion()
    {
        return $this->nextQuestion;
    }

    /**
     * @param mixed $nextQuestion
     */
    public function setNextQuestion($nextQuestion)
    {
        $this->nextQuestion = (int) $nextQuestion;
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

    public function setAnswer($number, $value)
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
            'next_question' => $this->getNextQuestion(),
            'answers' => $this->getAnswers(),
            'completed' => $this->isCompleted()
        );
    }

}