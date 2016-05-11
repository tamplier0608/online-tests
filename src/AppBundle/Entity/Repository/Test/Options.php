<?php

namespace AppBundle\Entity\Repository\Test;

use CoreBundle\Db\Repository;

class Options extends Repository
{
    protected static $table = 'test_options';
    protected static $rowClass = 'AppBundle\Entity\Test\Option';

    public function getRightAnswersForTest($testId)
    {
        $query = $this->buildRightAnswersQuery();
        return $this->executeQuery($query, array($testId))->fetchAll();
    }

    private function buildRightAnswersQuery()
    {
        $query = 'SELECT test_questions.index as question_number, test_questions.value as question_title,
                    test_options.title as option_title, test_options.`index` as option_index, test_options.value as option_value
                  FROM tests
                  JOIN test_questions ON tests.id = test_questions.test_id
                  JOIN test_options ON test_questions.id = test_options.question_id
                  WHERE tests.id = ?
                    AND test_options.value = 1';

        return $query;
    }

    public function getAnswersDataByIds(array $ids)
    {
        $query = $this->buildAnswersQuery();
        $ids = implode(',', $ids);
        return $this->executeQuery($query, $ids)->fetchAll();
    }

    private function buildAnswersQuery()
    {
        $query = 'SELECT test_questions.index as question_number, test_questions.value as question_title,
                    test_options.title as option_title, test_options.value as option_value
                  FROM tests
                  JOIN test_questions ON tests.id = test_questions.test_id
                  JOIN test_options ON test_questions.id = test_options.question_id
                  WHERE tests.id = ?';

        return $query;
    }
}