<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
         $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?\Symfony\Component\HttpFoundation\Response
    {
         $user = $token->getUser();
         if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
              $redirectUrl = $this->urlGenerator->generate('admin_dashboard');
         } else {
              $redirectUrl = $this->urlGenerator->generate('home');
         }

         return new RedirectResponse($redirectUrl);
    }
}