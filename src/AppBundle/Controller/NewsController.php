<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\NewsEntity;

class NewsController extends Controller
{
    /**
     * @Route("/admin/news", name="admin_news")
     */
    public function indexAction(Request $request)
    {
        $news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAll();

        return $this->render('news/index.html.twig', array(
            'page_name' => 'News', 'sub_text' => '', 'mode' => 'ADMIN', 'news' => $news
        ));
    }

    /**
     * @Route("/admin/news/create", name="ajax_create_news")
     */
    public function ajax_CreateAction(Request $request)
    {
        if($request->getMethod() == 'POST') {

            $em = $this->getDoctrine('default')->getManager();
            $subject = $request->request->get('subject');
            $content = $request->request->get('content');

            $item = new NewsEntity();
            $item->setContent($content);
            $item->setSubject($subject);
            $item->setAuthor($this->getUser()->getUsername());

            $em->persist($item);
            $em->flush();

            return new Response("OK");
        }

        return $this->render('news/create.html.twig');
    }

    /**
     * @Route("/admin/news/edit", name="ajax_edit_news")
     */
    public function ajax_EditAction(Request $request)
    {
        return $this->render('news/edit.html.twig');
    }

    /**
     * @Route("/admin/news/delete", name="ajax_delete_news")
     */
    public function ajax_DeleteAction(Request $request)
    {
        return $this->render('news/delete.html.twig');
    }
}
