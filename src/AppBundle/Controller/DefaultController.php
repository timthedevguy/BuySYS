<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Model\BuyBackModel;
use AppBundle\Form\BuyBackType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $bb = new BuyBackModel();
        $form = $this->createForm(new BuyBackType(), $bb);

        $form->handleRequest($request);

        // Get count of outstanding transactions
        $transactions = $this->getDoctrine('default')->getRepository('AppBundle\Entity\TransactionEntity');
        $query = $transactions->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.is_complete = 0')
            ->andWhere('t.type = :type')
            ->orderBy('t.created', 'DESC')
            ->setParameter('user', $this->getUser())
            ->setParameter('type', "P")
            ->getQuery();

        //$outstandingSales = count($query->getResult());
        $oSales = $query->getResult();
        $news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAll();


        return $this->render('default/index.html.twig', array(
            'base_dir' => 'test', 'page_name' => 'Dashboard', 'sub_text' => 'User Dashboard', 'form' => $form->createView(), 'mode' => 'USER',
         'oSales' => $oSales, 'news' => $news));
    }
}
