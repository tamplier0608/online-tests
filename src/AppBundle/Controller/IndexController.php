<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Repository\User\PassedTests;
use AppBundle\Entity\Repository\Test\Options as TestOptions;
use AppBundle\Entity\Repository\Test\Questions as TestQuestions;
use AppBundle\Entity\Repository\Test\Results as TestResults;
use AppBundle\Entity\Repository\Tests;
use AppBundle\Entity\Test;
use AppBundle\Entity\User\PassedTest;
use CoreBundle\Test\Storage\Session;
use CoreBundle\Test\Flow as TestFlow;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IndexController
{
    protected $testFlow;

    public function getTestFlow($testId, $app)
    {
        if (null === $this->testFlow) {
            $this->testFlow = new TestFlow($testId, new Session($app['session']));
        }
        return $this->testFlow;
    }
    
    public function indexAction(Request $request, \Application $app)
    {
        $testsRepository = new Tests();

        return $app['twig']->render('index/index.html.twig', array(
            'tests' => $testsRepository->fetchAll()
        ));
    }

    /**
     * @Method GET
     */
    public function doTestAction(Request $request, \Application $app)
    {
        $testId = $request->get('id');
        $testFlow = $this->getTestFlow($testId, $app);
        
        $testData = $testFlow->getTestData();

        if ($testData->isEmpty() or $testData->isCompleted()) {
            $testFlow->initTestData();
        }

        $testData = $testFlow->getTestData($force = true);
        $answers = $testData->getAnswers();
        $nextQuestion = 1;

        if (!is_null($answers)) {
            $nextQuestion = $testFlow->getNextQuestionNumber();
        }

        list($test, $question) = $this->getQuestionData($testId, $nextQuestion);

        if (empty($test)) {
            throw new NotFoundHttpException($app['translator']->trans('test.not_found'));
        }

        return $app['twig']->render('index/test.html.twig', array(
            'test' => $test,
            'question' => $question,
        ));
    }

    /**
     * @Method POST
     * @TODO need refactoring
     */
    public function processTestAction(Request $request, \Application $app)
    {
        $testId = $request->get('id');
        $testFlow = $this->getTestFlow($testId, $app);

        $prevQuestion = (int) $app['request']->get('question_number', 1);
        $nextQuestion = (int) $testFlow->getNextQuestionNumber();
        $answer = $app['request']->get('option');

        # set result according to POST data
        if (
            # check if it is first attempt to answer this question
            false === $testFlow->getTestData()->hasAnswer($nextQuestion) &&

            # check if it's right number of question. We have not saved new answer yet so
            # number of the next question should be equal to number of previous question
            $nextQuestion === $prevQuestion
        ) {
            $currentResult = $testFlow->getTestData()->getResult(); # get current result from PHP session

            if (null !== $answer) {
                $result = $currentResult + $answer;

                $testFlow->getTestData()->setResult($result);
                $testFlow->getTestData()->setAnswer($nextQuestion, $answer);

                $question['number'] = ++$nextQuestion;
            } else {
                $app['session']->getFlashBag()->set('danger', $app['translator']->trans('error.empty_answer'));
            }
        } else {
            $app['session']->getFlashBag()->set('warning', $app['translator']->trans('error.repeat_answer'));
            $question['number'] = $nextQuestion;
        }

        if ($app['debug']) {
            $answers = $testFlow->getTestData()->getAnswers();
            echo '<pre>';
            echo 'Answer: ' . var_export(!is_null($answer), true) . '<br />';
            echo 'Count answers: ' . var_export(count($answers), true) . '<br />';
            echo 'Next question: ' . var_export($nextQuestion, true) . '<br />';
            echo 'Last answered question: ' . var_export(count($answers), true) . '<br />';
            echo '</pre>';
        }

        $data = $this->getQuestionData($testId, $nextQuestion);

        if (false == $data) {
            $app['session']->getFlashBag()->set('success', $app['translator']->trans('test.result_success'));

            if (false === $testFlow->getTestData()->isCompleted()) {
                $test = new Test($testId);
                $test->passed = $test->passed + 1;
                $test->save();

                $this->savePassedTestRecord($app, $testFlow, $testId);
                
                $testFlow->getTestData()->setCompleted(true);
                $testFlow->saveTestData(); # save test data in session
            }

            return $app->redirect($app['url_generator']->generate('result_page', array('id' => $testId)));
        }

        list($test, $question) = $data;

        if (empty($test)) {
            throw new NotFoundHttpException($app['translator']->trans('test.not_found'));
        }

        $testFlow->getTestData()->setNextQuestion($nextQuestion);
        $testFlow->saveTestData(); # save data in session

        return $app['twig']->render('index/test.html.twig', array(
            'test' => $test,
            'question' => $question,
        ));
    }

    protected function savePassedTestRecord(\Application $app, $testFlow, $testId)
    {
        $points = $testFlow->getTestData()->getResult();
        $testResultsRepository = new TestResults();
        $testResult = $testResultsRepository->getDescriptionByPoints($testId, $points);

        $testResult['points'] = $points;

        if ($user = $this->isLoggedUser($app)) {
            $passedTest = new PassedTest();
            $passedTest->user_id = $user->id;
            $passedTest->result_id = $testResult->id;
            $passedTest->points = $points;
            $passedTest->test_id = $testId;
            $passedTest->test_data = serialize($testFlow->getTestData());

            $now = new \DateTime();
            $passedTest->passed_at = $now->format('Y-m-d H:i:s');

            $passedTest->save();
        }
    }

    public function finishTestAction(Request $request, \Application $app)
    {
        $testId = $request->get('id');
        $testFlow = $this->getTestFlow($testId, $app);

        $points = $testFlow->getTestData()->getResult();
        $testResultsRepository = new TestResults();
        $testResult = $testResultsRepository->getDescriptionByPoints($testId, $points);

        if (!$testResult) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $testResult['points'] = $points;

        return $app['twig']->render('index/result.html.twig', array(
            'result' => $testResult,
        ));
    }

    /**
     * Returns question data of false if test is over
     * @param $id
     * @param int $questionNumber
     * @return array|bool
     */
    private function getQuestionData($id, $questionNumber = 1)
    {
        $testRepository = new Tests();
        $test = $testRepository->find($id);

        $questionRepository = new TestQuestions();
        $questionCount = $questionRepository->countAll(array('test_id = ?'), array($test['id']));

        if ($questionNumber > $questionCount) {
            return false; # it means test is over
        }

        $question = $questionRepository->findBy(
            array('test_id = ?'),
            array($test['id']),
            ($questionNumber - 1) . ', 1' #
        )[0];

        $question['number'] = $questionNumber;
        $optionsRepository = new TestOptions();
        $question['options'] = $optionsRepository->findBy(array('question_id = ?'), array($question['id']));

        return array($test, $question);
    }

    public function cancelTestAction(Request $request, \Application $app)
    {
        $testId = $request->get('id');
        $testFlow = $this->getTestFlow($testId, $app);

        $test = new Test($testId);
        $testFlow->removeTestData(); # remove test data from session

        $app['session']->getFlashBag()->set(
            'success',
            $app['translator']->trans('test.canceled', array('%title%' => $test['name']))
        );
        $link = $app['url_generator']->generate('test_page', array('id' => $test['id']));
        $app['session']->getFlashBag()->set(
            'info',
            $app['translator']->trans('test.start_again', array('%link%' => $link))
        );
        return $app->redirect($app['url_generator']->generate('homepage'));
    }

    private function isLoggedUser($app) {
        return $app['session']->has('user') ? $app['session']->get('user')[0] : false;
    }
}