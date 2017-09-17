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

class GenerateQuestionsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('questions:generate')
            ->setDescription('Generates Questions and outputs to standard output in requested format')
            ->addArgument('question-type', InputArgument::REQUIRED, 'What type of questions would you like to generate?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionType = $input->getArgument('question-type');
        switch ($questionType) {
            case 'polish-provinces':
                $this->generateAndLoadPolishProvincesQuestions($output);
                break;

            default:
                $output->writeln('Unknown question-type: ' . $questionType);
        }
    }

    private function generateAndLoadPolishProvincesQuestions(OutputInterface $output)
    {
        $generator = new Generator();
        $wiki = new Wiki();

        $questions = $generator->generateQuestionsAboutPolishProvince($wiki->getPolishProvinces());
        foreach ($questions as $question) {
            $hints = $question->getHints();
            $output->writeln(
                sprintf(
                    '"%s","%s","%s","%s","%s"',
                    $question->getText(),
                    $question->getProperAnswer(),
                    $hints[0],
                    $hints[1],
                    $hints[2]
                )
            );
        }
    }
} 