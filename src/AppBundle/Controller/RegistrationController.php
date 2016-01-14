<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pheal\Pheal;
use Pheal\Core\Config;

use AppBundle\Form\RegisterUserType;
use AppBundle\Entity\UserEntity;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request)
    {
        $user = new UserEntity();
        $form = $this->createForm(new RegisterUserType(), $user);

        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted())
        {
            // Setup new PhealNG access
            $pheal = new Pheal($user->getApiKey(), $user->getApiCode());
            $hasMatch = false;

            try
            {
                $result = $pheal->Characters();
                // Check results to see if we find a match
                foreach($result->characters as $character)
                {
                    if($character->name == $user->getUsername())
                    {
                        $hasMatch = true;
                        $user->setCharacterId($character->characterID);
                    }
                }

                if($hasMatch == false)
                {
                    $this->addFlash('error', "Can't find " . $user->getUsername() . " with supplied API information.");
                    return $this->redirectToRoute('register');
                }

            } catch (\Pheal\Exceptions\PhealException $e) {

                $this->addFlash('error', 'Something has gone horribly wrong, please contact Lorvulk Munba in game');
                return $this->redirectToRoute('register');
            }

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRole("ROLE_USER");
            $user->setIsActive(true);

            // 4) save the User!
            $em = $this->getDoctrine()->getEntityManager('default');
            $em->persist($user);
            $em->flush();

            // ... do any other work - like send them an email, etc
            // maybe set a "flash" success message for the user
            $this->addFlash('success','Created '.$user->getUsername().', login below to conitnue.');

            return $this->redirectToRoute('login_route');

        } elseif ($form->isSubmitted()) {

            $this->addFlash('error', 'Please correct the highlighted errors.');
        }

        return $this->render(
            'registration/register.html.twig',
            array('form' => $form->createView())
        );
    }
}
