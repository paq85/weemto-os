<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Controller;

use Paq\GameBundle\Entity\User;
use Paq\GameBundle\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class TestController extends Controller
{

    /**
     * @Route("/jasmine/", name="paqgame_test_jasmine")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jasmineAction()
    {
        if ($this->get('kernel')->isDebug()) {
            return $this->render('PaqGameBundle:Test:jasmine-SpecRunner.html.twig');
        } else {
            return $this->createAccessDeniedException();
        }
    }

}