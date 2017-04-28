<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 4/28/17
 * Time: 1:44 PM
 */

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class PreferencesControlloer extends Controller
{



    /**
     * @Route("/updatepreferences", name="update_preferences")
     */
    public function passwordResetConfirmAction(Request $request)
    {
        if($request->getMethod() == 'POST') {

            $themePreference = $request->request->get('email');
        }

        return $this->redirectToRoute('confirm_password_reset');
    }
}