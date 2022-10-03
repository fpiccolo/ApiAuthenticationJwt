<?php
declare(strict_types=1);

namespace tests\unit\Controller;

use App\Controller\ChangePermissionUserController;
use App\Controller\LoginController;
use App\DTO\Input\ChangePermissionInput;
use App\DTO\Input\LoginInput;
use App\DTO\Output\LoginOutput;
use App\Manager\ChangePermissionManager;
use App\Manager\LoginUserManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ChangePermissionUserControllerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|SerializerInterface $serializer;
    private ObjectProphecy|ChangePermissionManager $changePermissionManager;
    private ChangePermissionUserController $changePermissionUserController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = self::prophesize(SerializerInterface::class);
        $this->changePermissionManager = self::prophesize(ChangePermissionManager::class);

        $this->changePermissionUserController = new ChangePermissionUserController(
            $this->serializer->reveal(),
            $this->changePermissionManager->reveal(),
        );
    }

    public function testChangePermission(): void
    {
        /** @var ObjectProphecy|Request $request */
        $request = self::prophesize(Request::class);
        $content = 'content';
        $changePermissionInput = new ChangePermissionInput();

        $request
            ->getContent()
            ->willReturn($content)
            ->shouldBeCalledOnce();

        $this->serializer
            ->deserialize($content, ChangePermissionInput::class, 'json')
            ->willReturn($changePermissionInput)
            ->shouldBeCalledOnce();

        $this->changePermissionManager
            ->changePermissions($changePermissionInput)
            ->shouldBeCalledOnce();

        $response = $this->changePermissionUserController->changePermission($request->reveal());

        self::assertEquals(new Response(), $response);
    }
}