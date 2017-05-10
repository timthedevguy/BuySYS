<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{

    private $rolesArray = Array('ROLE_SYSTEM_ADMIN',
                                'ROLE_TRANSACTION_ADMIN',
                                'ROLE_BUY_ADMIN',
                                'ROLE_SELL_ADMIN',
                                'ROLE_SRP_ADMIN',
                                'ROLE_EDITOR',
                                'ROLE_MEMBER',
                                'ROLE_ALLY',
                                'ROLE_GUEST',
                                'ROLE_DENIED');

    /**
     * @Route("/system/admin/users", name="admin_users")
     */
    public function indexAction(Request $request)
    {
        $users = $this->getDoctrine('default')->getRepository('AppBundle:UserEntity')->findAll();

        return $this->render('users/index.html.twig', array(
            'page_name' => 'Users', 'sub_text' => '', 'users' => $users, 'roles' => $this->rolesArray
        ));
    }

    /**
     * @Route("/system/admin/users/disable", name="ajax_disable_user")
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
     * @Route("/system/admin/users/enable", name="ajax_enable_user")
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

    /**
     * @Route("/system/admin/users/updateOverride", name="ajax_update_override_role")
     */
    public function ajax_UpdateOverrideRole(Request $request)
    {
        $id = $request->request->get('id');
        $role = $request->request->get('role');

        // Get Entity Manager
        $em = $this->getDoctrine('default')->getManager();
        $user = $this->getDoctrine('default')->getRepository('AppBundle:UserEntity')->find($id);

        $user->setOverrideRole($role);
        $em->flush();

        return new Response("OK");
    }
}
