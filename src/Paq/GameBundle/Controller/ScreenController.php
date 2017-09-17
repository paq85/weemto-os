<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Controller;

use Paq\GameBundle\Entity\Repo;
use Paq\GameBundle\Entity\Tag;
use Paq\GameBundle\Entity\User;
use Paq\GameBundle\Entity\Game;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ScreenController extends AbstractController
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="paq_game_main")
     */
    public function detectorAction()
    {
        $detector = new \Mobile_Detect();

        if ($detector->isMobile()) {
            return $this->redirectToRoute('paqgame_gui_controller_start');
        } else {
            return $this->redirectToRoute('paqgame_gui_start');
        }

    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/start", name="paqgame_gui_start")
     */
    public function startAction(Request $request)
    {
        $this->get('session')->start();

        $sessionId = $this->get('session')->getId();
        $foundGames = $this->getDoctrine()->getRepository('PaqGameBundle:Game')->findBySessionId($request->getClientIp());
        $categories = $this->getDoctrine()->getRepository('PaqGameBundle:Tag')->findAllCategoryTags(true);
        $challenges = $this->getDoctrine()->getRepository('PaqGameBundle:Tag')->findBy(
            ['type' => Tag::TYPE_CHALLENGE, 'isFeatured' => true], ['id' => 'DESC']
        );

        $challenges = array_merge($challenges, $categories);

        return $this->render(
            'PaqGameBundle:Screen:start.html.twig',
            [
                'sessionId'         => $sessionId,
                'platform_version'  => $this->getPlatformVersion(),
                'availableGames'    => $foundGames,
                'challenges'        => $challenges
            ]
        );
    }

    /**
     * @ParamConverter("game", class="PaqGameBundle:Game", options={"mapping": {"gcode": "gcode"}})
     * @Security("has_role('ROLE_USER')")
     * @Route("/board/{gcode}/", name="paqgame_gui_board_by_gcode")
     */
    public function boardByGCodeAction(Game $game, Request $request)
    {
        $this->checkGameOwnerAccess($game);

        $this->denyAccessUnlessGranted(
            new Expression(
                'object.hasUser(user)'
            ),
            $game
        );

        $viewMode = $request->get('vm') === 'c' ? 'controller' : 'board';
        switch ($viewMode) {
            case 'board':
                return $this->renderBoard($game);

            case 'controller':
                return $this->renderBoard($game, 'controller');
        }
    }

    /**
     * @Route("/controller-start", name="paqgame_gui_controller_start")
     */
    public function controllerStartAction()
    {
        return $this->render('PaqGameBundle:Screen:controller-start.html.twig');
    }

    /**
     * @param Game $game
     * @param User $user
     * @ParamConverter("game", class="PaqGameBundle:Game", options={"mapping": {"gcode": "gcode"}})
     * @Security("has_role('ROLE_USER')")
     * @Route("/controller/{gcode}", name="paqgame_gui_controller")
     */
    public function controllerAction(Game $game)
    {
        $this->checkGameOwnerAccess($game);

        $this->denyAccessUnlessGranted(
            new Expression(
                'object.hasUser(user)'
            ),
            $game
        );

        return $this->renderBoard($game, 'controller');
    }

    /**
     * @Route("/watcher", name="paqgame_gui_watcher_start")
     */
    public function watcherStartAction(Request $request)
    {
        if ($request->get('gcode')) {
            return $this->redirect($this->generateUrl('paqgame_gui_watcher', ['gcode' => $request->get('gcode')]));
        }

        $gamesRepo = $this->getDoctrine()->getRepository('PaqGameBundle:Game');
        $games = $gamesRepo->findBySessionId($request->getClientIp());
        if (count($games) > 0) {
            // LAN game already exists so he will watch the first Game found
            return $this->redirect(
                $this->generateUrl(
                    'paqgame_gui_watcher',
                    [
                        'gcode' => $games[0]->getGcode()
                    ]
                )
            );
        }

        return $this->render('@PaqGame/Screen/watcher-start.html.twig');
    }

    /**
     * @param Game $game
     * @ParamConverter("game", class="PaqGameBundle:Game", options={"mapping": {"gcode": "gcode"}})
     * @Route("/watcher/{gcode}", name="paqgame_gui_watcher")
     */
    public function watcherAction(Game $game)
    {
        return $this->renderBoard($game, 'watcher');
    }

    /**
     * (Form) action to create a Game for logged in user and redirect to Game's Board
     *
     * @param Request $request
     *  GET string[] tags Tag names to which Game should be limited
     *  GET int enable_lan If "1" then Game's Session ID will be set to Client's IP
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/action/game/create", name="p_g_g_c") // paqgame_gui_game_create
     * @Security("has_role('ROLE_USER')")
     */
    public function gameCreateAction(Request $request)
    {
        $user = $this->getUser();
        $gamesRepo = $this->getDoctrine()->getRepository('PaqGameBundle:Game');
        $gcode = null;
        $tagIds = $request->get('tags', []);

        $lanEnabledGame = (bool) $request->get('enable_lan', false);

        /**
         * If there's a LAN game and user did not requested new Game with specific tags he will be redirected to
         * existing game
         */
        if ($lanEnabledGame && count($tagIds) === 0) {
            /* @var Game[] $games */
            $games = $gamesRepo->findBySessionId($request->getClientIp());
            if (count($games) > 0) {
                // check if there's a Game created by this user
                $redirectToGame = $games[0];
                foreach ($games as $game) {
                    if ($game->isCreatedBy($user)) {
                        $redirectToGame = $game;
                        break;
                    }
                }
                // User requested LAN Game and it already exists so he will join the first Game found
                return $this->redirect(
                    $this->generateUrl(
                        'p_g_g_j',
                        [
                            'gcode' => $redirectToGame->getGcode()
                        ]
                    )
                );
            }
        }

        $userCreatedGames = $gamesRepo->findBy(['createdBy' => $user]);
        if (count($userCreatedGames) > 0) {
            // User has existing game. He will get redirected to that Game.
            $this->addFlash(
                'notice',
                'flash.existing_game'
            );
            $gcode = $userCreatedGames[0]->getGCode();
        } else {
            $em = $this->getDoctrine()->getManager();
            $tags = $em->getRepository('PaqGameBundle:Tag')->findBy(['id' => $tagIds]);
            if (count($tags) === 0) {
                // assign all category tags
                $tags = $em->getRepository('PaqGameBundle:Tag')->findAllCategoryTags();
            }

            $game = new Game();
            $game->setGCode($gamesRepo->generateGCode());
            if ($lanEnabledGame) {
                $game->setSessionId($request->getClientIp());
            }
            $game->setTags($tags);
            $game->setRoundQuestionCountLimit($this->getGameRoundQuestionCountLimit());
            $gameService = $this->getGameService();
            $gameService->onNewGame($game);
            $gameService->onUserAdded($game, $user);

            $gcode = $game->getGCode();
        }

        $viewMode = $request->get('vm', 'default');
        $redirectRoute = in_array($viewMode, ['default', 'b']) ? 'paqgame_gui_board_by_gcode' : 'paqgame_gui_controller';

        return $this->redirect(
            $this->generateUrl(
                $redirectRoute,
                [
                    'gcode' => $gcode,
                    'vm'  => $viewMode
                ]
            )
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/action/join/", name="p_g_g_j") // paqgame_gui_game_join
     */
    public function joinGameAction(Request $request)
    {
        $gcode = $request->get('gcode');
        $game = $this->getDoctrine()->getRepository('PaqGameBundle:Game')->findOneBy(['gcode' => $gcode]);
        if (!$game) {
            throw $this->createNotFoundException('Game not found by GCode: ' . $gcode);
        }

        $this->forward(
            'PaqGameBundle:GameService:gameJoin',
            [
                'user_id' => $this->getUser()->getId(),
                'game_id' => $game->getId(),
                '_locale'   => $request->getLocale()
            ]
        );

        $viewMode = $request->get('vm', 'b');
        $redirectRoute = $viewMode === 'b' ? 'paqgame_gui_board_by_gcode' : 'paqgame_gui_controller';

        return $this->redirect(
            $this->generateUrl(
                $redirectRoute,
                [
                    'gcode' => $game->getGCode()
                ]
            )
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/join/board/by-session", name="paqgame_gui_join_board_by_session")
     */
    public function joinBoardBySessionAction(Request $request)
    {
        $this->get('session')->start();

        $game = $this->getDoctrine()->getRepository('PaqGameBundle:Game')->find($request->get('game_id'));
        if (!$game) {
            throw $this->createNotFoundException('Game not found');
        }

        if ($game->getSessionId() === $this->get('session')->getId()) {
            // TODO: do not authenticate if already authenticated?
            $user = $game->getCreatedBy();
            $this->authenticateAs($user);
        } else {
            throw $this->createAccessDeniedException('You do not have permissions to join selected game');
        }

        return $this->redirect(
            $this->generateUrl(
                'paqgame_gui_board_by_gcode',
                [
                    'gcode' => $game->getGCode()
                ]
            )
        );
    }

    /**
     * @param Game $game
     * @param string $viewMode 'board', 'controller' or 'watcher';
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderBoard(Game $game, $viewMode = 'board')
    {
        /* @var User $user */
        $user = $this->getUser();
        $showAds = (! $game->getCreatedBy()->hasProVersion());
        $gameTagIds = Repo::getIds($game->getTags());

        $this->addFlashWithGCode($game);

        return $this->render(
            '@PaqGame/Screen/board.html.twig',
            [
                'game'          => $game,
                'user'          => $user,
                'viewMode'      => $viewMode,
                'ownerMode'     => $user && $game->isCreatedBy($user),
                'categoryTags'  => $this->getTags(Tag::TYPE_CATEGORY),
                'challengeTags' => $this->getTags(Tag::TYPE_CHALLENGE),
                'gameTagIds'    => $gameTagIds,
                'showAds'       => $showAds
            ]
        );
    }

    /**
     * @param Game $game
     * @return \Symfony\Component\HttpFoundation\Response
     * NOTICE: obsolete we render board in compact view
     */
    private function renderController(Game $game)
    {
        $user = $this->getUser();

        $this->addFlashWithGCode($game);

        return $this->render(
            '@PaqGame/Screen/controller.html.twig',
            [
                'game'          => $game,
                'user'          => $user
            ]
        );
    }

    /**
     * @param string $tagType
     * @return Tag[]
     */
    private function getTags($tagType)
    {
        $tagRepo = $this->getDoctrine()->getRepository('PaqGameBundle:Tag');
        $tags = $tagRepo->findByType($tagType);
        $tagRepo->sortAlphabetically($tags, $this->get('translator'));

        return $tags;
    }

    /**
     * Check if it's the Game owner accessing the Game and if it is then make him join the Game again
     * @param Game $game
     */
    private function checkGameOwnerAccess(Game $game)
    {
        if ($game->isCreatedBy($this->getUser()) && !$game->hasUser($this->getUser())) {
            $this->getGameService()->onUserAdded($game, $this->getUser());
        }
    }

    private function addFlashWithGCode(Game $game)
    {
        $this->addFlash(
            'info',
            'Witaj w pokoju numer: ' . strtoupper($game->getGCode())
        );
    }

}