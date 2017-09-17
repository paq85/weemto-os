<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Integration\Entity;


use Paq\GameBundle\Entity\TagEnum;
use Paq\GameBundle\Entity\TagRepository;
use Paq\GameBundle\Tests\Integration\IntegrationTestCase;

class TagRepositoryTest extends IntegrationTestCase
{

    public function testGetFindsByName()
    {
        $tag = $this->getRepo()->get(TagEnum::GEOGRAPHY);

        $this->assertInstanceOf('\\Paq\\GameBundle\\Entity\\Tag', $tag);
        $this->assertEquals(TagEnum::GEOGRAPHY, $tag->getName());
    }

    public function testGetRandom()
    {
        $tags = $this->getRepo()->findAll();
        $tagsCount = count($tags);
        $tagPickCount = [];
        $pickRation = 10;

        // pick random tags at least $pickRation times more than there's tags available
        $i = 0;
        do {
            $tag = $this->getRepo()->getRandom();
            isset($tagPickCount[$tag->getId()]) ? $tagPickCount[$tag->getId()]++ : $tagPickCount[$tag->getId()] = 1;
            ++$i;
        } while ($i < ($tagsCount * $pickRation));

        $this->assertCount($tagsCount, $tagPickCount, 'It is very likely that every tag should have been picked at least once.');

        foreach ($tags as $tag) {
            $this->assertArrayHasKey($tag->getId(), $tagPickCount, 'Its very likely that tag should have been picked at least once. [Tag ID: ' . $tag->getId() . '');
            $this->assertGreaterThanOrEqual(1, $tagPickCount[$tag->getId()], 'Its very likely that tag should have been picked at least once.');
        }
    }

    public function testGetRandomWithExcludedByNames()
    {
        $expectedTagName = TagEnum::$categories[0];
        $excludedNames = array_merge(TagEnum::$categories, TagEnum::$generated, ['challenge_1', 'wyzwanie_1', 'wyzwanie_2']);
        unset($excludedNames[0]); // remove the tag we expect to get

        $tag = $this->getRepo()->getRandom(['excluded_names' => $excludedNames]);
        $this->assertEquals($expectedTagName, $tag->getName());
    }

    /**
     * @depends testGetFindsByName
     */
    public function testGetRandomWithExcludedByIds()
    {
        $expectedTagName = TagEnum::$categories[0];
        $excludedNames = array_merge(TagEnum::$categories, TagEnum::$generated, ['challenge_1', 'wyzwanie_1', 'wyzwanie_2']);
        unset($excludedNames[0]); // remove the tag we expect to get
        $excludedTags = $this->getRepo()->findBy(['name' => $excludedNames]);
        foreach ($excludedTags as $excludedTag) {
            $excludedIds[] = $excludedTag->getId();
        }

        $tag = $this->getRepo()->getRandom(['excluded_ids' => $excludedIds]);
        $this->assertEquals($expectedTagName, $tag->getName(), 'Tags should be excluded by their IDs');
    }

    public function testFindAllCategoryTags()
    {
        $tags = $this->getRepo()->findAllCategoryTags();

        $this->assertInstanceOf('\\Paq\\GameBundle\\Entity\\Tag', $tags[0]);
        $this->assertNotContains('@', $tags[0]->getName());
    }

    /**
     * @return TagRepository
     */
    private function getRepo()
    {
        return $this->em->getRepository('PaqGameBundle:Tag');
    }
}
