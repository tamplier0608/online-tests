<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Repository\Test\Results as TestResults;
use AppBundle\Entity\Repository\Tests;
use AppBundle\Entity\Test;
use AppBundle\Entity\User\PassedTest;
use CoreBundle\Test\Storage\Session;
use CoreBundle\Test\Flow as TestFlow;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CoreBundle\Test\Flow\Calculate\Strategy\TotalWeight;

class IndexController
{
    protected $testFlow;

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
        $testFlow = $this->getTestFlow($app);
        
        $testData = $testFlow->getTestProgress();
        $user = $this->isLoggedUser($app);

        if ($testData->isEmpty() or $testData->isCompleted()) {
            $userId = $user ? $user->id : false;
            $testFlow->initTestData($testId, $userId);
        }
        # if there is test in progress redirect user to homepage with notice
        else if (false === $testData->isEmpty() && $testData->getTestId() !== $testId) {
            $app['session']->getFlashBag()->set('warning', $app['translator']->trans('test.test_in_progress_found'));
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        if ($user && !$testData->getUserId()) {
            $testData->setUserId($user->id);
        }

        $testData = $testFlow->getTestProgress($force = true);
        $answers = $testData->getAnswers();
        $nextQuestion = 1;

        if (!is_null($answers)) {
            $nextQuestion = $testFlow->getTestProgress()->getCurrentQuestion();
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
        $testFlow = $this->getTestFlow($app);
        $user = $this->isLoggedUser($app);

        if ($user && !$testFlow->getTestProgress()->getUserId()) {
            $testFlow->getTestProgress()->setUserId($user->id);
        }

        $prevQuestion = (int) $request->get('question_number', 1);
        $nextQuestion = (int) $testFlow->getNextQuestionNumber();
        $answer = $request->get('option');

        # save answer of previous question according to POST data
        if (false === $testFlow->getTestProgress()->hasAnswer($prevQuestion)) {
            if (null !== $answer) {
                $testFlow->getTestProgress()->saveAnswer($prevQuestion, $answer);
                $testFlow->getTestProgress()->setCurrentQuestion($nextQuestion);
            } else {
                $app['session']->getFlashBag()->set('danger', $app['translator']->trans('error.empty_answer'));
            }
        } else {
            $app['session']->getFlashBag()->set('warning', $app['translator']->trans('error.repeat_answer'));
        }

        if ($app['debug']) {
            $answers = $testFlow->getTestProgress()->getAnswers();
            echo '<pre>';
            echo 'Answer: ' . var_export(!is_null($answer), true) . '<br />';
            echo 'Count answers: ' . var_export(count($answers), true) . '<br />';
            echo 'Prev question in request: ' . var_export($prevQuestion, true) . '<br />';
            echo 'Current question in SESSION: ' . var_export($testFlow->getTestProgress()->getCurrentQuestion(), true) . '<br />';
            echo 'Last answered question: ' . var_export(count($answers), true) . '<br />';
            echo '</pre>';
        }

        $data = $this->getQuestionData($testId, $testFlow->getTestProgress()->getCurrentQuestion());

        # test is over
        if (false === $data) {
            $app['session']->getFlashBag()->set('success', $app['translator']->trans('test.result_success'));

            if (false === $testFlow->getTestProgress()->isCompleted()) {
                $test = new Test($testId);
                $testResult = $testFlow->calculateResult($test->getCalcStrategy());
                $testFlow->getTestProgress()->setResult($testResult);
                $testFlow->getTestProgress()->setCompleted(true);

                $this->updatePassedOfTest($testId);
                $this->savePassedTestRecord($testFlow, $testId);
            }

            return $app->redirect($app['url_generator']->generate('result_page', array('id' => $testId)));
        }

        list($test, $question) = $data;

        if (empty($test)) {
            throw new NotFoundHttpException($app['translator']->trans('test.not_found'));
        }

        return $app['twig']->render('index/test.html.twig', array(
            'test' => $test,
            'question' => $question,
        ));
    }

    protected function updatePassedOfTest($testId)
    {
        $test = new Test($testId);
        $test->passed = $test->passed + 1;
        $test->save();
    }

    protected function savePassedTestRecord(TestFlow $testFlow, $testId)
    {
        $points = $testFlow->getTestProgress()->getResult();
        $testResultsRepository = new TestResults();
        $testResult = $testResultsRepository->getDescriptionByPoints($testId, $points);

        $userId = $testFlow->getTestProgress()->getUserId();

        if ($userId) {
            $passedTest = new PassedTest();
            $passedTest->user_id = $userId;
            $passedTest->result_id = $testResult->id;
            $passedTest->points = $points;
            $passedTest->test_id = $testId;
            $passedTest->test_data = serialize($testFlow->getTestProgress());

            $now = new \DateTime();
            $passedTest->passed_at = $now->format('Y-m-d H:i:s');

            $passedTest->save();
        }
    }

    public function finishTestAction(Request $request, \Application $app)
    {
        $testId = $request->get('id');
        $testFlow = $this->getTestFlow($app);

        $points = $testFlow->getTestProgress()->getResult();
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

        $questions = $test->getQuestions();
        $questionCount = count($questions);

        if ($questionNumber > $questionCount) {
            return false; # it means test is over
        }

        $question = $questions[$questionNumber - 1];
        $question['number'] = $questionNumber;
        $question['options'] = $question->getOptions();

        return array($test, $question);
    }

    public function cancelTestAction(Request $request, \Application $app)
    {
        $testId = $request->get('id');
        $testFlow = $this->getTestFlow($app);

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

    public function getTestFlow(\Application $app)
    {
        if (null === $this->testFlow) {
            $this->testFlow = new TestFlow(new Session($app['session']));
        }
        return $this->testFlow;
    }

    private function isLoggedUser($app) {
        return $app['session']->get('user')[0];
    }
}