<?php

namespace CoreBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class Numeric extends Constraint
{
    const IS_NOT_NUMERIC_ERROR = 'online-tests-custom-validator-constraint-02';

    protected static $errorNames = array(
        self::IS_NOT_NUMERIC_ERROR => 'IS_NOT_NUMERIC_ERROR',
    );

    public $message = 'This value should be numeric.';
}