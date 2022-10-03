<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\Roles;
use App\Service\PasswordEncrypt;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class AppFixtures extends Fixture
{
    private PasswordEncrypt $passwordEncrypt;

    public function __construct(
        PasswordEncrypt $passwordEncrypt
    )
    {
        $this->passwordEncrypt = $passwordEncrypt;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User(
            'Francesco',
            'Piccolo',
            'fra@gmail.com',
            $this->passwordEncrypt->encrypt('password'),
            [
                Roles::ROLE_ADMIN,
                Roles::ROLE_USER,
            ]
        );

        $manager->persist($user);

        $user = new User(
            'Luca',
            'Rossi',
            'luca@gmail.com',
            $this->passwordEncrypt->encrypt('password'),
            [
                Roles::ROLE_USER,
            ]
        );

        $manager->persist($user);

        $manager->flush();
    }
}
