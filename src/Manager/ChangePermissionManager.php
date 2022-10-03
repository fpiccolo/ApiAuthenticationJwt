<?php
declare(strict_types=1);

namespace App\Manager;

use App\DTO\Input\ChangePasswordInput;
use App\DTO\Input\ChangePermissionInput;
use App\Exception\UserNotFoundException;
use App\Repository\UserTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ChangePermissionManager
{
    private UserRepository $userRepository;
    private UserTokenRepository $userJwtRepository;

    public function __construct(
        UserRepository         $userRepository,
        UserTokenRepository    $userJwtRepository,
    )
    {
        $this->userRepository = $userRepository;
        $this->userJwtRepository = $userJwtRepository;
    }

    public function changePermissions(ChangePermissionInput $changePermissionInput): void
    {
        $user = $this->userRepository->findOneBy(['email' => $changePermissionInput->email]);

        if(null === $user){
            throw new UserNotFoundException($changePermissionInput->email);
        }

        $user->setRoles($changePermissionInput->roles);

        $this->userRepository->save($user, true);

        $this->userJwtRepository->invalidateUserTokens($user);
    }
}