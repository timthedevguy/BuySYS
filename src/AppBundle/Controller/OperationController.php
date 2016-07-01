<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\OperationEntity;

class OperationController extends Controller
{
    /**
     * @Route("/operations", name="upcoming_fleet_ops")
     */
    public function indexAction(Request $request)
    {
        $ops = $this->getDoctrine()->getRepository('AppBundle:OperationEntity', 'default')->findAllUpcomingOrderedByDate();

        return $this->render('operations/index.html.twig', array(
            'page_name' => 'Upcoming Fleet Ops', 'sub_text' => 'Alliance/Corp sponsered fleet operations'));
    }

    /**
     * @Route("/operations/create", name="ajax_create_ops")
     */
    public function ajax_CreateAction(Request $request)
    {
        $template = $this->render('operations/create.html.twig');

        return $template;
    }
}
