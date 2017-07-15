<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserPreferencesEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\ESI\ESI;

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
    public function ajax_GetLosses(Request $request)
    {
		$ESI = new ESI($this->get('eve_sso'), $request->getSession());
		$losses = $ESI->getCharactersCharacterIdKillmailsRecent(["characterId" => $this->getUser()->getCharacterId(), 'maxCount' => 50]);
		
		if($losses) {
			
			$fullLosses = [];
			foreach($losses as $loss) {
				
				$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
				$data = json_decode(file_get_contents('https://esi.tech.ccp.is/latest/killmails/'.$loss->getKillmailId().'/'.$loss->getKillmailHash().'/',false,$context), true);
				if(isset($data['killmail_time']) && $data['victim']['character_id'] == $this->getUser()->getCharacterId()) {
					$fullLosses []= $data;
				}
				
			}
		
			$filteredLosses = [];
			foreach($fullLosses as $loss) {
				
				if(in_array($loss['victim']['ship_type_id'], [670, 26890, 26888, 12198, 23055])) {
					continue;
				}
				$loss['srp_status'] = null;
				if(($transaction = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findByOrderID("SRP".$loss['killmail_id'])) !== null) {
					$loss['srp_status'] = $transaction->getStatus();
				}
				$filteredLosses []= $loss;
				
			}
			
			$losses = $filteredLosses;
		}
		
        return $this->render('losses/mylosses.html.twig', array('losses' => array_slice($filteredLosses, 0, 10)));
    }

}
