<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Controller;

use Doctrine\DBAL\LockMode;
use Paq\GameBundle\Entity\User;
use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Service\GameService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class GuiController extends AbstractController
{

    /**
     * Checking User's account
     *
     * If ANONYMOUS:
     * Form for registering a temporal (ROLE_TEMPORAL) User
     * Once registered it redirects User to a page specified in GET target_path
     *
     * If USER:
     * It redirects User to a page specified in GET target_path
     *
     * @Route("/uc/", name="paqgame_gui_user_check")
     * @param Request $request
     *  string tr target route; If "__game_create_or_join" it will pick one of "p_g_g_j" or "p_g_g_c"
     */
    public function userCheckAction(Request $request)
    {
        $isCreatingGame = false;
        
        $forwardedQuery = $request->query->all();
        if (isset($forwardedQuery['tr'])) {
            unset($forwardedQuery['tr']);
        }

        $targetRoute = $request->get('tr', 'paq_game_main');
        if ($targetRoute === '__game_create_or_join') {
            if ($request->get('gcode', '') !== '') {
                $targetRoute = 'p_g_g_j';
            } else {
                $targetRoute = 'p_g_g_c';
                $isCreatingGame = true;
            }
        }

        $targetPath = $this->generateUrl($targetRoute, $forwardedQuery);

        // User may want to join
        if ($targetRoute === 'p_g_g_j') {
            $gcode = $request->get('gcode');
            $game = $this->getDoctrine()->getRepository('PaqGameBundle:Game')->findOneBy(['gcode' => $gcode]);
            if (!$game) {
                throw $this->createNotFoundException('Game not found by GCode = ' . $gcode);
            }
        }

        $user = $this->getUser();

        if ($user) {
            return $this->redirect($targetPath);
        }

        if ($request->isMethod(Request::METHOD_POST) && $request->get('display_name')) {
            $this->getOrCreateAuthenticatedUser($request);

            return $this->redirect($targetPath);
        } else {
            // if user decides to log in first redirect him to this page once authenticated
            $this->get('session')->set(
                '_security.main.target_path',
                $this->generateUrl('paqgame_gui_user_check', $request->query->all(), UrlGeneratorInterface::ABSOLUTE_URL)
            );

            return $this->render(
                '@PaqGame/Gui/register-temporal.html.twig',
                [
                    'targetRoute' => $targetRoute,
                    'forwardedQuery' => http_build_query($forwardedQuery),
                    'isCreatingGame' => $isCreatingGame
                ]
            );
        }
    }

    /**
     * @param Game $game
     * @ParamConverter("game", class="PaqGameBundle:Game")
     * @Route("/games/{game}/disconnect", name="paqgame_gui_disconnect_game")
     * @Security("has_role('ROLE_USER')")
     */
    public function disconnectGameAction(Game $game)
    {
        $user = $this->getUser();

        $this->denyAccessUnlessGranted(
            new Expression(
                'object.hasUser(user)'
            ),
            $game
        );

        $this->getGameService()->onUserDisconnect($game, $user);

        return $this->redirect($this->generateUrl('paqgame_gui_start'));
    }

    /**
     * @param Request $request
     *  string $username
     *  string $result
     *  string $tag
     *  string $checksum
     * @Route("/summary/", name="paqgame_gui_summary")
     */
    public function summaryAction(Request $request)
    {
        $username = $request->get('username', 'username');
        $result = $request->get('result', '0');
        $tagName = $request->get('tagName', 'tagName');
        $tagId = $request->get('tagId', '1');
        $playerCount = (int) $request->get('playerCount', 1);
        $playerCount = ($playerCount > 0) ? $playerCount : 1;

        $expectedChecksum = $this->generateSummaryChecksum($username, $result, $tagName);
        $checksum = $request->get('checksum', $expectedChecksum);

        if ($checksum !== $expectedChecksum) {
            throw $this->createNotFoundException('Sorry. Result not found. Checksum mismatch. Expected: ' . $expectedChecksum);
        }

        $scorePercent = ($result * 100) / ($playerCount * Game::QUESTIONS_LIMIT);
        $scorePercent = min(round($scorePercent), 100); // for old results we may have > 20 points in single player game

        $tag = $this->getDoctrine()->getRepository('PaqGameBundle:Tag')->find($tagId);

        return $this->render(
            '@PaqGame/Gui/summary.html.twig',
            [
                'username'      => $username,
                'result'        => $result,
                'scorePercent'  => $scorePercent,
                'playerCount'   => $playerCount,
                'tagName'       => $tagName,
                'tag'           => $tag
            ]
        );
    }

    /**
     * @return Response
     * @Route("/options/", name="paqgame_gui_options")
     */
    public function optionsAction()
    {
        return $this->render('@PaqGame/Gui/options.html.twig');
    }

    private function generateSummaryChecksum($username, $result, $tag)
    {
        $usernameLength = mb_strlen($username, 'UTF-8');
        $tagNameLength = mb_strlen($tag, 'UTF-8');

        return base64_encode($usernameLength + (int) $result + $tagNameLength);
    }

}