<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Command;

use Paq\GameBundle\Doctrine\Filter\LocaleFilter;
use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\PaqGameBundle;
use Paq\GameBundle\Service\Chat;
use Paq\GameBundle\Service\WebsocketsServer;
use Paq\GameBundle\Symfony\EventSubscriber\LocaleSubscriber;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Thruway\ClientSession;
use Thruway\Connection;
use Thruway\Logging\Logger;
use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GameServerRunCommand extends ContainerAwareCommand
{
    const WS_SERVER_ADDRESS = 'ws://127.0.0.1:8089';

    /**
     * @var string
     */
    private $locale;

    /**
     * @var Connection
     */
    private $connection;

    public function __destruct()
    {
        if ($this->connection) {
            try {
                $this->connection->close();
            } catch (\Exception $ex) {
                // ?
            }
        }
    }

    protected function configure()
    {
        $this->setName('paqgame:game-server:run')
            ->setDescription('Runs Game Websockets Server')
            ->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'One of locales: pl, en');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locale = $input->getOption('locale');
        if (!PaqGameBundle::supportsLocale($locale)) {
            throw new \InvalidArgumentException('Not supported locale: ' . $locale);
        }
        $this->locale = $locale;

        $output->writeln('Starting Websockets Game Server');
        $this->applyLocaleSettings();
        // disabling SQL logs to reduce memory usage
        $this->getContainer()->get('doctrine')->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

        $loop = \React\EventLoop\Factory::create();
        // fixing issue with Docker
        // http://www.frankmayer.me/blog/14-a-solution-to-use-reactphp-in-docker-with-linked-containers-aka-fixing-the-etc-hosts-problem
        $factory = new \React\Dns\Resolver\Factory();
        $dns = $factory->create('8.8.8.8', $loop);

        $dns->resolve('weemtocrossbar')->then(function ($ip) {
            echo "Host: $ip\n";
        });

        $this->connection = new Connection(
            [
                "realm" => 'com_weemto_game_service',
                "url" => self::WS_SERVER_ADDRESS
            ],
            $loop
        );

        $this->connection->on('open', function (ClientSession $session) use ($loop, $output) {
            $onNewClient = function ($args) use ($session) {
                // args: [gameId]
                $gameId = (int) $args[0];
                $game = $this->getGame($gameId);
                $this->publishGame($session, $game);
                $this->freeMemory();
            };
            $session->subscribe($this->locale . 'com.weemto.clients', $onNewClient);

            // NOTICE: Must NOT use camelCase as procedure name!!!
            // REGISTER a procedure for remote calling
            $session->register($this->locale . 'com.weemto.game.set_answer', function ($args) use ($session, $output) {
                // args: [userId, gameId, questionId, answer]
                $userId = (int) $args[0];
                $gameId = (int) $args[1];
                $questionId = (int) $args[2];
                $answer = $args[3];

                $game = $this->getGame($gameId);
                $user = $this->getUser($userId);

                $this->getContainer()->get('paqgame.gameservice')->onUserAnswers($game, $user, $questionId, $answer);

                $this->publishGame($session, $game);

                return 'OK: com.weemto.game.set_answer';
            });

            $session->register($this->locale . 'com.weemto.game.set_answer_by_index', function ($args) use ($session, $output) {
                // args: [userId, gameId, answerIndex]
                $userId = (int) $args[0];
                $gameId = (int) $args[1];
                $answerIndex = (int) $args[2];

                $game = $this->getGame($gameId);
                $user = $this->getUser($userId);

                if (! $game->hasCurrentQuestion()) {
                    return ('ERROR: Requested Game has no current Question.');
                }
                if (!isset($game->getCurrentQuestionHintsOrder()[$answerIndex])) {
                    return ("ERROR: Requested answer not found in Hints order array [index: $answerIndex]");
                }

                $hints = $game->getCurrentQuestion()->getHints();

                $expectedHintId = $game->getCurrentQuestionHintsOrder()[$answerIndex];
                $expectedAnswerHint = null;
                foreach ($hints as $hint) {
                    if ($hint->getId() === $expectedHintId) {
                        $expectedAnswerHint = $hint;
                    }
                }

                if ($expectedAnswerHint === null) {
                    return ("ERROR: Requested answer not found in Question Hints [Hint ID: $expectedHintId]");
                }

                $answer = $expectedAnswerHint->getText();
                $questionId = $game->getCurrentQuestion()->getId();

                $this->getContainer()->get('paqgame.gameservice')->onUserAnswers($game, $user, $questionId, $answer);

                $this->publishGame($session, $game);

                return 'OK: com.weemto.game.set_answer_by_index';
            });

            $session->register($this->locale . 'com.weemto.game.next_question', function ($args) use ($session, $output) {
                // args: [userId, gameId]
                $userId = (int) $args[0];
                $gameId = (int) $args[1];

                $game = $this->getGame($gameId);
                $user = $this->getUser($userId);
                if (! $game->isCreatedBy($user)) {
                    return 'Access Denied';
                }

                $this->getContainer()->get('paqgame.gameservice')->onGameNextQuestion($game);

                $this->publishGame($session, $game);

                return 'OK: com.weemto.game.next_question';
            });

            $session->register($this->locale . 'com.weemto.game.reset', function ($args) use ($session, $output) {
                // args: [userId, gameId]
                $userId = (int) $args[0];
                $gameId = (int) $args[1];

                $game = $this->getGame($gameId);
                $user = $this->getUser($userId);
                if (! $game->isCreatedBy($user)) {
                    return 'Access Denied';
                }

                $this->getContainer()->get('paqgame.gameservice')->onGameReset($game);

                $this->publishGame($session, $game);
                $this->freeMemory();

                return 'OK: com.weemto.game.reset';
            });

            $session->register($this->locale . 'com.weemto.game.delete', function ($args) use ($session, $output) {
                // args: [userId, gameId]
                $userId = (int) $args[0];
                $gameId = (int) $args[1];

                $game = $this->getGame($gameId);
                $user = $this->getUser($userId);
                if (! $game->isCreatedBy($user)) {
                    return 'Access Denied';
                }

                $em = $this->getContainer()->get('doctrine')->getManager();
                $this->getContainer()->get('paqgame.gameservice')->onUserDisconnect($game, $user);

                $this->publishGame($session, $game, false);

                $em->remove($game);
                $em->flush();
                $this->freeMemory();

                return 'OK: com.weemto.game.delete';
            });

            $session->register($this->locale . 'com.weemto.game.disconnect', function ($args) use ($session, $output) {
                // args: [userId, gameId]
                $userId = (int) $args[0];
                $gameId = (int) $args[1];
                $disconnectedUserId = (int) $args[2];

                $game = $this->getGame($gameId);
                $user = $this->getUser($userId);
                if ($userId !== $disconnectedUserId && !$game->isCreatedBy($user)) {
                    // Someone tries to disconnect other user. Only owner can do that
                    return 'Access Denied. Not the Game owner.';
                } else if ($userId === $disconnectedUserId && !$game->hasUser($user)) {
                    // Someone tries to remove him self from the Game he's not playing
                    return 'Access Denied. Not in the Game.';
                }

                $disconnectedUser = $this->getUser($disconnectedUserId);
                $this->getContainer()->get('paqgame.gameservice')->onUserDisconnect($game, $disconnectedUser);

                $this->publishGame($session, $game);
                $this->freeMemory();

                return 'OK: com.weemto.game.disconnect';
            });

            $session->register($this->locale . 'com.weemto.game.set_tags', function ($args) use ($session, $output) {
                // args: [userId, gameId, tagIds]
                $userId = (int) $args[0];
                $gameId = (int) $args[1];
                $tagIds = $args[2];

                $game = $this->getGame($gameId);
                $user = $this->getUser($userId);
                if (! $game->isCreatedBy($user)) {
                    return 'Access Denied';
                }

                $tagIds = array_map(function($element) { return (int) $element; }, $tagIds);
                $tags = [];
                if (count($tagIds)) {
                    $tags = $this->getContainer()->get('doctrine')->getRepository('PaqGameBundle:Tag')->findBy(['id' => $tagIds]);
                }

                $this->getContainer()->get('paqgame.gameservice')->onTagsSelected($game, $tags);

                $this->publishGame($session, $game);

                return 'OK: com.weemto.game.set_tags';
            });
        }
        );

        $this->connection->on('close', function ($reason) use ($loop, &$timer, $output) {
            if ($timer) {
                $loop->cancelTimer($timer);
            }
            $this->log()->warning("The connected has closed with reason: {$reason}");
        });

        $this->connection->on('error', function ($reason) use ($output) {
            $this->log()->warning("The connected has closed with error: {$reason}");
        });

        $this->connection->open();
    }

    private function applyLocaleSettings()
    {
        // filter for questions enabled for current locale
        LocaleFilter::MANUALLY_ENABLED_HERE;
        $localeSubscriber = new LocaleSubscriber();
        $localeSubscriber->setContainer($this->getContainer());
        $localeSubscriber->enableLocaleFilter($this->locale);
        // translate to current locale
        $this->getContainer()->get('stof_doctrine_extensions.listener.translatable')->setTranslatableLocale($this->locale);
    }

    /**
     * @param int $gameId
     * @return Game
     */
    private function getGame($gameId)
    {
        $game = $this->getContainer()->get('doctrine')->getRepository('PaqGameBundle:Game')->find($gameId);
        if (!$game) {
            throw new \InvalidArgumentException("Game not found [ID: $gameId]");
        }

        return $game;
    }

    /**
     * @param $userId
     * @return \Paq\GameBundle\Entity\User
     */
    private function getUser($userId)
    {
        $user = $this->getContainer()->get('doctrine')->getRepository('PaqGameBundle:User')->find($userId);
        if (!$user) {
            throw new \InvalidArgumentException("User not found [ID: $userId]");
        }

        return $user;
    }

    /**
     * @param ClientSession $session
     * @param Game $game
     * @param bool|true $detach detach from Entity Manager?
     */
    private function publishGame(ClientSession $session, Game $game, $detach = true)
    {
        $session->publish($this->locale . 'com.weemto.game' . $game->getId(), [$this->getContainer()->get('serializer')->serialize($game, 'json')]);

        if ($detach) {
            $this->getContainer()->get('doctrine.orm.entity_manager')->detach($game);
        }
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    private function log()
    {
        return $this->getContainer()->get('logger');
    }

    /**
     * Tries to free some memory
     */
    private function freeMemory()
    {
        $this->getContainer()->get('doctrine.orm.entity_manager')->flush();
        $this->getContainer()->get('doctrine.orm.entity_manager')->clear();
        gc_collect_cycles();
    }

}