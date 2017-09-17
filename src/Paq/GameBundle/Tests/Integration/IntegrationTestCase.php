<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Integration;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Paq\GameBundle\Doctrine\Filter\LocaleFilter;
use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\Question;
use Paq\GameBundle\Entity\User;
use Paq\GameBundle\PaqGameBundle;
use Paq\GameBundle\Symfony\EventSubscriber\LocaleSubscriber;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * For building tests using Database and/or dispatching Symfony Controllers
 */
abstract class IntegrationTestCase extends WebTestCase
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ReferenceRepository
     */
    protected $refRepo;

    public function setUp()
    {
        $this->loadTestFixturesSet();

        $this->client = static::createClient();
        $this->logOut();
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (is_object($this->em)) {
            $this->em->close();
        }
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');

        return $this->em;
    }

    protected function loadTestFixturesSet()
    {
        $this->getEntityManager();
        $localeSubscriber = new LocaleSubscriber();
        $localeSubscriber->setContainer($this->getContainer());
        $localeSubscriber->disableLocaleFilter();

        $executor = $this->loadFixtures([
            'Paq\GameBundle\DataFixtures\Test\LoadUserData',
            'Paq\GameBundle\DataFixtures\Test\LoadTagData',
            'Paq\GameBundle\DataFixtures\Test\LoadQuestionData',
            'Paq\GameBundle\DataFixtures\Test\LoadGameData',
        ]);

        $localeSubscriber->enableLocaleFilter(PaqGameBundle::LOCALE_PL);
        $this->refRepo = $executor->getReferenceRepository();
    }

    /**
     * @param Client $client
     * @return \Symfony\Component\Routing\Router
     */
    protected function getRouter(Client $client = null)
    {
        if (null === $client) {
            return $this->client->getContainer()->get('router');
        } else {
            return $client->getContainer()->get('router');
        }
    }

    /**
     * @param Client $client
     * @return \JMS\Serializer\Serializer
     */
    protected function getSerializer(Client $client = null)
    {
        if (null === $client) {
            return $this->client->getContainer()->get('serializer');
        } else {
            return $client->getContainer()->get('serializer');
        }
    }

    /**
     * @param Client $client
     * @return Session
     */
    protected function getSession(Client $client = null)
    {
        if (null === $client) {
            return $this->client->getContainer()->get('session');
        } else {
            return $client->getContainer()->get('session');
        }
    }

    /**
     * @param Client $client
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getDoctrine(Client $client = null)
    {
        if (null === $client) {
            return $this->client->getContainer()->get('doctrine');
        } else {
            return $client->getContainer()->get('doctrine');
        }
    }

    /**
     * Log user in by username
     *
     * @param string|User $user username or User object
     * @param \Symfony\Bundle\FrameworkBundle\Client $client
     */
    protected function logIn($user, $password = null, \Symfony\Bundle\FrameworkBundle\Client $client = null)
    {
        if (null === $client) {
            $client = $this->client;
        }

        $username = ($user instanceof User) ? $user->getUsernameCanonical() : $user;

        $crawler = $client->request('GET', $this->getRouter($client)->generate('fos_user_security_login'));
        $form = $crawler->selectButton('_submit')->form();

        $form['_username'] = $username;
        $form['_password'] = $password ? $password : $username;
        $client->submit($form);
        $response = $client->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response, 'Authentication response should be an redirect');
        $this->assertNotContains(
            '/login',
            $response->headers->get('location'),
            'Authentication failed - got redirected back to login page. Wrong username or password?'
        );
        $client->followRedirect();
    }

    /**
     * Log user out
     * Clear session and cookies
     *
     * @param \Symfony\Bundle\FrameworkBundle\Client $client
     */
    protected function logOut(\Symfony\Bundle\FrameworkBundle\Client $client = null)
    {
        if (null === $client) {
            $client = $this->client;
        }

        $crawler = $client->request('GET', $this->getRouter($client)->generate('fos_user_security_logout'));

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $client->getResponse(),
            'Authentication response should be an redirect; Got: ' . $client->getResponse()->getContent()
        );
        $client->followRedirect();
    }

    /**
     * @param User $user
     * @param Game $game
     * @return Response
     */
    protected function userDisconnectsGame(User $user, Game $game)
    {
        $this->logIn($user);
        $router = $this->getRouter();
        $route = $router->generate('paq_game_service_game_user_disconnect', ['gameId' => $game->getId(), 'userId' => $user->getId()]);

        $crawler = $this->client->request('POST', $route);

        return $this->client->getResponse();
    }

    /**
     * @param string $referenceName
     * @return Game
     */
    protected function getGame($referenceName)
    {
        return $this->refRepo->getReference('game-' . $referenceName);
    }

    /**
     * @param $referenceName
     * @return Question
     */
    public function getQuestion($referenceName)
    {
        return $this->refRepo->getReference('question-' . $referenceName);
    }

    /**
     * @param string $referenceName
     * @return User
     */
    protected function getUser($referenceName)
    {
        return $this->refRepo->getReference('user-' . $referenceName);
    }

    /**
     * @param string $referenceName
     * @return Tag
     */
    protected function getTag($referenceName)
    {
        return $this->refRepo->getReference('tag-' . $referenceName);
    }

    /**
     * @param string $expectedRouteName
     * @param Response $actualResponse
     * @param string $message
     */
    protected function assertRedirectToRoute($expectedRouteName, Response $actualResponse, $message = 'Response is not a proper redirection')
    {
        $router = $this->getRouter();
        $matchedRoute = $router->match($actualResponse->headers->get('Location'));
        $responseRoute = $matchedRoute['_route'];

        $this->assertTrue(
            $actualResponse->isRedirect(),
            $message . '; Response is not a redirection.; ' . $actualResponse->getContent());
        $this->assertEquals(
            $expectedRouteName,
            $responseRoute,
            $message . '; Response redirects to different Route.'
        );
    }

    protected function assertUserExists($criteria)
    {
        $user = $this->getEntityManager()->getRepository('PaqGameBundle:User')->findOneBy($criteria);

        $this->assertNotNull($user, 'User not found by criteria: ' . print_r($criteria, true));
    }

}