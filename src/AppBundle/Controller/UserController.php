<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @Route("/admin/users", name="admin_users")
     */
    public function indexAction(Request $request)
    {
        $users = $this->getDoctrine('default')->getRepository('AppBundle:UserEntity')->findAll();

        return $this->render('users/index.html.twig', array(
            'page_name' => 'Users', 'sub_text' => '', 'mode' => 'ADMIN', 'users' => $users
        ));
    }

    /**
     * @Route("/admin/users/disable", name="ajax_disable_user")
     */
    public function ajax_DisableAction(Request $request)
    {
        $id = $request->request->get('id');

        // Get Entity Manager
        $em = $this->getDoctrine('default')->getManager();
        $user = $this->getDoctrine('default')->getRepository('AppBundle:UserEntity')->find($id);

        $user->setIsActive(false);
        $em->flush();

        return new Response("OK");
    }

    /**
     * @Route("/admin/users/enable", name="ajax_enable_user")
     */
    public function ajax_EnableAction(Request $request)
    {
        $id = $request->request->get('id');

        // Get Entity Manager
        $em = $this->getDoctrine('default')->getManager();
        $user = $this->getDoctrine('default')->getRepository('AppBundle:UserEntity')->find($id);

        $user->setIsActive(true);
        $em->flush();

        return new Response("OK");
    }
}
