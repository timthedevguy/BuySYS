<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login_route")
     */
    public function loginAction(Request $request)
    {
        // Get Maintenance Mode flag
        // Note: If null is returned then no settings exists, proceed to
        // generate them.
        $isDown = $this->get("helper")->getSetting("system_maintenance");

        if($isDown != null) {

            if($isDown == "0") {

                // get the login error if there is one
                $error = $this->get('security.authentication_utils')->getLastAuthenticationError();

                // Generate an oauth code to ensure Session doesn't get hijacked
                $oauth = uniqid('OAA', true);
                $request->getSession()->set('oauth', $oauth);

                $clientID = $this->container->getParameter('sso_client_id');
				$scopes = $this->container->getParameter('sso_scopes');
                $callbackURL = $this->generateUrl('sso_callback', array(), true);

                // build SSO URL
                $login_url =  '/oauth/authorize?response_type=code&redirect_uri='.$callbackURL.'&client_id='.$clientID;
				$login_url .= '&scope='.urlencode($scopes).'&state='.$oauth;
				
				// needs to be fully-encoded. This is a double-redirect.
				$login_url = "https://login.eveonline.com/Account/LogOn?ReturnUrl=".urlencode($login_url);

                return $this->render('security/login.html.twig', array('error' => $error, 'login_url' => $login_url));

            } else {

                // In Maintenance Mode, display the message
                return $this->render('security/maintenance.html.twig');
            }
        } else {

            // No settings exists, Generate them and then display the login page again.
            $this->get("helper")->generateDefaultSettings();
            $this->addFlash("success", 'Generated default settings, login to continue!');
            return $this->redirectToRoute('login_route');
        }
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    /**
     * @Route("/sso/callback", name="sso_callback")
     */
    public function ssoCallbackAction(Request $request)
    {
        return $this->redirectToRoute('homepage');
    }

}
