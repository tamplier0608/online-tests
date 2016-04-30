<?php

namespace CoreBundle\Test\Flow\Calculate\Strategy;

interface StrategyInterface
{
    public function processData(array $data);
}