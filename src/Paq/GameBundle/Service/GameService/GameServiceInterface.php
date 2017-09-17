<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service\GameService;


use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\Tag;
use Paq\GameBundle\Entity\User;

interface GameServiceInterface
{

    /**
     * @deprecated Use onGameReset instead?
     * @param Game $game
     */
    public function onNewGame(Game $game);

    /**
     * @param Game $game
     * @param User $user
     */
    public function onUserAdded(Game $game, User $user);

    /**
     * @param Game $game
     * @param User $user
     */
    public function onUserDisconnect(Game $game, User $user);

    /**
     * @param Game $game
     * @param User $user
     * @param int $questionId which Question does User provide an answer for
     * @param string $answer
     */
    public function onUserAnswers(Game $game, User $user, $questionId, $answer);

    /**
     * @param Game $game
     */
    public function onGameReset(Game $game);

    /**
     * User wants next Question to be shown
     *
     * @param Game $game
     */
    public function onGameNextQuestion(Game $game);

    /**
     * Selects new Tags and resets the Game
     *
     * @param Game $game
     * @param Tag[] $tags
     */
    public function onTagsSelected(Game $game, $tags);
}