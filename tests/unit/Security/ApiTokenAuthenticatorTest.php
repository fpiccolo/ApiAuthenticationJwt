<?php
declare(strict_types=1);

namespace tests\unit\Security;

use App\Entity\User;
use App\Entity\UserToken;
use App\Exception\NoApiTokenProvidedException;
use App\Exception\TokenNotFoundException;
use App\Exception\TokenNotValidException;
use App\Repository\UserTokenRepository;
use App\Security\ApiTokenAuthenticator;
use App\Service\JwtService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticatorTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|JwtService $jwtService;
    private ObjectProphecy|UserTokenRepository $userTokenRepository;
    private ApiTokenAuthenticator $apiTokenAuthenticator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtService = self::prophesize(JwtService::class);
        $this->userTokenRepository = self::prophesize(UserTokenRepository::class);

        $this->apiTokenAuthenticator = new ApiTokenAuthenticator(
            $this->jwtService->reveal(),
            $this->userTokenRepository->reveal()
        );
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $path, bool $expectedResult): void
    {
        /** @var ObjectProphecy|Request $request */
        $request = self::prophesize(Request::class);

        $request->getPathInfo()
            ->willReturn($path)
            ->shouldBeCalledOnce();

        $result = $this->apiTokenAuthenticator->supports($request->reveal());

        self::assertEquals($expectedResult, $result);
    }

    public function supportsDataProvider(): iterable
    {
        yield ['/api/login', false];
        yield ['/api', true];
        yield ['/api/other', true];
    }

    /**
     * @dataProvider authenticateThrowNoApiTokenProvidedExceptionDataProvider
     */
    public function testAuthenticateThrowNoApiTokenProvidedException(?string $notValidToken): void
    {
        self::expectExceptionObject(new NoApiTokenProvidedException());

        $request = new Request();
        $request->headers = self::createMock(HeaderBag::class);

        $request
            ->headers
            ->expects(self::once())
            ->method('get')
            ->with('Authorization', '')
            ->willReturn($notValidToken);

        $this->apiTokenAuthenticator->authenticate($request);

    }

    public function authenticateThrowNoApiTokenProvidedExceptionDataProvider(): iterable
    {
        yield [null];
        yield [""];
        yield ["notvalid"];
        yield ["prova notvalid"];
        yield ["Bearer "];
    }

    public function testAuthenticateThrowTokenNotValidException(): void
    {
        $errorMessage = 'error message';
        $token = 'token';
        $authorization = 'Bearer '.$token;
        self::expectExceptionObject(new TokenNotValidException($errorMessage));

        $request = new Request();
        $request->headers = self::createMock(HeaderBag::class);

        $request
            ->headers
            ->expects(self::once())
            ->method('get')
            ->with('Authorization', '')
            ->willReturn($authorization);

        $this->jwtService
            ->validateToken($token)
            ->willThrow(new \Exception($errorMessage))
            ->shouldBeCalledOnce();

        $this->apiTokenAuthenticator->authenticate($request);
    }

    public function testAuthenticateThrowTokenNotFoundException(): void
    {
        $token = 'token';
        $authorization = 'Bearer '.$token;
        self::expectExceptionObject(new TokenNotFoundException());

        $request = new Request();
        $request->headers = self::createMock(HeaderBag::class);

        $request
            ->headers
            ->expects(self::once())
            ->method('get')
            ->with('Authorization', '')
            ->willReturn($authorization);

        $this->jwtService
            ->validateToken($token)
            ->shouldBeCalledOnce();

        $this->userTokenRepository
            ->findOneBy(['token' => $token, 'invalidatedAt' => null])
            ->willReturn(null)
            ->shouldBeCalledOnce();

        $this->apiTokenAuthenticator->authenticate($request);
    }

    public function testAuthenticate(): void
    {
        $token = 'token';
        $authorization = 'Bearer '.$token;

        $request = new Request();
        $request->headers = self::createMock(HeaderBag::class);

        $email = 'email';

        $userToken = self::prophesize(UserToken::class);
        $user = self::prophesize(User::class);

        $request
            ->headers
            ->expects(self::once())
            ->method('get')
            ->with('Authorization', '')
            ->willReturn($authorization);

        $this->jwtService
            ->validateToken($token)
            ->shouldBeCalledOnce();



        $this->userTokenRepository
            ->findOneBy(['token' => $token, 'invalidatedAt' => null])
            ->willReturn($userToken)
            ->shouldBeCalledOnce();

        $userToken
            ->getUser()
            ->willReturn($user)
            ->shouldBeCalledOnce();

        $user
            ->getEmail()
            ->willReturn($email)
            ->shouldBeCalledOnce();

        $result = $this->apiTokenAuthenticator->authenticate($request);

        $expectedResult = new SelfValidatingPassport(new UserBadge($email));

        self::assertEquals($expectedResult, $result);
    }

    public function testOnAuthenticationSuccess(): void
    {
        $request = self::createMock(Request::class);
        $token = self::createMock(TokenInterface::class);

        self::assertNull($this->apiTokenAuthenticator->onAuthenticationSuccess($request, $token, ''));
    }

    public function testOnAuthenticationFailure(): void
    {
        $request = self::createMock(Request::class);
        $authenticationException = self::prophesize(AuthenticationException::class);
        $key = 'key';
        $message = 'message';


        $authenticationException
            ->getMessageKey()
            ->willReturn($key)
            ->shouldBeCalledOnce();

        $authenticationException
            ->getMessageData()
            ->willReturn([$message])
            ->shouldBeCalledOnce();

        self::assertEquals(
            new JsonResponse([
                'message' => strtr($key, [$message])
            ], Response::HTTP_UNAUTHORIZED),
            $this->apiTokenAuthenticator->onAuthenticationFailure($request, $authenticationException->reveal(), '')
        );
    }
}