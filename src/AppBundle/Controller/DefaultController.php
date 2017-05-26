<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserPreferencesEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Model\BuyBackModel;
use AppBundle\Form\BuyBackForm;
use AppBundle\Model\TransactionSummaryModel;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $bb = new BuyBackModel();
        $form = $this->createForm(BuyBackForm::class, $bb);

        $form->handleRequest($request);
        $eveCentralOK = $this->get("helper")->getSetting("eveCentralOK");
        $oSales = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAllVisibleByUser($this->getUser());
        $news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAllOrderedByDate();

        $salesSummary = new TransactionSummaryModel($oSales);

        $oPurchases = array(); //coming soon!
        $purchasesSummary = new TransactionSummaryModel($oPurchases);

        //set preferences
        $preferences = $this->getDoctrine()->getRepository('AppBundle:UserPreferencesEntity', 'default')->findOneBy(array('user' => $this->getUser()));

        if($preferences == null) { //user doesn't have preferences yet.  set defaults (and save)
            $preferences = new UserPreferencesEntity();
            $preferences->setUser($this->getUser());

            $em = $this->getDoctrine()->getEntityManager('default');
            $em->persist($preferences);//persist preferences
            $em->flush();
        }
        $this->get('session')->set('userPreferences', $preferences);

        return $this->render('default/index.html.twig', array(
            'base_dir' => 'test', 'page_name' => 'Dashboard', 'sub_text' => 'User Dashboard', 'form' => $form->createView(),
            'oSales' => $oSales, 'salesSummary'=> $salesSummary, 'oPurchases' => $oPurchases, 'purchasesSummary' => $purchasesSummary,
            'news' => $news, 'eveCentralOK' => $eveCentralOK ));
    }

}
