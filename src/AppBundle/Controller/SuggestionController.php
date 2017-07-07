<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\SuggestionForm;
use AppBundle\Model\SuggestionModel;
use AppBundle\ESI\ESI;

class SuggestionController extends Controller
{
    /**
     * @Route("/suggestion", name="guest_suggestion")
     */
    public function indexAction(Request $request)
    {
        $sm = new SuggestionModel();
        $form = $this->createForm(SuggestionForm::class, $sm);
        $user = $this->getUser();

        // Handle Form
        $form->handleRequest($request);

        // If form is valid
        if ($form->isValid() && $form->isSubmitted())
        {
			$ESI = new ESI($this->get('eve_sso'), $request->getSession());
			$mail = new \nullx27\ESI\Models\PostCharactersCharacterIdMailMail();
			$recipients = [
				new \nullx27\ESI\Models\PostCharactersCharacterIdMailRecipient(['recipientId' => 95914159, 'recipientType' => 'character']),
				new \nullx27\ESI\Models\PostCharactersCharacterIdMailRecipient(['recipientId' => 1066295668, 'recipientType' => 'character']),
				new \nullx27\ESI\Models\PostCharactersCharacterIdMailRecipient(['recipientId' => 95878956, 'recipientType' => 'character'])
			];
			$mail->setBody($sm->getMessage());
			$mail->setRecipients($recipients);
			$mail->setSubject('Suggestion from Website');
			$sendMail = $ESI->postCharactersCharacterIdMail(["characterId" => $this->getUser()->getCharacterId(), 'mail' => $mail]);
			
			if(is_numeric($sendMail))
				$this->addFlash('success', 'EVE Mail Sent. Thank you!');
			else
				$this->addFlash('error', 'We were unable to send this EVE Mail. '.print_r($sendMail, true));
        }

        return $this->render('suggestion/index.html.twig', array('form' => $form->createView() ));
    }
}
