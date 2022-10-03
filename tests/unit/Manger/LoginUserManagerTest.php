<?php
declare(strict_types=1);

namespace tests\unit\Manager;

use App\DTO\Input\LoginInput;
use App\Entity\User;
use App\Entity\UserToken;
use App\Exception\InvalidCredentialException;
use App\Manager\LoginUserManager;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use App\Service\JwtService;
use App\Service\PasswordEncrypt;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class LoginUserManagerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|UserRepository $userRepository;
    private ObjectProphecy|JwtService $jwtService;
    private ObjectProphecy|UserTokenRepository $userTokenRepository;
    private LoginUserManager $loginUserManager;
    private ObjectProphecy|PasswordEncrypt $passwordEncrypt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = self::prophesize(UserRepository::class);
        $this->jwtService = self::prophesize(JwtService::class);
        $this->userTokenRepository = self::prophesize(UserTokenRepository::class);
        $this->passwordEncrypt = self::prophesize(PasswordEncrypt::class);

        $this->loginUserManager = new LoginUserManager(
            $this->userRepository->reveal(),
            $this->jwtService->reveal(),
            $this->userTokenRepository->reveal(),
            $this->passwordEncrypt->reveal()
        );
    }

    public function testLoginThrowInvalidCredentialException(): void
    {
        self::expectExceptionObject(new InvalidCredentialException());

        $loginInput = new LoginInput();
        $loginInput->email = 'email@email.it';
        $loginInput->password = 'password';
        $passwordEncrypted = 'crypt';

        $this->passwordEncrypt
            ->encrypt($loginInput->password)
            ->willReturn($passwordEncrypted)
            ->shouldBeCalledOnce();

        $this->userRepository
            ->findOneBy([
                "email" => $loginInput->email,
                "password" => $passwordEncrypted,
            ])
            ->willReturn(null)
            ->shouldBeCalledOnce();



        $this->jwtService
            ->generateToken(Argument::any())
            ->shouldNotBeCalled();

        $this->userTokenRepository
            ->save(Argument::any())
            ->shouldNotBeCalled();

        $this->loginUserManager->login($loginInput);
    }

    public function testLoginReturnToken(): void
    {
        $loginInput = new LoginInput();
        $loginInput->email = 'email@email.it';
        $loginInput->password = 'password';
        $passwordEncrypted = 'crypt';
        $token = 'token';

        $createdAt = Chronos::now();
        $expiredAt = Chronos::now()->addMinute(60);

        $user = self::prophesize(User::class);

        $this->passwordEncrypt
            ->encrypt($loginInput->password)
            ->willReturn($passwordEncrypted)
            ->shouldBeCalledOnce();

        $this->userRepository
            ->findOneBy([
                "email" => $loginInput->email,
                "password" => $passwordEncrypted,
            ])
            ->willReturn($user)
            ->shouldBeCalledOnce();

        $this->jwtService
            ->generateToken($user)
            ->willReturn($token)
            ->shouldBeCalledOnce();

        $this->jwtService
            ->getTokenCreatedAt($token)
            ->willReturn($createdAt)
            ->shouldBeCalledOnce();

        $this->jwtService
            ->getTokenExpireAt($token)
            ->willReturn($expiredAt)
            ->shouldBeCalledOnce();

        $this->userTokenRepository
            ->save(
                Argument::that(function (UserToken $userToken) use ($user, $createdAt, $expiredAt): bool {
                    return $user->reveal() === $userToken->getUser()
                        && $createdAt === $userToken->getCreatedAt()
                        && $expiredAt === $userToken->getExpireAt();
                }),
                true
            )
            ->shouldBeCalledOnce();

        $loginOutput = $this->loginUserManager->login($loginInput);

        self::assertEquals($token, $loginOutput->token);
    }
}