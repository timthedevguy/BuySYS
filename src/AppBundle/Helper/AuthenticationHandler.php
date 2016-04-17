<?php
namespace AppBundle\Helper;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $token->getUser()->setLastLogin(new \DateTime());
        $this->container->get('doctrine')->getEntityManager()->flush();

        return new RedirectResponse($this->container->get('router')->generate('homepage'));
    }
}
