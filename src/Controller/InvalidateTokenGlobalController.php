<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvalidateTokenGlobalController extends AbstractController
{
    private UserTokenRepository $userJwtRepository;

    public function __construct(
        UserTokenRepository $userJwtRepository
    )
    {
        $this->userJwtRepository = $userJwtRepository;
    }

    #[Route(path: 'api/invalidate-token-global', name: 'token-global-invalidation', methods: ["POST"])]
    public function invalidate(): Response
    {

        $this->userJwtRepository->invalidateAllTokens();

        return new Response();
    }
}