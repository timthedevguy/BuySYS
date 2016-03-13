<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OperationController extends Controller
{
    /**
     * @Route("/operations/mining", name="operations_mining")
     */
    public function indexAction(Request $request)
    {
        return $this->render('operations/index.html.twig', array(
            'page_name' => 'Mining Op', 'sub_text' => 'Operations', 'mode' => 'USER'
        ));
    }
}
