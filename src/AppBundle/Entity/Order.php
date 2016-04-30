<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Repository\Tests;
use CoreBundle\Db\Entity;

class Order extends Entity
{
    protected static $table = 'orders';

    private $content;
    protected static $avoidSaving = array('content');

    public function getContent()
    {
        if (null === $this->content) {
            $testRepository = new Tests();
            $this->content = $testRepository->find($this->test_id);
        }

        return $this->content;
    }
}