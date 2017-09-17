<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Command;


use Paq\GameBundle\Service\Question\Generator;
use Paq\GameBundle\Service\Wiki;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlatformVersionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('paqgame:platform:version')
            ->setDescription('Prints PaqGame Platform version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getContainer()->get('paq_game.platform')->getVersion());
    }

}