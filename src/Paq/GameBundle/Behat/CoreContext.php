<?php

namespace Paq\GameBundle\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Session;
use Behat\Mink\WebAssert;
use Paq\GameBundle\Entity\Game;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Defines application features from the specific context.
 */
class CoreContext extends DefaultContext
{

    /**
     * @Given /^I am Anonymous User$/
     */
    public function iAmAnonymousUser()
    {
        $this->getSession()->visit('/logout');
    }

    /**
     * @param string $deviceId
     * @Given /I am Anonymous User using "([^"]*)" device$/
     */
    public function iAmAnonymousUserUsingDevice($deviceId)
    {
        $this->getMink()->setDefaultSessionName($deviceId);
        $this->getSession()->visit('/logout');
    }

    /**
     * @param string $deviceId
     * @param string $username
     *
     * @Given I am authenticated on :deviceId as :username
     */
    public function iAmAuthenticatedOnDeviceAsUser($deviceId, $username)
    {
        $this->getMink()->setDefaultSessionName($deviceId);
        $this->authenticate($this->getSession(), $username);
    }

    /**
     * @Given /^I am authenticated as "([^"]*)"$/
     */
    public function iAmAuthenticatedAs($username)
    {
        $this->authenticate($this->getSession(), $username);
    }

    /**
     * @param string $gcode
     * @param string|null $sessionId
     * @Given /^Game with GCode "([^"]*)" exists$/
     */
    public function gameWithGCodeExists($gcode, $sessionId = null)
    {
        $originalSession = $this->getMink()->getDefaultSessionName();

        $this->getMink()->setDefaultSessionName('Board');
        $this->iAmAuthenticatedAs('damian');
        $this->iCreateGame();
        $damian = $this->getUser();

        /* @var Game $game */
        $game = $this->findOneBy('PaqGameBundle:Game', ['createdBy' => $damian->getId()]);
        $game->setGCode($gcode);
        $game->setSessionId($sessionId);

        $this->getEntityManager()->persist($game);
        $this->getEntityManager()->flush();

        $this->getMink()->setDefaultSessionName($originalSession);
    }

    /**
     * @param string $gcode
     * @Given /^LAN Game with GCode "([^"]*)" exists$/
     */
    public function lanGameWithGCodeExists($gcode)
    {
        return $this->gameWithGCodeExists($gcode, '127.0.0.1');
    }

    /**
     * @Given /^There are no Games$/
     */
    public function deleteAllGames()
    {
        $this->getRepository('PaqGameBundle:Game')->deleteAll();
    }

    /**
     * @param string $sessionId
     * @Given /^Game for Session ID "(?P<sessionId>(?:[^"]|\\")*)" should exist$/
     */
    public function assertGameWithSessionIdExists($sessionId)
    {
        $game = null;
        try {
            $game = $this->findOneBy('PaqGameBundle:Game', ['sessionId' => $sessionId]);
        } catch (\InvalidArgumentException $iae) {
        }

        $this->assert((null !== $game), "Game for Session ID = $sessionId does not exist.");
    }

    /**
     * @param string $sessionId
     * @Given /^Game for Session ID "(?P<sessionId>(?:[^"]|\\")*)" should not exist$/
     */
    public function assertGameWithSessionIdNotExists($sessionId)
    {
        $game = null;
        try {
            $game = $this->findOneBy('PaqGameBundle:Game', ['sessionId' => $sessionId]);
            $this->assert((null === $game), "Game for Session ID = $sessionId does exist.");
        } catch (\InvalidArgumentException $iae) {
        }

    }

    /**
     * @When /^I create a Game$/
     */
    public function iCreateGame()
    {
        $this->visit('/pl/uc/?tr=p_g_g_c&enable_lan=1');
    }

    /**
     * @param string $gcode
     * @When I join Game :gcode
     */
    public function iJoinGame($gcode)
    {
        $this->iAmOnHomepage();
        $this->fillField('gcode', $gcode);
        $this->pressButton('button_play');
    }

    /**
     * @param string $deviceId
     * @param string $pageId
     * @Given :deviceId device displays :pageId
     */
    public function deviceDisplaysPage($deviceId, $pageId)
    {
        switch ($pageId) {
            case 'StartPage':
                $this->getSession($deviceId)->visit('/');
                break;

            case 'Game-Board[GCode=abc]':
                if ($deviceId !== 'Board') {
                    throw new PendingException('Not implemented displaying ' . $pageId . ' on ' . $deviceId);
                } else {
                    $this->gameWithGCodeExists('abc');
                    $this->iAmAuthenticatedOnDeviceAsUser('board', 'damian');
                    $this->getSession($deviceId)->visit('/board/abc');
                }
                break;

            case 'Game-Join-Anonymous [GCode=abc]':
                if ($deviceId !== 'Controller') {
                    throw new PendingException('Not implemented displaying ' . $pageId . ' on ' . $deviceId);
                } else {
                    $session = $this->getSession($deviceId);
                    $this->logout($session);
                    $session->visit('/uc/?gcode=abc&view=b&tr=p_g_g_j');
                }
                break;

            default:
                throw new PendingException('Unknown pageId: ' . $pageId);
        }

    }

    /**
     * @param string $pageId
     * @param string $deviceId
     * @When /^I open "([^"]*)" on "([^"]*)" device$/
     */
    public function iOpenPageOnDevice($pageId, $deviceId)
    {
        $this->getSession($deviceId)->visit($pageId);
    }

    /**
     * Checks, that button with specified ID has a value attribute set to given value
     *
     * @Then /^(?:|I )should see button "(?P<buttonId>(?:[^"]|\\")*)" with text "(?P<value>[^"]*)"$/
     */
    public function assertButtonIsVisible($buttonId, $value)
    {
        $button = $this->getSession()->getPage()->findById($buttonId);

        if (!$button) {
            throw new ExpectationException("Button [ID = $buttonId] not found", $this->getSession());
        }

        $actualValue = $button->getAttribute('value');
        if ($actualValue !== $value) {
            throw new ExpectationException("Button [ID = $buttonId] has value attribute = $actualValue instead of $value", $this->getSession());
        }
    }

    /**
     * @param string $pageId
     * @param string $deviceId
     * @Then /^I should see "([^"]*)" on "([^"]*)" device$/
     */
    public function iShouldSeePageOnDevice($pageId, $deviceId)
    {
        $webAssert = $this->getWebAssert($deviceId);

        switch ($pageId) {
            case 'Game-Join-Anonymous':
                $webAssert->addressMatches('/uc\//');
                break;

            case 'Game-Join-Anonymous[GCode=abc]':
                $webAssert->addressMatches('/uc\//');
                $this->fullAddressMatches($deviceId, '/gcode=abc/');
                break;

            case 'Game-Join-Anonymous[Session=X;View=Controller]':
                $webAssert->addressMatches('/uc\//');
                $this->fullAddressMatches($deviceId, '/sid=/');
                $this->fullAddressMatches($deviceId, '/vm=c/');
                break;

            case 'Game-Create-Anonymous':
                $webAssert->addressMatches('/action\/game\/create-anonymous/');
                break;

            case 'Game-Controller[GCode=abc]':
                $webAssert->addressMatches('/controller\/abc/');
                break;

            case 'Game-Board':
                $webAssert->addressMatches('/board\/\w+/');
                break;

            case 'Game-Board[GCode=abc]':
                $webAssert->addressMatches('/board\/\w+/');
                $webAssert->pageTextContains('Link dla znajomego');
                $webAssert->pageTextContains('abc');
                break;

            case 'Game-Watcher[GCode=abc]':
                $webAssert->addressMatches('/watcher\/abc/');
                $webAssert->pageTextContains('abc');
                break;

            case 'User[name=agata]':
                $webAssert->pageTextContains('agata');
                break;

            default:
                throw new PendingException('Unknown pageId: ' . $pageId);
        }
    }

    /**
     * @param string $qrCodeId
     * @param string $qrCodeDeviceId
     * @param string $openOnDeviceId
     * @When /^I scan QR Code "([^"]*)" on "([^"]*)" device using "([^"]*)" device$/
     */
    public function iScanQRCodeOnDeviceUsing($qrCodeId, $qrCodeDeviceId, $openOnDeviceId)
    {
        $qrCode = $this->getSession($qrCodeDeviceId)->getPage()->findById($qrCodeId);
        if (!$qrCode) {
            throw new \InvalidArgumentException('Could not find QR Code: ' . $qrCodeId);
        }

        $qrCodeUrl = $qrCode->getAttribute('alt');

        $client = $this->getSession($openOnDeviceId)->getDriver()->getClient();
        $client->followRedirects(true);

        $this->getSession($openOnDeviceId)->visit($qrCodeUrl);
    }

    /**
     * Checks that current session address matches regex.
     *
     * @param string $deviceId
     * @param string $regex
     *
     * @throws ExpectationException
     */
    public function fullAddressMatches($deviceId, $regex)
    {
        $actual = $this->getSession($deviceId)->getCurrentUrl();
        $message = sprintf('Current page "%s" does not match the regex "%s".', $actual, $regex);

        $this->assert((bool) preg_match($regex, $actual), $message);
    }

    /**
     * Asserts a condition.
     *
     * @param bool   $condition
     * @param string $message   Failure message
     *
     * @throws ExpectationException when the condition is not fulfilled
     */
    private function assert($condition, $message)
    {
        if ($condition) {
            return;
        }

        throw new ExpectationException($message, $this->getSession());
    }

}
