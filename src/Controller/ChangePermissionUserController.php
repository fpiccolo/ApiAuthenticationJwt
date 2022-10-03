<?php
declare(strict_types=1);

namespace App\Controller;

use App\DTO\Input\ChangePermissionInput;
use App\Manager\ChangePermissionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ChangePermissionUserController extends AbstractController
{
    private SerializerInterface $serializer;
    private ChangePermissionManager $changePermissionManager;

    public function __construct(
        SerializerInterface $serializer,
        ChangePermissionManager $changePermissionManager
    )
    {
        $this->serializer = $serializer;
        $this->changePermissionManager = $changePermissionManager;
    }

    #[Route(path: 'api/change-permission', name: 'change-permission', methods: ["POST"])]
    public function changePermission(Request $request): Response
    {
        /** @var ChangePermissionInput $dto */
        $dto = $this->serializer->deserialize($request->getContent(), ChangePermissionInput::class, 'json');

        $this->changePermissionManager->changePermissions($dto);

        return new Response();
    }
}