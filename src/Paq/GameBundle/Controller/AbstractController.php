<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Controller;

use Paq\GameBundle\Entity\User;
use Paq\GameBundle\Service\GameService\GameServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractController extends Controller
{

    /**
     * Returns authenticated User or creates and returns User for "anonymous" account
     *
     * User's display name is set if GET display_name is provided
     *
     * @param Request $request
     * @param array $rawOptions if not specified it will create a temporary account
     *  ['username' => string, 'password' => string, 'email' => string, 'roles' => string[]]
     * @return User
     */
    protected function getOrCreateAuthenticatedUser(Request $request, $rawOptions = [])
    {
        $user = $this->getUser();
        if ($user) {
            return $user;
        }

        $em = $this->getDoctrine()->getManager();
        $uuid = uniqid();
        $optionsResolver = new OptionsResolver();
        // by default it creates a temporary account
        $optionsResolver
            ->setDefaults([
                'username' => "anonymous.$uuid",
                'password' => $uuid,
                'email' => "mail+anonymous.$uuid@anonymous.weemto.com",
                'roles' => [User::ROLE_TEMPORAL, User::ROLE_DEFAULT]
            ]);
        $options = $optionsResolver->resolve($rawOptions);

        /* @var User $user */
        $user = $em->getRepository('PaqGameBundle:User')->findOneBy(['email' => $options['email']]);
        if (!$user) {
            // if it's anonymous user create an account for him/her
            /* JsonResponse $userRegisterResponse */
            $userRegisterResponse = $this->forward(
                'PaqGameBundle:GameService:userRegister',
                [
                    'username'  => $options['username'],
                    'password'  => $options['password'],
                    'email'     => $options['email']
                ]
            );

            if (!$userRegisterResponse->isSuccessful()) {
                throw new \Exception("Could not register user [username = {$options['username']}]");
            }

            $userId = \GuzzleHttp\json_decode($userRegisterResponse->getContent())->id;
            /* @var User $user */
            $user = $em->getRepository('PaqGameBundle:User')->find($userId);
            $user->setRoles($options['roles']);
            $user->setEnabled(true);
            if ($request->get('display_name') !== null) {
                $user->setDisplayName($request->get('display_name'));
            }

            $em->persist($user);
            $em->flush();
        }

        $this->authenticateAs($user);

        return $user;
    }

    /**
     * @return string
     */
    protected function getPlatformVersion()
    {
        $platformVersion = 'unknown';

        try {
            $platformVersion = $this->get('paq_game.platform')->getVersion();
        } catch (\Exception $rex) {
            $this->get('logger')->warn($rex->getMessage());
        }

        return $platformVersion;
    }

    /**
     * @param User $user
     */
    protected function authenticateAs(User $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    /**
     * @return GameServiceInterface
     */
    protected function getGameService()
    {
        return $this->get('paqgame.gameservice');
    }

    /**
     * @param $data
     * @param $statusCode
     * @return Response
     */
    protected function createJsonResponse($data, $statusCode = Response::HTTP_OK)
    {
        return new Response(
            $this->get('serializer')->serialize($data, 'json'),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @return int
     * @throws \InvalidArgumentException if parameter not set in config file
     */
    protected function getGameRoundQuestionCountLimit()
    {
        if ($this->container->hasParameter('paq_game.game_round_question_count_limit')) {
            return (int) $this->container->getParameter('paq_game.game_round_question_count_limit');
        } else {
            throw new \InvalidArgumentException('Game Round Question count limit not set. Check your config file: paq_game.game_round_question_count_limit');
        }
    }
}