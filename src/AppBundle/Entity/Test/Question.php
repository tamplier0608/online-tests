<?php

namespace AppBundle\Entity\Test;

use AppBundle\Entity\Repository\Test\Options as TestOptions;
use CoreBundle\Db\Entity;

class Question extends Entity
{
    protected static $table = 'test_questions';

    protected $options = array();
    protected static $avoidSaving = array('options');

    public function getOptions($force = false)
    {
        if (empty($this->options) || $force) {
            $testOptionsRepository = new TestOptions();
            $this->options = $testOptionsRepository->findBy(array('question_id=?'), array($this->id));
        }

        return $this->options;
    }
}