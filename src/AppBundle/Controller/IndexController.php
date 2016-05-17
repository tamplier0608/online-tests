<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Repository\Categories;
use AppBundle\Entity\Repository\Test\Options;
use AppBundle\Entity\Repository\Tests;
use AppBundle\Entity\Repository\User\PassedTests;
use AppBundle\Entity\Repository\Users;
use AppBundle\Entity\Test;
use AppBundle\Entity\User;
use AppBundle\Entity\User\PassedTest;
use CoreBundle\Db\Repository;
use CoreBundle\Test\Flow as TestFlow;
use CoreBundle\Test\Flow\Calculate\Strategy\NumberCorrectAnswers;
use CoreBundle\Test\Storage\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IndexController
{
    const TESTS_PER_PAGE = 4;
    protected $testFlow;

    public function indexAction(Request $request, \Application $app)
    {
        $page = $request->get('page', 1);
        $testRepository = new Tests();

        $tests = $this->getIndexPagination($testRepository, $page, self::TESTS_PER_PAGE);
        
        return $app['twig']->render('index/index.html.twig', array(
            'pagination' => $tests
        ));
    }

    private function getIndexPagination(Repository $repository, $page, $countPerPage)
    {
        $countAll = $repository->countAll();
        $paginationData = $this->getPaginationParams($page, $countPerPage, $countAll);
        $limit = $paginationData['start'] . ', ' . $countPerPage;
        $rowset = $repository->fetchAll($limit, $orderBy = ' passed DESC, id DESC');

        return array_merge($paginationData, array('data' => $rowset));
    }

    public function showCategoryAction(Request $request, \Application $app)
    {
        $catId = $request->get('id');
        $catRepository = new Categories();
        $cat = $catRepository->find($catId);

        if (false === $cat) {
            throw new HttpException(404, 'Category is not found');
        }

        $testRepository = new Tests();
        $page = $request->get('page', 1);
        $tests = $this->getCatPagination($testRepository, $catId, $page, self::TESTS_PER_PAGE);

        return $app['twig']->render('index/show-category.html.twig', array(
            'cat' => $cat,
            'pagination' => $tests
        ));
    }

    private function getCatPagination(Repository $repository, $catId, $page, $countPerPage)
    {
        $countAll = $repository->countAll(array('category_id = ?'), array($catId));
        $paginationData = $this->getPaginationParams($page, $countPerPage, $countAll);
        $limit = $paginationData['start'] . ', ' . $countPerPage;
        $rowset = $repository->findBy(array('category_id = ?'), array($catId), $limit, $orderBy = ' passed DESC, passed DESC');

        return array_merge($paginationData, array('data' => $rowset));
    }

    private function getPaginationParams($page, $countPerPage, $countAll)
    {
        $countPages = (int) ceil($countAll / $countPerPage);

        if ($countPages && $page > $countPages) {
            throw new HttpException(404, 'Page is not found');
        }

        $start = ($page - 1) * $countPerPage;

        if ($start <= 0) {
            $start = 0;
        }

        $next = false;
        $prev = false;

        if ($page < $countPages && $countPages > 1) {
            $next = $page + 1;
        }

        if ($page > 1) {
            $prev = $page - 1;
        }

        return array(
            'start' => $start,
            'countPerPage' => $countPerPage,
            'countPages' => $countPages,
            'countAll' => $countAll,
            'next' => $next,
            'prev' => $prev
        );

    }

    /**
     * @Method GET
     */
    public function doTestAction(Request $request, \Application $app)
    {
        $testId = $request->get('id');
        $testFlow = $this->getTestFlow($app);
        
        $testData = $testFlow->getTestProgress();
        $user = $app->getUser();

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

        $questionData = $this->getQuestionData($testId, $nextQuestion);

        if (false === $questionData) {
            throw new NotFoundHttpException($app['translator']->trans('test.no_questions'));
        }

        list($test, $question) = $questionData;

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
        $user = $app->getUser();

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
            echo 'Test data: ' . var_export($testFlow->getTestProgress()->getAnswers(), true);
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
                $testFlow->saveTestData();

                $this->updatePassedOfTest($test);
                $this->savePassedTestRecord($testFlow, $test);
            }

            return $app->redirect($app['url_generator']->generate('result_page', array('id' => $testId)));
        }

        list($test, $question) = $data;

        if (empty($test)) {
            throw new NotFoundHttpException($app['translator']->trans('test.not_found'));
        }

        $testFlow->saveTestData();

        return $app['twig']->render('index/test.html.twig', array(
            'test' => $test,
            'question' => $question,
        ));
    }

    protected function updatePassedOfTest(Test $test)
    {
        $test->passed = $test->passed + 1;
        $test->save();
    }

    protected function savePassedTestRecord(TestFlow $testFlow, Test $test)
    {
        $resultValue = $testFlow->getTestProgress()->getResult();
        $testResult = $this->getTestResult($test, $resultValue);
        $userId = $testFlow->getTestProgress()->getUserId();

        if ($userId) {
            $passedTest = new PassedTest();
            $passedTest->user_id = $userId;
            $passedTest->result_id = $testResult->id;
            $passedTest->result_value = $resultValue;
            $passedTest->test_id = $test->id;
            $passedTest->test_data = serialize($testFlow->getTestProgress());

            $now = new \DateTime();
            $passedTest->passed_at = $now->format('Y-m-d H:i:s');

            $passedTest->save();
        }
    }

    public function finishTestAction(Request $request, \Application $app)
    {
        $testId = $request->get('id');

        try {
            $test = new Test($testId);
        } catch(Exception $e) {
            if ($app['debug']) {
                throw new $e;
            }
            return new HttpException(404, 'Запрощенный тест не найден');
        }

        $user = $app->getUser();

        # try to replace user if it's request from teacher cabinet
        if ($user && $user->hasRole(User::ROLE_TEACHER) and $request->get('studentId')) {
            $userRepository = new Users();
            $student = $userRepository->find($request->get('studentId'));

            if ($student instanceof User) {
                $user = $student;
            }
        }

        if ($user) { # if user is logged in or request test result from cabinet get data from record of passed test
            $passedTestRepository = new PassedTests();
            $passedTests = $passedTestRepository->findBy(array('user_id = ?', 'test_id = ?'), array($user->id, $test->id));

            $passedTest = false;
            if (is_array($passedTests) && count($passedTests)) {
                $passedTest = $passedTests[0];
            }

            if (!$passedTest) {
                $app->redirect($app['url_generator']->generate('homepage'));
            }

            $testData = unserialize($passedTest->test_data);
            $resultValue = $testData->getResult();
            $answers = $testData->getAnswers();
        } else { # else get data from test flow object
            $testFlow = $this->getTestFlow($app);
            $resultValue = $testFlow->getTestProgress()->getResult();
            $answers = $testFlow->getTestProgress()->getAnswers();
        }

        $testResult = $this->getTestResult($test, $resultValue);

        if (!$testResult || empty($answers) || empty($resultValue)) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $viewData = array(
            'resultValue' => $resultValue,
            'result' => $testResult,
            'users_answers' => $this->getAnswersData($test->id, $answers)
        );

        if ($test->getCalcStrategy() instanceof NumberCorrectAnswers) {
            $optionsRepository = new Options();
            $viewData['right_answers'] = $optionsRepository->getRightAnswersForTest($test->id);
        }

        return $app['twig']->render('index/result.html.twig', $viewData);
    }

    private function getAnswersData($testId, array $answers)
    {
        $data = array();
        $test = new Test($testId);
        $questions = $test->getQuestions();

        foreach ($answers as $qNumber => $answer) {
            if (false === array_key_exists($qNumber - 1, $questions)) {
                continue;
            }
            $question = $questions[$qNumber - 1];
            $options = $question->getOptions();

            $answerData = explode('-', $answer);
            $optionIndex = $answerData[0];
            $option = $options[$optionIndex - 1];

            $data[] = array(
                'question_number' => $qNumber,
                'question_title' => $question->value,
                'option_index' => $option->index,
                'option_title' => $option->title,
                'option_value' => $option->value
            );
        }
        return $data;
    }

    /**
     * @param Test $test
     * @param $resultValue
     * @return Test\Result|bool
     */
    private function getTestResult(Test $test, $resultValue)
    {
        $resultResolver = new TestFlow\Result\Resolver();
        return $resultResolver->resolve($resultValue, $test);
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
}