<?php

namespace CoreBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class Unique extends Constraint
{
    const IS_NOT_UNIQUE_ERROR = 'online-tests-custom-validator-constraint-01';

    protected static $errorNames = array(
        self::IS_NOT_UNIQUE_ERROR => 'IS_NOT_UNIQUE_ERROR',
    );

    public $message = 'This value should be unique.';

    public $table;
    public $field;
    public $db;

    public function getRequiredOptions()
    {
        return array('table', 'field', 'db');
    }
}