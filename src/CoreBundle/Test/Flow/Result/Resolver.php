<?php

namespace CoreBundle\Test\Flow\Result;

use AppBundle\Entity\Repository\Test\Results;
use AppBundle\Entity\Test;
use CoreBundle\Test\Flow\Calculate\Strategy\NumberCorrectAnswers;
use CoreBundle\Test\Flow\Calculate\Strategy\PredominantVariant;
use CoreBundle\Test\Flow\Calculate\Strategy\TotalWeight;

class Resolver
{
    /**
     * @param $resultValue
     * @param Test $test
     * @return bool|Test\Result
     */
    public function resolve($resultValue, Test $test)
    {
        $testResultsRepository = new Results();
        $strategy = $test->getCalcStrategy();

        if ($strategy instanceof TotalWeight) {
            $testResult = $testResultsRepository->getResultByPoints($test->id, $resultValue);
        } else if ($strategy instanceof PredominantVariant) {
            $testResult = $testResultsRepository->getResultByVariant($test->id, $resultValue);
        } else if ($strategy instanceof NumberCorrectAnswers) {
            $testResult = $testResultsRepository->getResultByPoints($test->id, $resultValue);
        } else {
            throw new \RuntimeException(__CLASS__ . ': Calculation strategy is unknown');
        }

        return $testResult;
    }
}