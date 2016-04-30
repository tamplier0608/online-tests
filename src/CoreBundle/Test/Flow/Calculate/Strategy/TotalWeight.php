<?php

namespace CoreBundle\Test\Flow\Calculate\Strategy;

class TotalWeight implements StrategyInterface
{
    public function processData(array $data = array())
    {
        $totalWeight = 0;

        foreach ($data as $index => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $totalWeight += $v;
                }
            } else {
                $totalWeight += $value;
            }
        }
        return $totalWeight;
    }
}