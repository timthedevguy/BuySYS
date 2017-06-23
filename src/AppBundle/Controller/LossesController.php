<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserPreferencesEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Model\BuyBackModel;
use AppBundle\Form\BuyBackForm;
use AppBundle\Model\TransactionSummaryModel;
use AppBundle\ESI\ESI;

class LossesController extends Controller
{
    /**
     * @Route("/mylosses", name="losses")
     */
    public function indexAction(Request $request)
    {
		$losses = json_decode(file_get_contents("https://zkillboard.com/api/character/".$this->getUser()->getCharacterId()."/losses/json/"), true);
		$filteredLosses = [];
		
		foreach($losses as $loss)
			if(!in_array($loss['victim']['shipTypeID'], [670, 26888, 12198]))
				$filteredLosses []= $loss;
			
		unset($losses);
		
		
        return $this->render('losses/losses.html.twig', [
            'base_dir' => 'test',
			'page_name' => 'My Recent Losses',
			'sub_text' => 'Order and reship!',
			'userCharacterName' => $this->getUser()->getUsername(),
			'losses' => $filteredLosses,
			'hiddenTypes' => array(670)]);
    }

}
