<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Command;


use Paq\GameBundle\Service\Chat;
use Paq\GameBundle\Service\WebsocketsServer;
use Thruway\ClientSession;
use Thruway\Connection;
use Thruway\Logging\Logger;
use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChatServerRunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('paqgame:chat-server:run')
            ->setDescription('Runs Chat Websockets Server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting Websockets Chat Server');

        $timer = null;
        $loop = \React\EventLoop\Factory::create();
        $connection = new Connection(
            [
                "realm" => 'realm1',
                "url" => 'ws://127.0.0.1:8089/ws'
            ],
            $loop
        );

        $connection->on('open', function (ClientSession $session) use ($connection, $loop, &$timer, $output) {

            // REGISTER a procedure for remote calling
            $say = function ($args) use ($session, $output) {
                $message = $args[0];
                $output->writeln("say() called with {$message}");

                $session->publish('com.weemto.chat.messages', [$message]);
                $output->writeln("published to 'com.weemto.chat.messages' with message {$message}");
            };
            $session->register('com.weemto.chat.say', $say);
        }
        );

        $connection->on('close', function ($reason) use ($loop, &$timer, $output) {
            if ($timer) {
                $loop->cancelTimer($timer);
            }
            $output->writeln("The connected has closed with reason: {$reason}");

        });

        $connection->on('error', function ($reason) use ($output) {
            $output->writeln("The connected has closed with error: {$reason}");
        });

        $connection->open();
    }

}