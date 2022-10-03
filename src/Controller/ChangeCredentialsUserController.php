<?php
declare(strict_types=1);

namespace App\Controller;

use App\DTO\Input\ChangePasswordInput;
use App\Enum\Roles;
use App\Manager\ChangePasswordManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ChangeCredentialsUserController extends AbstractController
{
    private SerializerInterface $serializer;
    private ChangePasswordManager $changePasswordManager;

    public function __construct(
        SerializerInterface $serializer,
        ChangePasswordManager $changePasswordManager
    )
    {
        $this->serializer = $serializer;
        $this->changePasswordManager = $changePasswordManager;
    }

    #[Route(path: 'api/change-password', name: 'change-password', methods: ["POST"])]
    public function changeCredentials(Request $request, UserInterface $user): Response
    {
        /** @var ChangePasswordInput $dto */
        $dto = $this->serializer->deserialize($request->getContent(), ChangePasswordInput::class, 'json');

        if(!in_array(Roles::ROLE_ADMIN->value, $user->getRoles()) && $user->getUserIdentifier() !== $dto->email){
            throw new \Exception("You don't have the permission for change password for another user", Response::HTTP_UNAUTHORIZED);
        }

        $this->changePasswordManager->changePassword($dto);

        return new Response();
    }
}