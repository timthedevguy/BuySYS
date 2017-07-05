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
     * @Route("/system/admin/news", name="admin_news")
     */
    public function indexAction(Request $request)
    {
        $news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAllOrderedByDate();

        return $this->render('news/index.html.twig', array(
            'page_name' => 'News', 'sub_text' => '', 'news' => $news
        ));
    }

    /**
     * @Route("/system/admin/news/create", name="ajax_create_news")
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
     * @Route("/system/admin/news/edit", name="ajax_edit_news")
     */
    public function ajax_EditAction(Request $request)
    {
        if($request->getMethod() == 'POST') {

            $id = $request->request->get('id');

            $em = $this->getDoctrine('default')->getManager();
            $item = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->find($id);

            $item->setContent($request->request->get('content'));
            $em->flush();

            return new Response("OK");
        }

        $id = $request->query->get('id');

        $item = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->find($id);

        return $this->render('news/edit.html.twig', array('item' => $item));
    }

    /**
     * @Route("/system/admin/news/delete", name="ajax_delete_news")
     */
    public function ajax_DeleteAction(Request $request)
    {
        if($request->getMethod() == 'POST') {

            $id = $request->request->get('id');

            $em = $this->getDoctrine('default')->getManager();
            $item = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->find($id);

            $em->remove($item);
            $em->flush();

            return new Response("OK");
        }

        $id = $request->query->get('id');

        return $this->render('news/confirm.html.twig', array('modal' => '#news_modal', 'id' => $id, 'returnPath' => 'ajax_delete_news',
        'functionToRun' => 'deleteRow', 'args' => $id));
    }

    /**
     * @Route("/news/view", name="ajax_view_news")
     */
    public function ajax_ViewAction(Request $request)
    {
        $id = $request->query->get('id');
        $item = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->find($id);

        return $this->render('news/view.html.twig', array('item' => $item));
    }

    /**
     * @Route("/news/latest", name="ajax_latest_news")
     */
    public function ajax_LatestAction(Request $request)
    {
        $news = $this->getDoctrine()->getRepository('AppBundle:NewsEntity', 'default')->findAllAfterDate($this->getUser()->getLastLogin());

        if(count($news) != 0) {

            $template = $this->render('elements/notificationMenu.html.twig', Array ( 'items' => $news, 'total' => count($news)));
            return $template;
        }

        return new Response('');
    }

    /**
     * @Route("/news/all", name="ajax_all_news")
     */
    public function ajax_AllAction(Request $request)
    {
        $news = $this->getDoctrine()->getRepository('AppBundle:NewsEntity', 'default')->findAllOrderedByDate();

        $template = $this->render('news/navControlTab1.html.twig', Array ( 'items' => $news ));
        return $template;
    }
}
