<?php
declare(strict_types=1);

namespace App\Manager;

use App\DTO\Input\ChangePasswordInput;
use App\Exception\UserNotFoundException;
use App\Repository\UserTokenRepository;
use App\Repository\UserRepository;
use App\Service\PasswordEncrypt;

class ChangePasswordManager
{
    private UserRepository $userRepository;
    private UserTokenRepository $userTokenRepository;
    private PasswordEncrypt $passwordEncrypt;

    public function __construct(
        UserRepository      $userRepository,
        UserTokenRepository $userTokenRepository,
        PasswordEncrypt $passwordEncrypt
    )
    {
        $this->userRepository = $userRepository;
        $this->userTokenRepository = $userTokenRepository;
        $this->passwordEncrypt = $passwordEncrypt;
    }

    public function changePassword(ChangePasswordInput $changePasswordInput): void
    {
        $user = $this->userRepository->findOneBy(['email' => $changePasswordInput->email]);

        if(null === $user){
            throw new UserNotFoundException($changePasswordInput->email);
        }

        $user->setPassword(
            $this->passwordEncrypt->encrypt($changePasswordInput->password)
        );

        $this->userRepository->save($user, true);

        $this->userTokenRepository->invalidateUserTokens($user);
    }
}