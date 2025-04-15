<?php

    namespace App\Security;

    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
    use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
    use Symfony\Component\Security\Http\Util\TargetPathTrait;

    class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
    {
        use TargetPathTrait;

        private UrlGeneratorInterface $urlGenerator;

        public function __construct(UrlGeneratorInterface $urlGenerator)
        {
             $this->urlGenerator = $urlGenerator;
        }

        public function authenticate(Request $request): Passport
        {
            // Use _username and _password to match the login form
            $username = $request->request->get('_username', '');
            $password = $request->request->get('_password', '');
            $csrfToken = $request->request->get('_csrf_token');

            return new Passport(
                new UserBadge($username),
                new PasswordCredentials($password),
                [
                    new CsrfTokenBadge('authenticate', $csrfToken)
                ]
            );
        }

        public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
        {
             $user = $token->getUser();
             if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                  return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
             }
             return new RedirectResponse($this->urlGenerator->generate('home'));
        }

        protected function getLoginUrl(Request $request): string
        {
             return $this->urlGenerator->generate('app_login');
        }
    }