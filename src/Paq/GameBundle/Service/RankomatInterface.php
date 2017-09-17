<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Service;

use Paq\GameBundle\Entity\Tag;
use Paq\GameBundle\Model\Rank\RankingResult;

interface RankomatInterface
{
    /**
     * @param int $usersLimit
     * @param Tag|null $tag
     * @return RankingResult[]
     */
    public function getRanking($usersLimit = 10, Tag $tag = null);
}