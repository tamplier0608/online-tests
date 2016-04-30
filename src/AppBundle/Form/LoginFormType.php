<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraint as Assert;

class LoginFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array(
                'constraints' => array(new Assert\NotBlank(array('message' => $app['translator']->trans('username.not_blank', array(), 'validation')))),
                'error_bubbling' => true
            ))
            ->add('password', 'password', array(
                'constraints' => array(new Assert\NotBlank(array('message' => $app['translator']->trans('password.not_blank', array(), 'validation')))),
                'error_bubbling' => true
            ))
            ->getForm();
    }
}