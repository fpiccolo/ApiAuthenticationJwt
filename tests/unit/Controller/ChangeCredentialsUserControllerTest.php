<?php
declare(strict_types=1);

namespace tests\unit\Controller;

use App\Controller\ChangeCredentialsUserController;
use App\DTO\Input\ChangePasswordInput;
use App\DTO\Input\ChangePermissionInput;
use App\DTO\Input\LoginInput;
use App\DTO\Output\LoginOutput;
use App\Entity\User;
use App\Enum\Roles;
use App\Manager\ChangePasswordManager;
use App\Manager\ChangePermissionManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exception;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ChangeCredentialsUserControllerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|SerializerInterface $serializer;
    private ObjectProphecy|ChangePasswordManager $changePasswordManager;
    private ChangeCredentialsUserController $changeCredentialsUserController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = self::prophesize(SerializerInterface::class);
        $this->changePasswordManager = self::prophesize(ChangePasswordManager::class);

        $this->changeCredentialsUserController = new ChangeCredentialsUserController(
            $this->serializer->reveal(),
            $this->changePasswordManager->reveal(),
        );

        $this->changeCredentialsUserController->setContainer(self::createMock(ContainerInterface::class));
    }

    public function testChangeCredentialsRoleAdmin(): void
    {
        /** @var ObjectProphecy|Request $request */
        $request = self::prophesize(Request::class);
        /** @var ObjectProphecy|UserInterface $request */
        $user = self::prophesize(UserInterface::class);
        $content = 'content';
        $changePasswordInput = new ChangePasswordInput();
        $changePasswordInput->email = 'email';

        $request
            ->getContent()
            ->willReturn($content)
            ->shouldBeCalledOnce();

        $this->serializer
            ->deserialize($content, ChangePasswordInput::class, 'json')
            ->willReturn($changePasswordInput)
            ->shouldBeCalledOnce();

        $user->getRoles()
            ->willReturn([Roles::ROLE_ADMIN, Roles::ROLE_USER])
            ->shouldBeCalledOnce();

        $user->getUserIdentifier()
            ->willReturn('email')
            ->shouldBeCalledOnce();


        $this->changePasswordManager
            ->changePassword($changePasswordInput)
            ->shouldBeCalledOnce();

        $response = $this->changeCredentialsUserController->changeCredentials($request->reveal(), $user->reveal());

        self::assertEquals(new Response(), $response);
    }

    public function testChangeCredentialsRoleUser(): void
    {
        /** @var ObjectProphecy|Request $request */
        $request = self::prophesize(Request::class);
        /** @var ObjectProphecy|UserInterface $request */
        $user = self::prophesize(UserInterface::class);
        $content = 'content';
        $email = 'email';
        $changePasswordInput = new ChangePasswordInput();
        $changePasswordInput->email = $email;

        $request
            ->getContent()
            ->willReturn($content)
            ->shouldBeCalledOnce();

        $this->serializer
            ->deserialize($content, ChangePasswordInput::class, 'json')
            ->willReturn($changePasswordInput)
            ->shouldBeCalledOnce();

        $user->getRoles()
            ->willReturn([Roles::ROLE_USER])
            ->shouldBeCalledOnce();

        $user->getUserIdentifier()
            ->willReturn($email)
            ->shouldBeCalledOnce();

        $response = $this->changeCredentialsUserController->changeCredentials($request->reveal(), $user->reveal());

        self::assertEquals(new Response(), $response);
    }

    public function testChangeCredentialsThrowException(): void
    {

        self::expectExceptionObject(new \Exception("You don't have the permission for change password for another user", Response::HTTP_UNAUTHORIZED));

        /** @var ObjectProphecy|Request $request */
        $request = self::prophesize(Request::class);
        /** @var ObjectProphecy|UserInterface $request */
        $user = self::prophesize(UserInterface::class);
        $content = 'content';
        $emailUser = 'emailUser';
        $email = 'email';
        $changePasswordInput = new ChangePasswordInput();
        $changePasswordInput->email = $email;

        $request
            ->getContent()
            ->willReturn($content)
            ->shouldBeCalledOnce();

        $this->serializer
            ->deserialize($content, ChangePasswordInput::class, 'json')
            ->willReturn($changePasswordInput)
            ->shouldBeCalledOnce();

        $user->getRoles()
            ->willReturn([Roles::ROLE_USER])
            ->shouldBeCalledOnce();

        $user->getUserIdentifier()
            ->willReturn($emailUser)
            ->shouldBeCalledOnce();

        $this->changeCredentialsUserController->changeCredentials($request->reveal(), $user->reveal());
    }
}