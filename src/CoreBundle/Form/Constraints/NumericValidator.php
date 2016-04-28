<?php

namespace CoreBundle\Form\Constraints;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NumericValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Numeric) {
            throw new UnexpectedTypeException(constraint, __NAMESPACE__.'\Numeric');
        }

        if (empty($value) || !is_numeric($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Numeric::IS_NOT_NUMERIC_ERROR)
                ->addViolation();
        }
    }
}