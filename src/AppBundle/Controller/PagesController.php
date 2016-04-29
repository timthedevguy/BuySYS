<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\ChapterEntity;
use AppBundle\Entity\TopicEntity;

class PagesController extends Controller
{
    /**
     * @Route("/pages/{chapter_slug}/{topic_slug}", name="pages", defaults={"chapter_slug" = "home", "topic_slug" = ""})
     */
    public function showAction(Request $request, $chapter_slug, $topic_slug)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $chapter = $chapters->findOneBySlug($chapter_slug);

        if($chapter == null)
        {
            return $this->render('pages/chapter_error.html.twig', array(
                'page_name' => 'test', 'sub_text' => 'Alliance/Corp sponsered fleet operations', 'toc' => $chapters->findAll(),
                 'chapter_slug' => $chapter_slug));
        }

        $topics = $this->getDoctrine()->getRepository('AppBundle:TopicEntity', 'default');
        $topic = $topics->findTopicBySlugAndChapterId($topic_slug, $chapter->getId());

        if($topic == null & $topic_slug != "")
        {
            return $this->render('pages/topic_error.html.twig', array(
                'page_name' => 'test', 'sub_text' => 'Alliance/Corp sponsered fleet operations', 'toc' => $chapters->findAll(),
                'chapter_slug' => $chapter_slug, 'topic_slug' => $topic_slug));
        }
        elseif($topic_slug == "")
        {
            return $this->render('pages/index.html.twig', array(
                'page_name' => 'test', 'sub_text' => 'Alliance/Corp sponsered fleet operations', 'toc' => $chapters->findAll(),
                'chapter_slug' => $chapter_slug, 'topic_slug' => $topic_slug, 'chapter' => $chapter));
        }

        $topic = $topic[0];

        return $this->render('pages/page.html.twig', array(
            'page_name' => 'test', 'sub_text' => 'Alliance/Corp sponsered fleet operations', 'toc' => $chapters->findAll(),
             'chapter_slug' => $chapter->getSlug(), 'topic_slug' => $topic_slug, 'chapter' => $chapter, 'topic' => $topic));
    }
    /**
     * @Route("/pages/test", name="pages_test")
     */
    public function testAction(Request $request)
    {
        return $this->render('pages/index.html.twig', array(
            'page_name' => 'Upcoming Fleet Ops', 'sub_text' => 'Alliance/Corp sponsered fleet operations'));
    }
}
