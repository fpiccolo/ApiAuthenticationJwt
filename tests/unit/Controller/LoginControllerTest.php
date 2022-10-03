<?php
declare(strict_types=1);

namespace tests\unit\Controller;

use App\Controller\LoginController;
use App\DTO\Input\LoginInput;
use App\DTO\Output\LoginOutput;
use App\Manager\LoginUserManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class LoginControllerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|SerializerInterface $serializer;
    private ObjectProphecy|LoginUserManager $loginUserManager;
    private LoginController $loginController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = self::prophesize(SerializerInterface::class);
        $this->loginUserManager = self::prophesize(LoginUserManager::class);

        $this->loginController = new LoginController(
            $this->serializer->reveal(),
            $this->loginUserManager->reveal(),
        );

        $this->loginController->setContainer(self::createMock(ContainerInterface::class));
    }

    public function testLogin(): void
    {
        /** @var ObjectProphecy|Request $request */
        $request = self::prophesize(Request::class);
        $content = 'content';
        $loginInput = new LoginInput();
        $loginOutput = new LoginOutput('token');

        $request
            ->getContent()
            ->willReturn($content)
            ->shouldBeCalledOnce();

        $this->serializer
            ->deserialize($content, LoginInput::class, 'json')
            ->willReturn($loginInput)
            ->shouldBeCalledOnce();

        $this->loginUserManager
            ->login($loginInput)
            ->willReturn($loginOutput)
            ->shouldBeCalledOnce();

        $response = $this->loginController->login($request->reveal());

        self::assertEquals(new JsonResponse($loginOutput), $response);
    }
}