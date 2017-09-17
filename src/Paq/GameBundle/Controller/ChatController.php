<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ChatController extends AbstractController
{
    /**
     * Displays open chat
     *
     * @Route("/chat/", name="paqgame_gui_chat")
     */
    public function chatAction()
    {
        return $this->render('PaqGameBundle:Chat:chat.html.twig');
    }
}