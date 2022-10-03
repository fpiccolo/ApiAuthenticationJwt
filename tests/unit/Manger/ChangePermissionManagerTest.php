<?php
declare(strict_types=1);

namespace tests\unit\Manager;

use App\DTO\Input\ChangePasswordInput;
use App\DTO\Input\ChangePermissionInput;
use App\Entity\User;
use App\Enum\Roles;
use App\Exception\UserNotFoundException;
use App\Manager\ChangePasswordManager;
use App\Manager\ChangePermissionManager;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChangePermissionManagerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|UserRepository $userRepository;
    private ObjectProphecy|UserTokenRepository $userTokenRepository;
    private ChangePermissionManager $changePermissionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = self::prophesize(UserRepository::class);
        $this->userTokenRepository = self::prophesize(UserTokenRepository::class);

        $this->changePermissionManager = new ChangePermissionManager(
            $this->userRepository->reveal(),
            $this->userTokenRepository->reveal()
        );
    }

    public function testChangePermissionThrowUserNotFoundException(): void
    {
        $dto = new ChangePermissionInput();
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

        $this->changePermissionManager->changePermissions($dto);
    }

    public function testChangePermissionSuccess(): void
    {
        $dto = new ChangePermissionInput();
        $dto->email = "email@test.com";
        $dto->roles = [
            Roles::ROLE_ADMIN
        ];

        /** @var ObjectProphecy|User $user */
        $user = self::prophesize(User::class);

        $this->userRepository
            ->findOneBy([
                "email" => $dto->email
            ])
            ->willReturn($user->reveal())
            ->shouldBeCalledOnce();

        $user
            ->setRoles($dto->roles)
            ->shouldBeCalledOnce();

        $this->userRepository
            ->save($user, true)
            ->shouldBeCalledOnce();

        $this->userTokenRepository
            ->invalidateUserTokens($user)
            ->shouldBeCalledOnce();

        $this->changePermissionManager->changePermissions($dto);
    }
}