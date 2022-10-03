<?php
declare(strict_types=1);

namespace App\Security;

use App\Exception\NoApiTokenProvidedException;
use App\Exception\TokenNotFoundException;
use App\Exception\TokenNotValidException;
use App\Repository\UserTokenRepository;
use App\Service\JwtService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    private JwtService $jwtService;
    private UserTokenRepository $userJwtRepository;

    public function __construct(
        JwtService $jwtService,
        UserTokenRepository $userJwtRepository
    )
    {
        $this->jwtService = $jwtService;
        $this->userJwtRepository = $userJwtRepository;
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): ?bool
    {
        return !($request->getPathInfo() === '/api/login');
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        $authorization = $request->headers->get('Authorization', '');

        if (empty($authorization)){
            throw new NoApiTokenProvidedException();
        }

        $authorization = explode(' ', $authorization);

        if (2 !== count($authorization) || 'Bearer' !== $authorization[0] || empty($authorization[1])) {
            throw new NoApiTokenProvidedException();
        }


        try {
            $this->jwtService->validateToken($authorization[1]);
        }catch (\Throwable $exception){
            throw new TokenNotValidException($exception->getMessage());
        }

        $userToken = $this->userJwtRepository->findOneBy(['token' => $authorization[1], 'invalidatedAt' => null]);

        if(null === $userToken){
            throw new TokenNotFoundException();
        }


        return new SelfValidatingPassport(new UserBadge($userToken->getUser()->getEmail()));
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}