<?php
declare(strict_types=1);

namespace tests\unit\Controller;

use App\Controller\InvalidateTokenGlobalController;
use App\Controller\LoginController;
use App\Controller\ValidateUserTokenController;
use App\DTO\Input\LoginInput;
use App\DTO\Output\LoginOutput;
use App\Manager\LoginUserManager;
use App\Repository\UserTokenRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class InvalidateTokenGlobalControllerTest extends TestCase
{
    use ProphecyTrait;

    private InvalidateTokenGlobalController $invalidateTokenGlobalController;
    private ObjectProphecy|UserTokenRepository $userTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userTokenRepository = self::prophesize(UserTokenRepository::class);

        $this->invalidateTokenGlobalController = new InvalidateTokenGlobalController(
            $this->userTokenRepository->reveal()
        );
    }

    public function testInvalidate(): void
    {

        $this->userTokenRepository
            ->invalidateAllTokens()
            ->shouldBeCalledOnce();

        $response = $this->invalidateTokenGlobalController->invalidate();

        self::assertEquals(new Response(), $response);
    }
}