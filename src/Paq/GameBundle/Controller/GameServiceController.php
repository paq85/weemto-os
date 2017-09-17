<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Controller;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\OptimisticLockException;
use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\User;
use Paq\GameBundle\Service\GameService;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameServiceController extends AbstractController
{
    const HTTP_POLLING_TIMEOUT_DEFAULT = 0;
    const HTTP_POLLING_SLEEP_DEFAULT = 0;

    /**
     * Register user
     *
     * @param Request $request
     * {
     *  "username": "jasiu",
     *  "email": "jasiu@poczta.cos",
     *  "password": "plainTextPassword"
     * }
     * @return JsonResponse
     */
    public function userRegisterAction(Request $request)
    {
        // TODO: reuse FOS UserBundle registration
        $username = $request->get('username');
        $email = $request->get('email');
        $password = $request->get('password');

        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);

        $em->persist($user);
        $em->flush();

        return new JsonResponse((object) [
            'id'        => $user->getId(),
            'username'  => $user->getUsername()
        ]);
    }

    /**
     * @param Game $game
     * @ParamConverter("game", class="PaqGameBundle:Game")
     * @Security("has_role('ROLE_USER')")
     */
    public function gameGetAction(Game $game, Request $request)
    {
        $this->denyAccessUnlessGranted(
            new Expression(
                'object.hasUser(user)'
            ),
            $game
        );

        $response = new Response('', Response::HTTP_OK, ['Content-Type' => 'application/json']);
        $etag = $game->computeETag();
        $response->setEtag($etag);
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        } else {
            $response->setContent($this->get('serializer')->serialize($game, 'json'));
        }

        return $response;
    }

    /**
     * @param Game $game
     * @ParamConverter("game", class="PaqGameBundle:Game")
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function gameResetAction(Game $game, Request $request)
    {
        $this->denyAccessUnlessGranted(
            new Expression(
                'object.isCreatedBy(user)'
            ),
            $game
        );

        $this->getGameService()->onGameReset($game);

        return $this->gameGetAction($game, $request);
    }

    /**
     * @param Game $game
     * @ParamConverter("game", class="PaqGameBundle:Game")
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function gameNextQuestionAction(Game $game, Request $request)
    {
        $this->denyAccessUnlessGranted(
            new Expression(
                'object.isCreatedBy(user)'
            ),
            $game
        );

        $this->getGameService()->onGameNextQuestion($game);

        return $this->gameGetAction($game, $request);
    }

    /**
     * Return a list of Game IDs created by given User
     *
     * @ParamConverter("user", class="PaqGameBundle:User")
     * @param User $user
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function gamesCreatedAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $timeoutAt = time() + $this->getHttpPollingTimeout();
        do {
            $game = $em->getRepository('PaqGameBundle:Game')->findOneBy(['createdBy' => $user->getId()]);

            if (!$game) {
                $em->getConnection()->close();
                sleep($this->getHttpLongPollingSleep());
            }
        }
        while (!$game && $timeoutAt > time());

        if (!$game) {
            return new JsonResponse((object)['error' => 'Game not found.']);
        }

        return $this->createGameDataResponse($game, $game->getCreatedBy());
    }

    /**
     * Return a list of Game IDs matching given criteria
     * - sid Session ID
     */
    public function gamesFindAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $timeoutAt = time() + $this->getHttpPollingTimeout();
        $game = null;

        do {
            $criteria = [];
            if ($request->get('sid')) {
                $criteria['sessionId'] = $request->get('sid');
            }
            if (count($criteria) !== 0) {
                $game = $em->getRepository('PaqGameBundle:Game')->findOneBy($criteria);

                if (!$game) {
                    $em->getConnection()->close();
                    sleep($this->getHttpLongPollingSleep());
                }
            }
        }
        while (!$game && $timeoutAt > time());

        $response = null;
        if ($game) {
            $response = $this->createGameDataResponse($game, $game->getCreatedBy());
        } else {
            $response = new JsonResponse((object)['error' => 'Game not found.']);
        }

        return $response;
    }

    /**
     * @param Game $game
     * @ParamConverter("game", class="PaqGameBundle:Game")
     * @Security("has_role('ROLE_USER')")
     */
    public function gameDeleteAction(Game $game)
    {
        $this->denyAccessUnlessGranted(
            new Expression(
                'object.isCreatedBy(user)'
            ),
            $game
        );

        $em = $this->getDoctrine()->getManager();
        $em->remove($game);
        $em->flush();

        return new JsonResponse([]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @Security("has_role('ROLE_USER')")
     */
    public function gameJoinAction(Request $request)
    {
        $userId = $request->get('user_id');
        $gameId = $request->get('game_id');

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('PaqGameBundle:User')->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $game = $em->getRepository('PaqGameBundle:Game')->find($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        $this->getGameService()->onUserAdded($game, $user);

        return $this->createGameDataResponse($game, $user);
    }

    /**
     * @param Game $game
     * @param int $questionId
     * @ParamConverter("game", class="PaqGameBundle:Game", options={"id" = "gameId"})
     * @Security("has_role('ROLE_USER')")
     */
    public function gameUserAnswerAction(Game $game, $questionId, Request $request)
    {
        $this->denyAccessUnlessGranted(
            new Expression(
                'object.hasUser(user)'
            ),
            $game
        );
        $this->denyAccessUnlessGranted(
            new Expression(
                '!object.isFinished()'
            ),
            $game
        );

        $user = $this->getUser();
        $questionId = (int) $questionId;

        $this->getGameService()->onUserAnswers($game, $user, $questionId, $request->get('answer'));

        return $this->createJsonResponse($game);
    }

    /**
     * Based on request from Controller we match proper Hint based on Game's Question Hints order and set User's answer
     *
     * @param Game $game
     * @param int $questionId
     * @ParamConverter("game", class="PaqGameBundle:Game", options={"id" = "gameId"})
     * @Security("has_role('ROLE_USER')")
     */
    public function gameSetUserAnswerByIndexAction(Game $game, Request $request)
    {
        $answerIndex = (int) $request->get('answer_index');

        if (! $game->hasCurrentQuestion()) {
            throw $this->createNotFoundException('Requested Game has no current Question.');
        }
        if (!isset($game->getCurrentQuestionHintsOrder()[$answerIndex])) {
            throw $this->createNotFoundException("Requested answer not found in Hints order array [index: $answerIndex]");
        }

        $hints = $game->getCurrentQuestion()->getHints();

        $expectedHintId = $game->getCurrentQuestionHintsOrder()[$answerIndex];
        $expectedAnswerHint = null;
        foreach ($hints as $hint) {
            if ($hint->getId() === $expectedHintId) {
                $expectedAnswerHint = $hint;
            }
        }

        if ($expectedAnswerHint === null) {
            throw $this->createNotFoundException("Requested answer not found in Question Hints [Hint ID: $expectedHintId]");
        }

        $answer = $expectedAnswerHint->getText();
        $questionId = $game->getCurrentQuestion()->getId();

        $request->request->set('answer', $answer);

        return $this->gameUserAnswerAction($game, $questionId, $request);
    }

    /**
     * @param Game $game
     * @param User $user
     *
     * @ParamConverter("game", class="PaqGameBundle:Game", options={"id" = "gameId"})
     * @ParamConverter("user", class="PaqGameBundle:User", options={"id" = "userId"})
     * @Security("has_role('ROLE_USER')")
     */
    public function userDisconnectAction(Game $game, User $user)
    {
        $this->denyAccessUnlessGranted(
            new Expression(
                'object.hasUser(user)'
            ),
            $game
        );

        $this->getGameService()->onUserDisconnect($game, $user);

        return $this->createJsonResponse($game);
    }

    /**
     * @param Game $game
     * @param Request $request
     *
     * @ParamConverter("game", class="PaqGameBundle:Game", options={"id" = "gameId"})
     * @Security("has_role('ROLE_USER')")
     */
    public function gameSetTagsAction(Game $game, Request $request)
    {
        $this->denyAccessUnlessGranted(
            new Expression(
                'object.isCreatedBy(user)'
            ),
            $game
        );

        $tagIds = array_map(function($element) { return (int) $element; }, $request->get('tagIds', []));
        $tags = [];
        if (count($tagIds)) {
            $tags = $this->getDoctrine()->getRepository('PaqGameBundle:Tag')->findBy(['id' => $tagIds]);
        }
        $gameService = $this->getGameService($game);
        $gameService->onTagsSelected($game, $tags);

        return $this->createJsonResponse($game);
    }

    /**
     *
     * @param Game $game
     * @param User $user
     * @return Response
     */
    private function createGameDataResponse(Game $game, User $user)
    {
        return $this->createJsonResponse(
            [
                'game' => $game,
                'gui'   => [
                    'users' => [
                        [
                            'id' => $user->getId(),
                            'url'   => $this->generateUrl('paqgame_gui_board_by_gcode', ['gcode' => $game->getGCode()])
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * @return int
     */
    private function getHttpPollingTimeout()
    {
        if ($this->container->hasParameter('paq_game.http_long_polling')) {
            return (int) $this->container->getParameter('paq_game.http_long_polling')['timeout'];
        } else {
            return self::HTTP_POLLING_TIMEOUT_DEFAULT;
        }
    }

    /**
     * @return int
     */
    private function getHttpLongPollingSleep()
    {
        if ($this->container->hasParameter('paq_game.http_long_polling')) {
            return (int) $this->container->getParameter('paq_game.http_long_polling')['sleep'];
        } else {
            return self::HTTP_POLLING_SLEEP_DEFAULT;
        }
    }
} 