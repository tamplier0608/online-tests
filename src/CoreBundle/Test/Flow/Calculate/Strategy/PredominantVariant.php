<?php

namespace CoreBundle\Test\Flow\Calculate\Strategy;

class PredominantVariant implements StrategyInterface
{
    public function processData(array $data)
    {
        $tmp = array();

        foreach ($data as $index => $variant) {
            if (array_key_exists($variant, $tmp)) {
                $tmp[$variant] += 1;
            } else {
                $tmp[$variant] = 1;
            }
        }

        $variants = array_keys($tmp);
        $frequences = array_values($tmp);

        # sort by frequence of variants
        array_multisort($frequences, SORT_DESC, $variants);

        return $variants[0];
    }
}