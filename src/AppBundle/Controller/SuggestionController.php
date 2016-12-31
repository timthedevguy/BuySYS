<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\SuggestionForm;
use AppBundle\Model\SuggestionModel;

class SuggestionController extends Controller
{
    /**
     * @Route("/guest/suggestion", name="guest_suggestion")
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
            $message = \Swift_Message::newInstance()
            ->setSubject('Suggestion from Website')
            ->setFrom('buyback.omnigalactic@gmail.com')
            ->setTo(array('binary.god@gmail.com', 'Aarondorn2@gmail.com'))
            ->setBody(
                $this->renderView(
                    // app/Resources/views/Emails/registration.html.twig
                    'suggestion/message.html.twig',
                    array('message' => $sm->getMessage(), 'username' => $user->GetUsername())
                ),
                'text/html'
            );
            $this->get('mailer')->send($message);

            $this->addFlash('success','Email Sent.  Thank you!!!!');
        }

        return $this->render('suggestion/index.html.twig', array('form' => $form->createView() ));
    }
}
