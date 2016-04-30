<?php

namespace AppBundle\Entity\Repository;

use CoreBundle\Db\Repository;

class Orders extends Repository
{
    protected static $table = 'orders';
    protected static $rowClass = 'AppBundle\Entity\Order';

    public function isTestPurchasedByUser($testId, $userId)
    {
        $orders = $this->findBy(array('customer_id=?', 'test_id=?'), array($userId, $testId));
        return count($orders) > 0;
    }
}