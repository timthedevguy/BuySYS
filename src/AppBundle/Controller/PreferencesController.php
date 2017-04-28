<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 4/28/17
 * Time: 1:44 PM
 */

namespace AppBundle\Controller;
use AppBundle\Entity\UserPreferencesEntity;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class PreferencesController extends Controller
{



    /**
     * @Route("/updatepreferences", name="update_preferences")
     */
    public function passwordResetConfirmAction(Request $request)
    {
        if($request->getMethod() == 'POST') {

            $preferences = $this->getDoctrine()->getRepository('AppBundle:UserPreferencesEntity', 'default')->findOneBy(array('user' => $this->getUser()));

            $preferences->setDisplayTheme($request->request->get('themeRadios'));

            $this->getDoctrine()->getEntityManager('default')->flush();

            $this->get('session')->set('userPreferences', $preferences);
        }

        return $this->redirectToRoute('homepage');
    }
}