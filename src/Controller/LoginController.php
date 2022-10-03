<?php
declare(strict_types=1);

namespace App\Controller;

use App\DTO\Input\LoginInput;
use App\Manager\LoginUserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class LoginController extends AbstractController
{
    private SerializerInterface $serializer;
    private LoginUserManager $loginUserManager;

    public function __construct(
        SerializerInterface $serializer,
        LoginUserManager $loginUserManager
    )
    {
        $this->serializer = $serializer;
        $this->loginUserManager = $loginUserManager;
    }

    #[Route(path: '/api/login', name: 'login', methods: ["POST"])]
    public function login(Request $request): Response
    {
        $dto = $this->serializer->deserialize($request->getContent(), LoginInput::class, 'json');

        return $this->json($this->loginUserManager->login($dto));
    }
}