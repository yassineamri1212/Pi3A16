<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
#[Route('/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
if ($this->getUser()) {
return $this->redirectToRoute('admin_dashboard');
}

return $this->render('security/login.html.twig', [
'last_username' => $authenticationUtils->getLastUsername(),
'error' => $authenticationUtils->getLastAuthenticationError()
]);
}

#[Route('/logout', name: 'app_logout')]
public function logout(): void
{
// Controller can be blank - it will be intercepted by the logout key on your firewall
}
}