<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service\GameService;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\User;
use Paq\GameBundle\Service\GameService\GameServiceInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Game service taking care of storing all data in databse - using transactions if needed
 */
class TransactionGameService implements GameServiceInterface
{

    /**
     * @var GameService
     */
    private $gameService;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(GameService $gameService, EntityManager $entityManager)
    {
        $this->gameService = $gameService;
        $this->em = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public function onNewGame(Game $game)
    {
        $this->gameService->onNewGame($game);

        $this->em->persist($game);
        $this->em->flush();
    }

    /**
     * @inheritdoc
     */
    public function onUserAdded(Game $game, User $user)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $this->gameService->onUserAdded($game, $user);

            $this->em->persist($game);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollback();
            throw $ex;
        }
    }

    /**
     * @inheritdoc
     */
    public function onUserDisconnect(Game $game, User $user)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $this->gameService->onUserDisconnect($game, $user);

            $this->em->persist($game);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollback();
            throw $ex;
        }
    }

    /**
     * @inheritdoc
     */
    public function onUserAnswers(Game $game, User $user, $questionId, $answer)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $game = $this->em->getRepository('PaqGameBundle:Game')->find($game->getId(), LockMode::PESSIMISTIC_WRITE);

            $this->gameService->onUserAnswers($game, $user, $questionId, $answer);

            $this->em->persist($game);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (OptimisticLockException $ole) {
            $this->em->getConnection()->rollback();
            throw $ole;
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollback();
            throw $ex;
        }
    }

    /**
     * @inheritdoc
     */
    public function onGameReset(Game $game)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $this->gameService->onGameReset($game);

            $this->em->persist($game);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollback();
            throw $ex;
        }
    }

    /**
     * @inheritdoc
     */
    public function onGameNextQuestion(Game $game)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $this->gameService->onGameNextQuestion($game);

            $this->em->persist($game);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollback();
            throw $ex;
        }
    }

    /**
     * @inheritdoc
     */
    public function onTagsSelected(Game $game, $tags)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $this->gameService->onTagsSelected($game, $tags);

            $this->em->persist($game);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollback();
            throw $ex;
        }
    }

}