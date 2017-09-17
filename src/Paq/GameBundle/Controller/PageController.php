<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Controller;


use Doctrine\ORM\NoResultException;
use Paq\GameBundle\Entity\Question;
use Paq\GameBundle\Entity\Repo;
use Paq\GameBundle\Entity\Tag;
use Paq\GameBundle\Model\Rank\RankingResult;
use Paq\GameBundle\Service\RankomatInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{

    /**
     * Displays page template with prefix "help"
     *
     * @param string $page
     *
     * @Route("/help/{page}", name="paq_game_gui_page_help")
     */
    public function helpAction($page)
    {
        $response = new Response();

        $date = new \DateTime();
        $date->modify('+1 hour');
        $response->setExpires($date);
        $response->setPublic();

        /**
         * INFO: To add new page:
         * 1. Create a new file in Resources/views/Page, eg. help-foo.html.twig
         * 2. Add new page to $allowedPages array
         */
        $allowedPages = ['main'];
        if (! in_array($page, $allowedPages)) {
            throw $this->createNotFoundException('The help page does not exist');
        }

        return $this->render('PaqGameBundle:Page:help-' . $page . '.html.twig', [], $response);
    }

    /**
     * Displays page template with prefix "about"
     *
     * @param string $page
     *
     * @Route("/about/{page}", name="paq_game_gui_page_about")
     */
    public function aboutAction($page)
    {
        $response = new Response();

        // FIXME: Do not cache whole page - eg. log in panel should not be cached
        /*
        $date = new \DateTime();
        $date->modify('+1 hour');
        $response->setExpires($date);
        $response->setPublic();
        */

        switch ($page) {
            case 'main' :
                return $this->render('PaqGameBundle:Page:about-main.html.twig', [], $response);

            case 'terms-and-conditions' :
                return $this->render('PaqGameBundle:Page:about-termsandconditions.html.twig', [], $response);

            case 'questions':
                $tags = $this->getDoctrine()->getRepository('PaqGameBundle:Tag')->findAllCategoryTags();
                $questions = $this->findQuestions($tags);

                return $this->render('PaqGameBundle:Page:about-questions.html.twig', ['questions' => $questions], $response);

            case 'challenges':
                return new RedirectResponse($this->generateUrl('paq_game_gui_page_challenges'));
            
            case 'weemto-pro' :
                return $this->render('PaqGameBundle:Page:weemto-pro.html.twig', [], $response);

            default:
                throw $this->createNotFoundException('The about page does not exist');
        }
    }

    /**
     * Displays page template with prefix "challenge"
     *
     * @param int $tagId
     * @param string $tagName
     * @param Request $request
     *
     * @Route("/challenges/{tagId}/{tagName}", requirements={"tagId" = "\d+"}, name="paq_game_gui_page_challenge")
     * @Route("/challenges/{tagName}", name="paq_game_gui_page_challenge_obsolete")
     * @Route("/challenges-list", name="paq_game_gui_page_challenges")
     */
    public function challengesAction($tagId = null, $tagName = null, Request $request)
    {
        $response = new Response();

        // FIXME: Do not cache whole page - eg. log in panel should not be cached
        /*
        $date = new \DateTime();
        $date->modify('+1 hour');
        $response->setExpires($date);
        $response->setPublic();
        */

        $tagRepo = $this->getDoctrine()->getRepository('PaqGameBundle:Tag');

        // handling old, obsolete route "paq_game_gui_page_challenge_obsolete"
        if (null === $tagId && null !== $tagName) {
            $tag = $tagRepo->findOneBy(['name' => $tagName]);
            if ($tag) {
                return $this->redirectToRoute(
                    'paq_game_gui_page_challenge',
                    ['tagId' => $tag->getId(), 'tagName' => $tag->getName()],
                    Response::HTTP_MOVED_PERMANENTLY
                    );
            }
        }

        switch ($tagId) {
            case null:
                $this->get('session')->start();
                $sessionId = $this->get('session')->getId();
                $challenges = $tagRepo->findAllChallengeTags($request->getLocale());
                $categories = $tagRepo->findAllCategoryTags();
                $tagRepo->sortAlphabetically($categories, $this->get('translator'));

                $tags = array_merge($categories, $challenges);
                return $this->render(
                    'PaqGameBundle:Page:challenge-list.html.twig',
                    [
                        'challenges' => $tags,
                        'sessionId' => $sessionId
                    ],
                    $response
                );

            default:
                $tag = $tagRepo->find($tagId);

                if (!$tag) {
                    throw $this->createNotFoundException('Requested challenge does not exist [ID = ' . $tagId);
                }

                $questions = $tag->getQuestions()->slice(0, 5);

                return $this->render(
                    'PaqGameBundle:Page:challenge-single.html.twig',
                    [
                        'tag' => $tag,
                        'questions' => $questions,
                        'rankResults' => $this->getRanking(20, $tag)
                    ],
                    $response
                );
        }
    }

    /**
     * @Route("/ranking/", name="paqgame_gui_ranking")
     * @Route("/ranking/{tagId}", name="paqgame_gui_ranking_tag")
     */
    public function rankingAction($tagId = null)
    {
        $tag = null;
        if (null !== $tagId) {
            $tag = $this->getDoctrine()->getRepository('PaqGameBundle:Tag')->find($tagId);
        }
        return $this->render('PaqGameBundle:Page:ranking.html.twig', ['rankResults' => $this->getRanking(20, $tag), 'tag' => $tag]);
    }

    /**
     * @param int $usersLimit
     * @param Tag|null $tag
     * @return RankingResult[]
     */
    private function getRanking($usersLimit, Tag $tag = null)
    {
        return $this->get('paqgame.rankomat')->getRanking($usersLimit, $tag);
    }

    /**
     * @param $tags
     * @return Question[]
     */
    private function findQuestions($tags)
    {
        $questions = [];
        try {
            $questions[] = $this->getDoctrine()->getRepository('PaqGameBundle:Question')->getRandomQuestion([
                'included_tag_ids' => Repo::getIds($tags)
            ]);
        } catch (NoResultException $nre) {
            // no worries ... for now
        }

        return $questions;
    }
}