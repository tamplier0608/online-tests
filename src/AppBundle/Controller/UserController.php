<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Entity\Repository\Orders;
use AppBundle\Entity\Repository\Tests;
use AppBundle\Entity\Repository\User\Groups as UserGroups;
use AppBundle\Entity\Repository\Users;
use AppBundle\Entity\User;
use CoreBundle\Auth\Adapter\DbTable;
use CoreBundle\Auth\Auth;
use CoreBundle\Db\Adapter\DoctrineDbal;
use CoreBundle\Form\Constraints as CoreAssert;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Silex\Application;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

class UserController
{
    public function loginAction(Request $request, \Application $app)
    {
        $form = $this->createLoginForm($app);
        $form->handleRequest($request);

        $authError = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->getData()['username'];
            $password = $form->getData()['password'];

            $authAdapter = new DbTable(new DoctrineDbal($app['db']), 'users', 'username', 'password');
            $auth = new Auth($authAdapter);

            if ($auth->authorize($username, $password, $app['salt'])) {
                $userRepository = new Users();
                $user = $userRepository->findBy(array('username=?'), array($username))[0];
                $app['session']->set('user', $user->id);

                return $app->redirect($this->getRefUri($app));
            } else {
                $authError = $app['translator']->trans('auth.credentials_not_match', array(), 'validation');
            }
        }

        return $app['twig']->render('user/login.html.twig', array(
            'form' => $form->createView(),
            'auth_error' => $authError
        ));
    }

    /**
     * @param \Application $app
     * @return \Symfony\Component\Form\Form
     */
    protected function createLoginForm(\Application $app)
    {
        $form = $app['form.factory']->createBuilder('form', null, array('attr' => array('novalidate' => 'novalidate')))
            ->add('username', 'text', array(
                'constraints' => array(new Assert\NotBlank(array('message' => $app['translator']->trans('username.not_blank', array(), 'validation')))),
                'error_bubbling' => true
            ))
            ->add('password', 'password', array(
                'constraints' => array(new Assert\NotBlank(array('message' => $app['translator']->trans('password.not_blank', array(), 'validation')))),
                'error_bubbling' => true
            ))
            ->getForm();
        return $form;
    }

    public function signinAction(Request $request, \Application $app)
    {
        if ($app->isUserLogged()) {
            return $this->redirectToRefUriWithMessage($app, 'flashes.is_logged_user', 'warning');
        }

        $form = $this->createSigninForm($app);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->username = $form->getData()['username'];
            $user->email = $form->getData()['email'];
            $user->firstname = $form->getData()['firstname'];
            $user->lastname = $form->getData()['lastname'];
            $user->group_id = $form->getData()['group'];
            $user->password = md5($form->getData()['password'] . $app['salt']);
            $user->wallet = 10; # give to user registration prise

            $now = new \DateTime();
            $user->registered_at = $now->format('Y-m-d H:i:s');

            $user->activation_code = substr(base64_encode(serialize($user)), 0, 255);
            $user->save();

            # send an email with activation code
            $message = $this->getActivationEmailMessage($app, $user);
            $app['swiftmailer']->send($message);

            return $this->redirectToRefUriWithMessage($app, 'flashes.registration_success');
        }

        return $app['twig']->render('user/signin.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @param \Application $app
     * @return \Symfony\Component\Form\Form
     */
    protected function createSigninForm(\Application $app)
    {
        $form = $app['form.factory']->createBuilder('form', null, array('attr' => array('novalidate' => 'novalidate')))
            ->add('firstname', 'text', array(
                'constraints' => array(
                    new Assert\NotBlank(array('message' => $app['translator']->trans('firstname.not_blank', array(), 'validation'))),
                    new Assert\Length(array(
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => $app['translator']->trans('firstname.min_length', array(), 'validation'),
                        'maxMessage' => $app['translator']->trans('firstname.max_length', array(), 'validation')
                    )),
                ),
            ))
            ->add('lastname', 'text', array(
                'constraints' => array(
                    new Assert\NotBlank(array('message' => $app['translator']->trans('lastname.not_blank', array(), 'validation'))),
                    new Assert\Length(array(
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => $app['translator']->trans('lastname.min_length', array(), 'validation'),
                        'maxMessage' => $app['translator']->trans('lastname.max_length', array(), 'validation')
                    )),
                ),
            ))
            ->add('group', 'choice', array(
                'choices' => $this->getUserGroups(),
            ))
            ->add('username', 'text', array(
                'constraints' => array(
                    new Assert\NotBlank(array('message' => $app['translator']->trans('username.not_blank', array(), 'validation'))),
                    new Assert\Length(array(
                        'min' => 6,
                        'max' => 20,
                        'minMessage' => $app['translator']->trans('username.min_length', array(), 'validation'),
                        'maxMessage' => $app['translator']->trans('username.max_length', array(), 'validation')
                    )),
                    new CoreAssert\Unique(array(
                        'db' => $app['db'],
                        'table' => 'users',
                        'field' => 'username',
                        'message' => $app['translator']->trans('username.not_unique', array(), 'validation')
                    ))
                ),
            ))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'first_name' => 'password',
                'second_name' => 'repeat_password',
                'invalid_message' => $app['translator']->trans('password.not_equal', array(), 'validation'),
                'first_options' => array('constraints' => array(
                    new Assert\NotBlank(array('message' => $app['translator']->trans('password.not_blank', array(), 'validation'))),
                    new Assert\Length(array(
                        'min' => 6,
                        'max' => 32,
                        'minMessage' => $app['translator']->trans('password.min_length', array(), 'validation'),
                        'maxMessage' => $app['translator']->trans('password.max_length', array(), 'validation')
                    ))
                )),
            ))
            ->add('email', 'email', array(
                'constraints' => array(
                    new Assert\NotBlank(array('message' => $app['translator']->trans('email.not_blank', array(), 'validation'))),
                    new Assert\Email(array('message' => $app['translator']->trans('email.not_valid_email', array(), 'validation'))),
                )
            ))
            ->getForm();
        return $form;
    }

    private function getUserGroups()
    {
        $userGroupRepository = new UserGroups();
        $groups = $userGroupRepository->findBy(['id != 1'], [], $limit = false, $orderBy = 'name');

        $return = [];

        foreach ($groups as $group) {
            $return[$group->id] = $group->name;
        }

        return $return;
    }

    private function getActivationEmailMessage(\Application $app, $user)
    {
        $activationLink = $app['request']->getHost() . $app['url_generator']->generate('user_activation', array(
            'id' => $app['db']->lastInsertId(),
            'code' => $user->activation_code
        ));

        return  \Swift_Message::newInstance()
            ->setSubject($app['translator']->trans('email.activation.subject'))
            ->setFrom(array($app['translator']->trans('email.activation.from')))
            ->setBody($app['translator']->trans('email.activation.body', array('{{ link }}' => $activationLink)))
            ->setTo($user->email);
    }

    public function logoutAction(Request $request, \Application $app)
    {
        if (!$app->isUserLogged()) {
            return $this->redirectToRefUriWithMessage($app, 'flashes.is_not_logged', 'warning');
        }

        if ($app['session']->has('user')) {
            $app['session']->remove('user');
        }
        return $app->redirect($this->getRefUri($app));
    }

    public function cabinetAction(Request $request, \Application $app)
    {
        if (!$app->isUserLogged()) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $user = $app->getUser();
        $userRepository = new Users();

        $viewData = array();

        if ($user->hasRole(User::ROLE_TEACHER)) {
            # try to replace user if it's request from teacher cabinet
            if ($studentId = $request->get('id')) {
                $student = $userRepository->find($studentId);
                if ($student instanceof User) {
                    $user = $student;
                    $viewData['is_teachers_request'] = true;
                }
            }
            $viewData['students'] = $userRepository->findBy(['role != ?', 'active = ?'], [User::ROLE_TEACHER, 1]);
        }

        $viewData['user'] = $user;
        $viewData['orders'] = $user->getOrders();
        $viewData['passedTests'] = $user->getPassedTests();

        return $app['twig']->render('user/cabinet.html.twig', $viewData);
    }

    public function activationAction(Request $request, \Application $app)
    {
        $code = $request->get('code');

        try {
            $user = new User($request->get('id'));
        } catch(InvalidArgumentException $e) {
            throw new HttpException(404, 'User not found');
        }

        if ($user->activation_code === $code && false == $user->active) {
            $user->active = true;
            $message = $app['translator']->trans('user.activation_success');
        } else if ($user->active) {
            $message = $app['translator']->trans('user.is_active');
        } else {
            $message = $app['translator']->trans('user.activation_error');
        }

        try {
            $user->save();
        } catch(Exception $e) {
            echo 1;
            $message = $app['translator']->trans('user.activation_error');
        }

        return $app['twig']->render('user/activation.html.twig', array('message' => $message));
    }

    public function buyAction(Request $request, \Application $app)
    {
        if (!$app->isUserLogged()) {
            $request->getSession()->getFlashBag()->set('warning', $app['translator']->trans('user.need_login_or_signin'));
            $request->getSession()->set('ref_uri', $request->server->get('REQUEST_URI'));
            return $app->redirect($app['url_generator']->generate('user_login'));
        }

        $user = $app->getUser();
        $refUri = $app['url_generator']->generate('homepage');

        $testId = $request->get('test_id');
        $testRepository = new Tests();
        $test = $testRepository->find($testId);

        if (!$test) {
            throw new NotFoundHttpException('Test is not found', null, 404);
        }

        $wallet = (float) $user->wallet;
        $price = (float) $test->price;

        if ($test->isFree()) {
            $app['request']->getSession()->getFlashBag()->set('warning', $app['translator']->trans('user.purchase.cannot_purchase_free_content'));
            return $app->redirect($refUri);
        }

        $orderRepository = new Orders();

        if ($orderRepository->isTestPurchasedByUser($test->id, $user->id)) {
            $app['request']->getSession()->getFlashBag()->set('warning', $app['translator']->trans('user.purchase.cannot_purchase_purchased_content'));
            return $app->redirect($refUri);
        }
        
        if ($wallet >= $price) {
            $orderRepository->beginTransaction();

            try {
                $order = new Order();
                $order->customer_id = $user->id;
                $order->test_id = $test->id;
                $order->price = $price;

                $now = new \DateTime();
                $order->order_date = $now->format('Y-m-d H:i:s');

                $order->save();

                $user->wallet = $wallet - $price;
                $user->save();

            } catch(\Exception $e) {

                if ($app['debug']) {
                    throw new $e;
                }

                $user->wallet = $wallet; # roll back user amount in session
                $orderRepository->rollbackTransaction();

                $app['request']->getSession()->getFlashBag()->set('danger', $app['translator']->trans('user.purchase.error'));
                return $app->redirect($refUri);
            }

            $orderRepository->commitTransaction();
        } else {
            $app['request']->getSession()->getFlashBag()->set('danger', $app['translator']->trans('user.purchase.not_enough_money'));
            return $app->redirect($refUri);
        }

        return $this->redirectToRefUriWithMessage($app, 'user.purchase.success', 'success');
    }

    public function addcreditAction(Request $request, \Application $app)
    {
        if (!$user = $app->getUser()) {
            return $this->redirectToRefUriWithMessage($app, 'user.need_login_or_signin', 'danger');
        }

        $form = $app['form.factory']->createBuilder('form', null)
            ->add('sum', 'text', array(
                'constraints' => array(
                    new Assert\NotBlank(array('message' => $app['translator']->trans('sum.not_blank', array(), 'validation'))),
                    new CoreAssert\Numeric(array('message' => $app['translator']->trans('sum.not_numeric', array(), 'validation')))
                )
            ))
            ->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user->wallet = $form->getData()['sum'];
            $user->save();
            return $this->redirectToRefUriWithMessage($app, 'user.credit_changed', 'success');
        }

        return $app['twig']->render('user/add_credit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function cleanData(Request $request, Application $app)
    {
        $username = $request->get('username');
        $userRepository = new Users();
        $user = $userRepository->findBy(array('username=?'), array($username));
        
        if (!$user) {
            return $this->redirectToRefUriWithMessage($app, 'user.not_found', 'danger');
        }
        
        $query = 'DELETE FROM orders WHERE customer_id = (SELECT id FROM users u WHERE username = ?); ';
        $query .= 'DELETE FROM passed_tests WHERE user_id = (SELECT id FROM users u WHERE username = ?); ';

        try {
            $result = $app['db']->executeQuery($query, array($username, $username));
        } catch(\Exception $e) {
            if ($app['debug']) {
                throw new $e;
            }

            return $this->redirectToRefUriWithMessage($app, 'Ошибка при попытке очистить данные', 'danger');
        }

        if ($result) {
            return $this->redirectToRefUriWithMessage($app, 'Данные пользователя очищены', 'success');
        } else {
            return $this->redirectToRefUriWithMessage($app, 'Ошибка при попытке очистить данные', 'danger');
        }
    }

    private function getRefUri($app) {
        return ($app['session']->has('ref_uri') && $app['session']->get('ref_uri') != $app['request']->server->get('REQUEST_URI'))
            ? $app['session']->get('ref_uri')
            : $app['url_generator']->generate('homepage');
    }

    protected function redirectToRefUriWithMessage(\Application $app, $message, $type = 'info')
    {
        $app['request']->getSession()->getFlashBag()->set($type, $app['translator']->trans($message));
        $refUri = $this->getRefUri($app);
        return $app->redirect($refUri);
    }

}