<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pheal\Pheal;
use Pheal\Core\Config;
use zkillboard\crestsso;
use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Form\RegisterUserForm;
use AppBundle\Entity\UserEntity;

use AppBundle\Form\ChangePasswordForm;
use AppBundle\Form\ConfirmPasswordResetForm;
use AppBundle\Model\ChangePasswordModel;
use AppBundle\Model\ResetPasswordModel;
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
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request)
    {
        $clientID = $this->get('helper')->getSetting('sso_clientid');
        $secretKey = $this->get('helper')->getSetting('sso_secretkey');
        $callbackURL = $this->generateUrl('register_sso_callback');
        $baseUrl = "https://login.eveonline.com/oauth/authorize/?response_type=code&redirect_uri=";

        $url = $baseUrl . $this->get('request')->getSchemeAndHttpHost() . $callbackURL . '&client_id=' . $clientID;

        //$sso = new crestsso\CrestSSO($clientID, $secretKey, $callbackURL, $scopes);
        //$loginURL = $sso->getLoginURL($session);

        return $this->render('security/register.html.twig', array('login_url' => $url));
    }

    /**
     * @Route("/register/sso/callback", name="register_sso_callback")
     */
    public function registerSSOCallbackAction(Request $request)
    {
        dump($request);
        return $this->render(':security:register.html.twig', array());
    }

    /**
     * @Route("/register-old", name="register-old")
     */
    public function registerOldAction(Request $request)
    {
        $user = new UserEntity();
        $form = $this->createForm(RegisterUserForm::class, $user);

        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted())
        {
            // Setup new PhealNG access
            $pheal = new Pheal($user->getApiKey(), $user->getApiCode());
            $hasMatch = false;

            try
            {
                $result = $pheal->Characters();
                // Check results to see if we find a match
                foreach($result->characters as $character)
                {
                    if(strtolower($character->name) == strtolower($user->getUsername()))
                    {
                        $hasMatch = true;
                        $user->setCharacterId($character->characterID);
                    }
                }

                if($hasMatch == false)
                {
                    $this->addFlash('error', "Can't find " . $user->getUsername() . " with supplied API information.");
                    return $this->redirectToRoute('register');
                }

            } catch (\Pheal\Exceptions\PhealException $e) {

                $this->addFlash('error', 'Something has gone horribly wrong, please contact Lorvulk Munba in game...' + $e->getMessage());
                return $this->redirectToRoute('register');
            }

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRole("ROLE_MEMBER");
            $user->setIsActive(true);
            $user->setLastLogin(new \DateTime());

            // 4) save the User!
            $em = $this->getDoctrine()->getEntityManager('default');
            $em->persist($user);
            $em->flush();

            // ... do any other work - like send them an email, etc
            // maybe set a "flash" success message for the user
            $this->addFlash('success','Created '.$user->getUsername().', login below to conitnue.  You can now delete the API key used to register if you wish.');

            return $this->redirectToRoute('login_route');

        } elseif ($form->isSubmitted()) {

            $this->addFlash('error', 'Please correct the highlighted errors.');
        }

        return $this->render(
            'security/register-old.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/change_password", name="change_password")
     */
    public function changePasswordAction(Request $request)
    {
        // 1) build the form
        //$user = new User();
        $data = new ChangePasswordModel();
        $form = $this->createForm(ChangePasswordForm::class, $data);

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

    /**
     * @Route("/resetpassword", name="reset_password")
     */
    public function passwordResetAction(Request $request)
    {
        if($request->getMethod() == 'POST') {

            $email = $request->request->get('email');
            $reset_code = md5(uniqid());
            $user = $this->getDoctrine()->getRepository('AppBundle:UserEntity','default')->findOneByEmail($email);

            if($user != null)
            {
                $user->setResetCode($reset_code);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $message = \Swift_Message::newInstance()
                ->setSubject('OgSYS Password Reset Request')
                ->setFrom('amsys@alliedindustries-eve.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                        'security/passwordreset.html.twig',
                        array('reset_code' => $reset_code)
                    ),
                    'text/html'
                );
                $this->get('mailer')->send($message);

                $this->addFlash('success','Email Sent.  Please check your email for further instructions.');
            }
            else
            {
                $this->addFlash('error','User does not exist.  Please try again');
                return $this->redirectToRoute('reset_password');
            }

            return $this->redirectToRoute('confirm_password_reset');
        }
        /*$message = \Swift_Message::newInstance()
        ->setSubject('AmSYS Password Reset Request')
        ->setFrom('amsys@alliedindustries-eve.com')
        ->setTo('binary.god@gmail.com')
        ->setBody(
            $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                'registration/passwordreset.html.twig',
                array('name' => 'tim')
            ),
            'text/html'
        );
        $this->get('mailer')->send($message);*/

        return $this->render('security/reset_password.html.twig', array());
    }

    /**
     * @Route("/confirmpasswordreset", name="confirm_password_reset")
     */
    public function passwordResetConfirmAction(Request $request)
    {
        $data = new ResetPasswordModel();
        $form = $this->createForm(ConfirmPasswordResetForm::class, $data);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted())
        {
            $user = $this->getDoctrine()->getRepository('AppBundle:UserEntity', 'default')->findOneByResetCode($data->getResetCode());

            if($user != null)
            {
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $data->getNewPassword());
                $user->setPassword($password);
                $user->setResetCode("");

                // 4) save the User!
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash("success", 'Password set successfully!');
                return $this->redirectToRoute('login_route');
            }
            else
            {
                $this->addFlash('error', 'Reset Code not found!');
                return $this->redirectToRoute('confirm_password_reset');
            }
        }
        elseif ($form->isSubmitted())
        {
            $this->addFlash('error', 'Could not set new password');
        }

        // Logic
        return $this->render(
            'security/reset_confirm.html.twig',
            array('form' => $form->createView())
        );
    }
}
