<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Integration\Entity;


use Paq\GameBundle\Entity\QuestionRepository;
use Paq\GameBundle\Entity\Repo;
use Paq\GameBundle\Entity\TagEnum;
use Paq\GameBundle\Tests\Integration\IntegrationTestCase;

class QuestionRepositoryTest extends IntegrationTestCase
{

    public function testDeleteAll()
    {
        $this->getRepo()->deleteAll();

        $questions = $this->getRepo()->findAll();
        $this->assertCount(0, $questions, 'There should be no Questions in the DB');
    }

    public function testGetRandomQuestionWithIncludedTagIds()
    {
        $historyTag = $this->refRepo->getReference('tag-' . TagEnum::HISTORY);
        $question = $this->getRepo()->getRandomQuestion(['included_tag_ids' => [$historyTag->getId()]]);

        $this->assertNotNull($question);
        $questionTagIds = Repo::getIds($question->getTags());
        $this->assertContains($historyTag->getId(), $questionTagIds, 'Picked Question should be assigned to the required Tag');
    }

    /**
     * @depends testGetRandomQuestionWithIncludedTagIds
     */
    public function testGetRandomQuestionWithExcludedId()
    {
        $historyTag = $this->refRepo->getReference('tag-' . TagEnum::HISTORY);
        $question = $this->getRepo()->getRandomQuestion(['included_tag_ids' => [$historyTag->getId()], 'excluded_ids' => [1]]);

        $this->assertNotEquals(1, $question->getId(), 'It should be any question beside that one with ID = 1');
    }

    public function testGetRandomQuestionWithExcludedTagId()
    {
        // FIXME: This could not fail even if the code is wrongly written
        $tagIds = Repo::getIds($this->em->getRepository('PaqGameBundle:Tag')->findAll());

        $questions = $this->getRepo()->findAll();
        foreach ($questions as $currentQuestion) {
            $currentTag = $currentQuestion->getTags()[0];

            $nextQuestion = $this->getRepo()->getRandomQuestion(
                [
                    'included_tag_ids'  => $tagIds,
                    'excluded_ids' => [$currentQuestion->getId()],
                    'excluded_tag_ids' => [$currentTag->getId()]
                ]
            );

            $this->assertFalse($nextQuestion->hasTag($currentTag), 'New Question should not have excluded Tag. New Question ID: ' . $nextQuestion->getId());
        }
    }

    public function testGetRandomQuestionWithExcludedTagNames()
    {
        // FIXME: This could not fail even if the code is wrongly written
        $tagIds = Repo::getIds($this->em->getRepository('PaqGameBundle:Tag')->findAll());

        $questions = $this->getRepo()->findAll();
        foreach ($questions as $currentQuestion) {
            $currentTag = $currentQuestion->getTags()[0];

            $nextQuestion = $this->getRepo()->getRandomQuestion(
                [
                    'included_tag_ids'  => $tagIds,
                    'excluded_tag_names' => [$currentTag->getName()]
                ]
            );

            $this->assertFalse($nextQuestion->hasTag($currentTag), 'New Question should not have excluded Tag. New Question ID: ' . $nextQuestion->getId());
        }
    }

    /**
     * @expectedException \Doctrine\ORM\NoResultException
     */
    public function testGetRandomQuestionThrowsAnExceptionWhenNoQuestionFound()
    {
        $this->getRepo()->deleteAll();

        $tagIds = Repo::getIds($this->em->getRepository('PaqGameBundle:Tag')->findAll());

        $this->getRepo()->getRandomQuestion(['included_tag_ids' => $tagIds]);
    }

    public function testQuestionDoesNotRepeatUntilIfAnyQuestionHasNotBeenUsedYet()
    {
        $this->markTestIncomplete('Feature not implemented yet');

        $questions = $this->getRepo()->findAll();
        $questionCount = count($questions);
        $repo = $this->getRepo();
        $pickedQuestionIds = [];

        $currentQuestion = null;
        for ($i = 0; $i < $questionCount; ++$i) {
            // pick until we use all questions
            $currentQuestion = $repo->getRandomQuestion([
                'excluded_ids' => ($currentQuestion === null) ? [] : [$currentQuestion->getId()]
            ]);

            $this->assertNotContains($currentQuestion->getId(), $pickedQuestionIds, 'Question should not have been picked second time.');

            $pickedQuestionIds[] = $currentQuestion->getId();
        }

        // TODO: second test
        /*
        // pick once more so one of the questions gets picked again as there's no other not picked questions
        $currentQuestion = $currentQuestion = $repo->getRandomQuestion([
            'excluded_id' => ($currentQuestion === null) ? null : $currentQuestion->getId()
        ]);
        */
    }

    public function testDeletingQuestionDoesNotDeleteAssociatedTags()
    {
        $question = $this->getQuestion('7');
        $expectedTagCount = count($this->em->getRepository('PaqGameBundle:Tag')->findAll());

        $this->em->remove($question);
        $this->em->flush();

        $actualTagCount = count($this->em->getRepository('PaqGameBundle:Tag')->findAll());
        $this->assertEquals($expectedTagCount, $actualTagCount, 'Tags removed when removing Question!');
    }

    public function testFindMissingCorrectHint()
    {
        $expectedQuestion = $this->getQuestion('missing_correct_hint-1');
        $repo = $this->getRepo();

        $questions = $repo->findAllMissingCorrectHint();
        $this->assertCount(1, $questions, 'There should be one Question without correct hint');
        $this->assertEquals($expectedQuestion->getId(), $questions[0]->getId());
    }

    /**
     * @return QuestionRepository
     */
    private function getRepo()
    {
        return $this->em->getRepository('PaqGameBundle:Question');
    }

}
