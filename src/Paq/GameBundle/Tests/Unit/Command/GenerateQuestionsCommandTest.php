<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit\Command;


use Paq\GameBundle\Command\GenerateQuestionsCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateQuestionsCommandTest extends KernelTestCase
{

    public function testExecuteQuestionsGenerateCSVPolishProvinces()
    {
        $this->markTestIncomplete();

        $kernel = $this->createKernel();
        $kernel->boot();

        $app = new Application($kernel);
        $app->add(new GenerateQuestionsCommand());

        $command = $app->find('questions:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'question-type' => 'polish-provinces',
        ]);

        $this->assertRegExp('/Foo/', $commandTester->getDisplay());
    }
}
 