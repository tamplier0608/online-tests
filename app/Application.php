<?php

use Igorw\Silex\ConfigServiceProvider;
use MJanssen\Provider\RoutingServiceProvider;
use Silex\Application as SilexCoreApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class Application extends SilexCoreApplication
{
    use SilexCoreApplication\TwigTrait;
    use SilexCoreApplication\SecurityTrait;
    use SilexCoreApplication\FormTrait;
    use SilexCoreApplication\UrlGeneratorTrait;
    use SilexCoreApplication\SwiftmailerTrait;
    use SilexCoreApplication\MonologTrait;
    use SilexCoreApplication\TranslationTrait;

    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->bootstrapConfigs($values);
        $this->boostrapUrlGenerator();
        $this->bootstrapTwig();
        $this->bootstrapTranslations();
        $this->bootstrapSession();
        $this->bootstrapDbModels();
        $this->bootstrapForms();
        $this->bootstrapValidators();
        $this->bootstrapMailer();

        # set main rules for controllers
        $context = $this;
        $this['controllers']
            ->assert('id', '\d+') # make sure that id is digit for all controllers
            ->convert('id', function($id) use($context) { # convert string id to int
                return (int) $this->escape($id);
            })
            ->before(function() use($context) {
                # save referer URI in session but skip prohibited pages
                $requestUri = $this['request']->server->get('REQUEST_URI');
                $prohibitedUrls = array(
                    '/login',
                    '/logout',
                    '/sign-in',
                    '/activation',
                    '/result',
                    '/buy',
                    '/clean-data'
                );

                foreach ($prohibitedUrls as $url) {
                    if (strstr($requestUri, $url)) {
                        return;
                    }
                }
                $this['session']->set('ref_uri', $requestUri);
            });
    }

    /**
     * @param array $values
     */
    protected function bootstrapConfigs(array $values)
    {
        if (empty($values['config'])) {
            throw new \RuntimeException('Config must be set!');
        }

        $this->register(new ConfigServiceProvider($values['config'])); # register application config

        # register routes config
        $this->register(new ConfigServiceProvider(__DIR__ . '/config/routes.php'));
        $this->register(new RoutingServiceProvider('config.routes'));
    }

    protected function boostrapUrlGenerator()
    {
        # register URL generator service
        $this->register(new UrlGeneratorServiceProvider());
    }

    protected function bootstrapTwig()
    {
        # register Twig template engine service
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/Resources/views',
        ));

        $this['twig'] = $this->share($this->extend('twig', function($twig, $this) {
            $app = $this;

            $twig->addFunction(new Twig_SimpleFunction('asset', function($asset) use($app) {
                return $this['request']->getBaseUrl() . '/web/assets/' . $asset;
            }));

            $twig->addFunction(new Twig_SimpleFunction('dump', function($data) use($app) {
                echo '<pre>';
                var_dump($data);
                echo '</pre>';
            }));

            $twig->addFunction(new Twig_SimpleFunction('is_test_in_progress', function($testId) use($app) {
                $testFlow = new CoreBundle\Test\Flow(new \CoreBundle\Test\Storage\Session($app['session']));

                if (
                    false === $testFlow->getTestProgress()->isEmpty()
                    && $testId == $testFlow->getTestProgress()->getTestId()
                    && false === $testFlow->getTestProgress()->isCompleted()
                ) {
                    return true;
                }
                return false;
            }));

            $twig->addFunction(new Twig_SimpleFunction('get_test_in_progress', function() use($app) {
                return $this->getTestInProgress();
            }));

            $twig->addFunction(new Twig_SimpleFunction('display_test_in_progress', function($testId) use($app) {
                $testFlow = new \CoreBundle\Test\Flow(new \CoreBundle\Test\Storage\Session($app['session']));

                $testRepository = new \AppBundle\Entity\Repository\Tests();
                $test = $testRepository->find($testId);
                $all = count($test->getQuestions());

                if (0 !== $all) {
                    $complete = count($testFlow->getTestProgress()->getAnswers());
                    $percent = $complete * 100 / $all;
                    return sprintf('%d / %d (%d%%)', $complete, $all, $percent);
                }

            }));

            $twig->addFunction(new Twig_SimpleFunction('is_user_logged', function() use($app) {
                return $app->isUserLogged();
            }));

            $twig->addFunction(new Twig_SimpleFunction('get_user', function() use($app) {
                return $app->getUser();
            }));

            $twig->addFunction(new Twig_SimpleFunction('is_test_passed', function($testId) use($app) {
                $user = $app->getUser();

                if (!$user) {
                    return false;
                }

                return $user->isTestPassed($testId);
            }));

            $twig->addFunction(new Twig_SimpleFunction('is_test_purchased', function($testId) use($app) {
                $user = $app->getUser();

                if (!$user) {
                    return false;
                }

                $orderRepository = new \AppBundle\Entity\Repository\Orders();
                return $orderRepository->isTestPurchasedByUser($testId, $user->id);
            }));

            $twig->addFunction(new Twig_SimpleFunction('get_categories', function() use($app) {
                $categoryRepository = new \AppBundle\Entity\Repository\Categories();
                return $categoryRepository->fetchAll($limit = false, 'position');
            }));

            return $twig;
        }));

    }

    protected function bootstrapTranslations()
    {
        # register translator service
        $this->register(new TranslationServiceProvider(), array(
            'locale_fallbacks' => array('ru'),
        ));

        $app = $this;

        $this['translator'] = $this->share($this->extend('translator', function ($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('yaml', __DIR__ . '/Resources/translations/messages.ru.yml', 'ru');
            $translator->addResource('yaml', __DIR__ . '/Resources/translations/validation.ru.yml', 'ru', 'validation');

            return $translator;
        }));
    }

    protected function bootstrapSession()
    {
        # register session provider
        $this->register(new SessionServiceProvider());
    }

    protected function bootstrapDbModels()
    {
        # register database service
        $this->register(new DoctrineServiceProvider(), array(
            'db.options' => $this['db.options']
        ));

        CoreBundle\Db\Entity::setDefaultDbConnection(new \CoreBundle\Db\Adapter\DoctrineDbal($this['db']));
        CoreBundle\Db\Repository::setDefaultDbConnection(new \CoreBundle\Db\Adapter\DoctrineDbal($this['db']));
    }

    protected function bootstrapForms() {
        $this->register(new FormServiceProvider());
        $this['form.secret'] = md5(microtime() . 'online-tests');
    }

    protected function bootstrapValidators() {
        $this->register(new ValidatorServiceProvider());
    }

    protected function bootstrapMailer() {
        $this['swiftmailer'] = $this->share(function() {
            return new \Swift_Mailer(new \Swift_MailTransport());
        });
    }

    public function getUser()
    {
        $userId = $this['session']->get('user');

        if (null !== $userId) {
            return new \AppBundle\Entity\User($userId);
        }
        return false;
    }

    public function isUserLogged()
    {
        return $this['session']->has('user');
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function getTestInProgress()
    {
        $testInProgress = $this['session']->get('test_in_progress');

        if (empty($testInProgress)) {
            return false;
        }
        $testInProgress = unserialize($testInProgress);

        if ($testInProgress->isCompleted()) {
            return false;
        }

        $testId = $testInProgress->getTestId();

        $testRepository = new \AppBundle\Entity\Repository\Tests();
        $test = $testRepository->find($testId);

        if (!$test) {
            return false;
        }

        return $test;
    }
}