<?php
declare(strict_types=1);

namespace tests\unit\Controller;

use App\Controller\LoginController;
use App\Controller\ValidateUserTokenController;
use App\DTO\Input\LoginInput;
use App\DTO\Output\LoginOutput;
use App\Manager\LoginUserManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ValidateUserTokenControllerTest extends TestCase
{
    private ValidateUserTokenController $validateUserTokenController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validateUserTokenController = new ValidateUserTokenController();
    }

    public function testValidate(): void
    {

        $response = $this->validateUserTokenController->validate();

        self::assertEquals(new Response(), $response);
    }
}