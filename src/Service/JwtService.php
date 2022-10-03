<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Cake\Chronos\Chronos;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $key;
    private string $algorithm;

    public function __construct(
        string $key,
        string $algorithm
    )
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
    }

    public function generateToken(User $user): string
    {
        return JWT::encode(
            [
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'exp' => Chronos::now()->addMinute(60)->toUnixString(),
                'iat' => Chronos::now()->toUnixString(),
            ],
            $this->key,
            $this->algorithm
        );
    }

    public function getTokenCreatedAt(string $token): \DateTimeInterface
    {
        $decoded = JWT::decode($token, new Key($this->key, $this->algorithm));
        return Chronos::createFromTimestamp((int)$decoded->iat);
    }

    public function getTokenExpireAt(string $token): \DateTimeInterface
    {
        $decoded = JWT::decode($token, new Key($this->key, $this->algorithm));
        return Chronos::createFromTimestamp((int)$decoded->exp);
    }

    public function validateToken(string $token): void
    {
        JWT::decode($token, new Key($this->key, $this->algorithm));
    }
}