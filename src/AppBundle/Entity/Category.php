<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Repository\Tests;
use CoreBundle\Db\Entity;

class Category extends Entity
{
    protected static $table = 'categories';

    private $tests = array();
    protected static $avoidSaving = array('tests');

    public function getTests($limit = false, $orderBy = false)
    {
        if (0 === count($this->tests)) {
            $testRepository = new Tests();
            $this->tests = $testRepository->findBy(array('category_id = ?'), array($this->id), $limit, $orderBy);
        }

        return $this->tests;
    }
}