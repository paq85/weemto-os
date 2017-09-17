<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Controller;

use Paq\GameBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AndroidController extends AbstractController
{
    /**
     * @param Request $request
     * @Route("/android-pl", name="paqgame_gui_android_pl")
     * @Route("/android-pl/", name="paqgame_gui_android_pl_ts")
     */
    public function plLocaleAction(Request $request)
    {
        $this->findAndroidUser($request);

        return new RedirectResponse($this->generateUrl('paqgame_gui_start'));
    }

    /**
     * @param Request $request
     * @Route("/android-pro-pl", name="paqgame_gui_android_pro_pl")
     * @Route("/android-pro-pl/", name="paqgame_gui_android_pro_pl_ts")
     */
    public function plProLocaleAction(Request $request)
    {
        $user = $this->findAndroidUser($request);
        if ($user) {
            $user->setHasProVersion(true);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
        }

        return new RedirectResponse($this->generateUrl('paqgame_gui_start'));
    }

    /**
     * @param Request $request
     * @return null|User
     * @throws \Exception
     */
    private function findAndroidUser(Request $request)
    {
        $user = null;
        
        if ($request->query->has('email') && $request->query->has('email_checksum')) {
            $email = $request->query->get('email');
            if ($email == '') {
                $this->get('logger')->notice('Android app sent an empty email.');
            } else {
                $emailChecksum = $request->query->get('email_checksum');
                $androidBridge = $this->get('paqgame.android_bridge');
                $expectedChecksum = $androidBridge->createChecksum($email);

                if (strcmp($emailChecksum, $expectedChecksum) === 0) {
                    $user = $this->getOrCreateAuthenticatedUser(
                        $request,
                        [
                            'email' => $email,
                            'username' => $androidBridge->getUsernameFromEmail($email),
                            'password' => $androidBridge->getDefaultPasswordForEmail($email),
                            'roles' => [User::ROLE_USER]
                        ]
                    );
                } else {
                    $this->get('logger')->notice(
                        'Android app opened with email given but wrong email checksum. '
                        . "[email = $email; emailChecksum = $emailChecksum]");
                }
            }
        }
        
        return $user;
    }
}