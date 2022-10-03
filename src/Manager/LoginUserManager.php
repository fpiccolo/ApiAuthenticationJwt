<?php
declare(strict_types=1);

namespace App\Manager;

use App\DTO\Input\LoginInput;
use App\DTO\Output\LoginOutput;
use App\Entity\UserToken;
use App\Exception\InvalidCredentialException;
use App\Repository\UserTokenRepository;
use App\Repository\UserRepository;
use App\Service\JwtService;
use App\Service\PasswordEncrypt;

class LoginUserManager
{
    private UserRepository $userRepository;
    private JwtService $jwtService;
    private UserTokenRepository $userJwtRepository;
    private PasswordEncrypt $passwordEncrypt;

    public function __construct(
        UserRepository $userRepository,
        JwtService $jwtService,
        UserTokenRepository $userJwtRepository,
        PasswordEncrypt $passwordEncrypt
    )
    {
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
        $this->userJwtRepository = $userJwtRepository;
        $this->passwordEncrypt = $passwordEncrypt;
    }

    public function login(LoginInput $loginInput): LoginOutput
    {
        $user = $this->userRepository->findOneBy([
            "email" => $loginInput->email,
            "password" => $this->passwordEncrypt->encrypt($loginInput->password)
        ]);

        if(null === $user){
            throw new InvalidCredentialException();
        }

        $token = $this->jwtService->generateToken($user);

        $userToken = new UserToken();
        $userToken->setToken($token)
            ->setCreatedAt($this->jwtService->getTokenCreatedAt($token))
            ->setExpireAt($this->jwtService->getTokenExpireAt($token))
            ->setUser($user);

        $this->userJwtRepository->save($userToken, true);

        return new LoginOutput($token);
    }
}