<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Paq\GameBundle\Model\Rank\RankingResult;
use Paq\GameBundle\Model\UserScoreLogListenerInterface;
use Paq\GameBundle\Service\RankomatInterface;

class UserScoreLogRepository extends EntityRepository implements UserScoreLogListenerInterface, RankomatInterface
{
    public function onNewUserScoreLogs($logs)
    {
        foreach ($logs as $log) {
            $this->getEntityManager()->persist($log);
        }

        $this->getEntityManager()->flush($logs);
    }

    public function getRanking($usersLimit = 10, Tag $tag = null)
    {
        $results = [];

        if (null === $tag) {
            /**
             * MySQL equivalent:
             * SELECT user_id, SUM(pointsWon) sumOfPoints FROM UserScoreLog GROUP BY user_id ORDER BY sumOfPoints DESC LIMIT 10;
             */
            $dql = 'SELECT NEW Paq\GameBundle\Model\Ranking\RankingResult(u.username, SUM(usl.pointsWon))'
                . ', SUM(usl.pointsWon) as sumOfPoints FROM PaqGameBundle:UserScoreLog usl'
                . ' JOIN usl.user u'
                . ' GROUP BY usl.user ORDER BY sumOfPoints DESC';

            $results = $this->getEntityManager()->createQuery($dql)
                ->setMaxResults($usersLimit)
                ->useResultCache(true, 60)
                ->getResult();
        } else {
            /**
             * MySQL equivalent:
             * SELECT usl.user_id, SUM(usl.pointsWon) sumOfPoints
             * FROM UserScoreLog usl LEFT JOIN Question q ON usl.question_id = q.id
             * WHERE q.id IN (SELECT qhs.questionId FROM QuestionHasTags qhs WHERE qhs.tagId = 5)
             * GROUP BY user_id ORDER BY sumOfPoints DESC LIMIT 10;
             */
            $dql = 'SELECT NEW Paq\GameBundle\Model\Ranking\RankingResult(u.username, SUM(usl.pointsWon))'
                . ', SUM(usl.pointsWon) as sumOfPoints FROM PaqGameBundle:UserScoreLog usl'
                . ' JOIN usl.user u JOIN usl.question q JOIN q.tags t'
                . ' WHERE t.id = :tagId'
                . ' GROUP BY usl.user ORDER BY sumOfPoints DESC';

            $results = $this->getEntityManager()->createQuery($dql)
                ->setMaxResults($usersLimit)
                ->setParameter('tagId', $tag->getId())
                ->useResultCache(true, 60)
                ->getResult();
        }

        $rankingResults = [];
        foreach ($results as $result) {
            $rankingResults[] = $result[0];
        }

        $this->updateRankingPlace($rankingResults);

        return $rankingResults;
    }

    /**
     * @param RankingResult[] $results sorted results in descending order
     */
    private function updateRankingPlace($results)
    {
        // decide which place player has - players with same score have the same place
        $currentPlace = 1;
        $currentScore = 0;
        /* @var RankingResult $result */
        foreach ($results as $result) {
            if ($result->getScore() < $currentScore) {
                ++$currentPlace;
            }

            $currentScore = $result->getScore();
            $result->setPlace($currentPlace);
        }
    }
}