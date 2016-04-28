<?php

namespace CoreBundle\Db;

class Error
{
    /**
     * @var integer Error code
     */
    protected $code;

    /**
     * @var string Erorr message
     */
    protected $message;

    /**
     * Constructor of error object
     *
     * @param integer $code Error code
     * @param string $message Error message
     */
    public function __construct($code, $message)
    {
        $this->setCode($code);
        $this->setMessage($message);
    }

    /**
     * Gets the Error code.
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets the Error code.
     *
     * @param integer $code
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Gets the error message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the error message.
     *
     * @param string $message the _message
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}