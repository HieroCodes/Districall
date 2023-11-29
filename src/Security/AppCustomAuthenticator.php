<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;




class AppCustomAuthenticator extends AbstractAuthenticator
{
    private $urlGenerator;
    private $userProvider;

    public function __construct(UrlGeneratorInterface $urlGenerator, UserProviderInterface $userProvider)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userProvider = $userProvider;
    }

    public function supports(Request $request): ?bool
     {
        //vérifier si le user est sur la page de connexion et si la méthode est POST
        return $request->attributes->get('_route') === 'app_login' && $request->isMethod('POST');
     }


     public function authenticate(Request $request): Passport
     {
         $email = $request->request->get('email');
         $password = $request->request->get('password');
     
         return new SelfValidatingPassport(
             new UserBadge($email, function($userIdentifier) {
                 return $this->userProvider->loadUserByIdentifier($userIdentifier);
             }),
             [new PasswordCredentials($password)]
         );
     }
     

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('dashboard'));
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add('error', 'Email ou mot de passe incorrect');
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

}
