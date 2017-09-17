<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LocaleRedirectController extends AbstractController
{

    public function localeAction(Request $request)
    {
        $defaultLocale = $this->container->getParameter('locale');
        $oldUri = $request->getRequestUri();

        if (stripos($oldUri, '/pl/') === 0 || stripos($oldUri, '/app_dev.php/pl/') === 0) {
            throw new NotFoundHttpException('Page not found for URI: ' . $oldUri);
        }

        $newUri = '/' . $defaultLocale . $oldUri;
        if (stripos($oldUri, 'app_dev.php') !== false) {
            $newUri = str_replace('app_dev.php', 'app_dev.php/' . $defaultLocale, $oldUri);
        }

        return $this->redirect($newUri, Response::HTTP_MOVED_PERMANENTLY);
    }
}