<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserPreferencesEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LossesController extends Controller
{
    /**
     * @Route("/mylosses", name="losses")
     */
    public function indexAction(Request $request)
    {	
        return $this->render('losses/losses.html.twig', [
            'base_dir' => 'test',
			'page_name' => 'My Recent Losses',
			'sub_text' => 'Order and reship!',
			'userCharacterName' => $this->getUser()->getUsername(),
			'history' => [],
			'hiddenTypes' => array(670)]);
    }
	
	/**
     * @Route("/mylosses/losses", name="ajax_mylosses_losses")
     */
    public function ajax_GetLosses()
    {
		$losses = json_decode(file_get_contents("https://zkillboard.com/api/character/".$this->getUser()->getCharacterId()."/losses/json/"), true);
		$filteredLosses = [];
		
		foreach($losses as $loss)
			if(!in_array($loss['victim']['shipTypeID'], [670, 26890, 26888, 12198, 23055]))
				$filteredLosses []= $loss;
			
		unset($losses);
		
        return $this->render('losses/mylosses.html.twig', array('losses' => $filteredLosses));
    }

}
