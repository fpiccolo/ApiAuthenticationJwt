<?php
declare(strict_types=1);

namespace tests\unit\Service;

use App\Entity\User;
use App\Enum\Roles;
use App\Service\JwtService;
use Cake\Chronos\Chronos;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class JwtServiceTest extends TestCase
{
    use ProphecyTrait;

    private string $key = 'key';
    private string $algorithm = 'HS256';
    private JwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtService = new JwtService(
            $this->key,
            $this->algorithm
        );
    }

    public function testGenerateToken(): void
    {
        Chronos::setTestNow(Chronos::now());

        $user = new User(
            'Luca',
            'Rossi',
            'test@test.it',
            'password',
            [Roles::ROLE_ADMIN]
        );

        $expectedToken = JWT::encode(
            [
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'exp' => Chronos::now()->addMinute(60)->toUnixString(),
                'iat' => Chronos::now()->toUnixString(),
            ],
            $this->key,
            $this->algorithm
        );

        $token = $this->jwtService->generateToken($user);

        $this->assertEquals($expectedToken, $token);

        Chronos::setTestNow();
    }

    public function testGetTokenCreatedAt(): void
    {
        Chronos::setTestNow(Chronos::now());

        $iat =  Chronos::now();

        $token = JWT::encode(
            [
                'firstName' => 'Luca',
                'lastName' => 'Rossi',
                'email' => 'email@email.com',
                'roles' => [Roles::ROLE_ADMIN],
                'exp' => Chronos::now()->addMinute(60)->toUnixString(),
                'iat' => $iat->toUnixString(),
            ],
            $this->key,
            $this->algorithm
        );

        $createdAt = $this->jwtService->getTokenCreatedAt($token);

        $this->assertEquals($iat->toUnixString(), $createdAt->toUnixString());

        Chronos::setTestNow();
    }

    public function testGetTokenExpiredAt(): void
    {
        Chronos::setTestNow(Chronos::now());

        $exp = Chronos::now()->addMinute(60);

        $token = JWT::encode(
            [
                'firstName' => 'Luca',
                'lastName' => 'Rossi',
                'email' => 'email@email.com',
                'roles' => [Roles::ROLE_ADMIN],
                'exp' => $exp->toUnixString(),
                'iat' => Chronos::now()->toUnixString(),
            ],
            $this->key,
            $this->algorithm
        );

        $tokenExpireAt = $this->jwtService->getTokenExpireAt($token);

        $this->assertEquals($exp->toUnixString(), $tokenExpireAt->toUnixString());

        Chronos::setTestNow();
    }

    public function testValidateTokenTrowAndException(): void
    {
        self::expectException(\Exception::class);

        $this->jwtService->validateToken('not valid token');
    }
}