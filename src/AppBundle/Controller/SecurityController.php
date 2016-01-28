<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use AppBundle\Form\ChangePasswordType;
use AppBundle\Model\ChangePasswordModel;
use AppBundle\Entity\SettingEntity;

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

                // Get User Agent string
                $user_agent = $items = $request->server->get('HTTP_USER_AGENT');

                // Check if this is the In Game Browser or not
                if(!strpos($user_agent, 'EVE-IGB')) {

                    $authenticationUtils = $this->get('security.authentication_utils');

                    // get the login error if there is one
                    $error = $authenticationUtils->getLastAuthenticationError();

                    // last username entered by the user
                    $lastUsername = $authenticationUtils->getLastUsername();

                    return $this->render('security/login.html.twig', array('last_username' => $lastUsername, 'error' => $error));
                } else {

                    // This is the IGB, display the error
                    return $this->render('security/igb_error.html.twig');
                }
            } else {

                // In Maintenance Mode, display the message
                return $this->render('security/maintenance.html.twig');
            }
        } else {

            // No settings exists, Generate them and then display the login
            // page again.
            $this->get("helper")->generateDefaultSettings();
            $this->addFlash("success", 'Generated default settings, login to continue!');
            return $this->redirectToRoute('login_route');
        }
    }

    /**
     * @Route("/devlogin", name="devlogin")
     */
    public function devloginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
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
     * @Route("/change_password", name="change_password")
     */
    public function changePasswordAction(Request $request)
    {
        // 1) build the form
        //$user = new User();
        $data = new ChangePasswordModel();
        $form = $this->createForm(new ChangePasswordType(), $data);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted())
        {
            if($this->get('security.password_encoder')->isPasswordValid($this->getUser(), $data->getCurrentPassword()) == true) {

                if($this->get('security.password_encoder')->isPasswordValid($this->getUser(), $data->getNewPassword()) != true)
                {
                    $user = $this->getUser();

                    $password = $this->get('security.password_encoder')
                        ->encodePassword($user, $data->getNewPassword());
                    $user->setPassword($password);

                    // 4) save the User!
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    $this->addFlash("success", 'Password changed successfully!');
                    return $this->redirectToRoute('homepage');
                }
                else
                {
                    $this->addFlash('error', 'Cannot change password to the same password that is currently in use.');
                }
            }
            else
            {
                $this->addFlash('error', 'Current password is incorrect or empty.');
            }



        } elseif ($form->isSubmitted())
        {
            $this->addFlash('error', 'Passwords do not match or are empty.');
        }

        // Logic
        return $this->render(
            'security/change_password.html.twig',
            array('form' => $form->createView())
        );
    }
}
