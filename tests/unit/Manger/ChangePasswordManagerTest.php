<?php
declare(strict_types=1);

namespace tests\unit\Manager;

use App\DTO\Input\ChangePasswordInput;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Manager\ChangePasswordManager;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use App\Service\PasswordEncrypt;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChangePasswordManagerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|UserRepository $userRepository;
    private ObjectProphecy|UserTokenRepository $userTokenRepository;
    private ChangePasswordManager $changePasswordManager;
    private ObjectProphecy|PasswordEncrypt $passwordEncrypt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = self::prophesize(UserRepository::class);
        $this->userTokenRepository = self::prophesize(UserTokenRepository::class);
        $this->passwordEncrypt = self::prophesize(PasswordEncrypt::class);

        $this->changePasswordManager = new ChangePasswordManager(
            $this->userRepository->reveal(),
            $this->userTokenRepository->reveal(),
            $this->passwordEncrypt->reveal()
        );
    }

    public function testChangePasswordThrowUserNotFoundException(): void
    {
        $dto = new ChangePasswordInput();
        $dto->email = "email@test.com";


        self::expectExceptionObject(new UserNotFoundException($dto->email));

        $this->userRepository
            ->findOneBy([
                "email" => $dto->email
            ])
            ->willReturn(null)
            ->shouldBeCalledOnce();

        $this->userRepository
            ->save(Argument::any())
            ->shouldNotBeCalled();

        $this->userTokenRepository
            ->invalidateUserTokens(Argument::any())
            ->shouldNotBeCalled();

        $this->changePasswordManager->changePassword($dto);
    }

    public function testChangePasswordSuccess(): void
    {
        $dto = new ChangePasswordInput();
        $dto->email = "email@test.com";
        $dto->password = "password";
        $passwordEncrypted = 'crypt';

        /** @var ObjectProphecy|User $user */
        $user = self::prophesize(User::class);

        $this->userRepository
            ->findOneBy([
                "email" => $dto->email
            ])
            ->willReturn($user->reveal())
            ->shouldBeCalledOnce();

        $this->passwordEncrypt
            ->encrypt($dto->password)
            ->willReturn($passwordEncrypted)
            ->shouldBeCalledOnce();

        $user
            ->setPassword($passwordEncrypted)
            ->shouldBeCalledOnce();

        $this->userRepository
            ->save($user, true)
            ->shouldBeCalledOnce();

        $this->userTokenRepository
            ->invalidateUserTokens($user)
            ->shouldBeCalledOnce();

        $this->changePasswordManager->changePassword($dto);
    }
}