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

        $key = '_security.main.target_path';

        // try to redirect to the last page, or fallback to the homepage
        if ($this->container->get('session')->has($key)) {
          $url = $this->container->get('session')->get($key);
          $this->container->get('session')->remove($key);
        } else {
          $url = $this->container->get('router')->generate('homepage');
        }

        return new RedirectResponse($url);
        //return new RedirectResponse($this->container->get('router')->generate('homepage'));
    }
}
