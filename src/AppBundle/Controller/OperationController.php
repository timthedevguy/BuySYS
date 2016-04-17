<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OperationController extends Controller
{
    /**
     * @Route("/operations", name="upcoming_fleet_ops")
     */
    public function indexAction(Request $request)
    {
        return $this->render('operations/index.html.twig', array(
            'page_name' => 'Upcoming Fleet Ops', 'sub_text' => 'Alliance/Corp sponsered fleet operations', 'mode' => 'USER'
        ));
    }
}
