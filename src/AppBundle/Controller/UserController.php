<?php
namespace AppBundle\Controller;

use AppBundle\Security\AuthorizationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{

    /**
     * @Route("/system/admin/users", name="admin_users")
     */
    public function indexAction(Request $request)
    {
        $users = $this->getDoctrine('default')->getRepository('AppBundle:UserEntity')->findAll();

        return $this->render('access_control/users.html.twig', array(
            'page_name' => 'Access Control', 'sub_text' => '', 'users' => $users, 'roles' => AuthorizationManager::getRoles()
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
     * @Route("/system/admin/users/updateOverride", name="ajax_update_user_override_role")
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

    /**
     * @Route("/system/admin/users/updateOverrideEntitlement", name="ajax_update_user_override_entitlement")
     */
    public function ajax_UpdateOverrideEntitlement(Request $request)
    {
        $id = $request->request->get('id');
        $entitlements = $request->request->get('entitlements');

        // Get Entity Manager
        $em = $this->getDoctrine('default')->getManager();
        $user = $this->getDoctrine('default')->getRepository('AppBundle:UserEntity')->find($id);

        $user->setOverrideEntitlements($entitlements);
        $em->flush();

        return new Response("OK");
    }
}
