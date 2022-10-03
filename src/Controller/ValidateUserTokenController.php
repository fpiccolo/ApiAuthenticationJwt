<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ValidateUserTokenController extends AbstractController
{
    #[Route(path: 'api/token-verification', name: 'token-verification', methods: ["GET"])]
    public function validate(): Response
    {
        return new Response();
    }
}