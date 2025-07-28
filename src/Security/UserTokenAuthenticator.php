<?php

namespace App\Security;

use App\Repository\UserTokenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class UserTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserTokenRepository $userTokenRepository
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') && 
               str_starts_with($request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization');
        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        if (empty($token)) {
            throw new CustomUserMessageAuthenticationException('No token provided');
        }

        $userToken = $this->userTokenRepository->findValidToken($token);
        
        if (!$userToken) {
            throw new CustomUserMessageAuthenticationException('Invalid or expired token');
        }

        return new SelfValidatingPassport(
            new UserBadge($userToken->user->getUserIdentifier(), function() use ($userToken) {
                return $userToken->user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => 'Authentication failed',
            'message' => $exception->getMessageKey()
        ], Response::HTTP_UNAUTHORIZED);
    }
}