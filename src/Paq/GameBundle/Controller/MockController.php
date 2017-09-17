<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class MockController extends Controller
{

    public function getGameAction()
    {
        return new JsonResponse(json_decode(file_get_contents(__DIR__ .'/../Resources/doc/api/game_GET_200.json')));
    }
} 