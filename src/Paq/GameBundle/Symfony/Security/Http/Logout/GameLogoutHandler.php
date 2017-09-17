<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Symfony\Security\Http\Logout;

use Paq\GameBundle\Entity\GameRepository;
use Paq\GameBundle\Entity\User;
use Paq\GameBundle\Service\GameService\GameServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class GameLogoutHandler implements LogoutHandlerInterface
{

    /**
     * @var GameServiceInterface
     */
    private $gameService;

    /**
     * @var GameRepository
     */
    private $gameRepo;

    public function __construct(GameServiceInterface $gameService, GameRepository $gameRepository)
    {
        $this->gameService = $gameService;
        $this->gameRepo = $gameRepository;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $user = $token->getUser();
        if (! $user instanceof User) {
            return;
        }

        $games = $this->gameRepo->findByUser($user);
        if (count($games) === 0) {
            return;
        }

        foreach ($games as $game) {
            if ($game->isCreatedBy($user)) {
                // ignoring as creator can not leave his/hers Game
            } else {
                $this->gameService->onUserDisconnect($game, $user);
            }
        }
    }
}