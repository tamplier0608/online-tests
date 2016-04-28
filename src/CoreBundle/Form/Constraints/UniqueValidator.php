<?php

namespace CoreBundle\Form\Constraints;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Unique) {
            throw new UnexpectedTypeException(constraint, __NAMESPACE__.'\Unique');
        }

        $value = !empty($value) ? $value : '';

        $query = 'SELECT COUNT(*) as count FROM ' . $constraint->table . ' WHERE ' . $constraint->field . "='" . $value . "'";
        $result = $constraint->db->fetchAssoc($query);

        if ($result['count'] > 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Unique::IS_NOT_UNIQUE_ERROR)
                ->addViolation();
        }
    }
}