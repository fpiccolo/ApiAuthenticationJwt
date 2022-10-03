<?php
declare(strict_types=1);

namespace unit\Service;

use App\Service\PasswordEncrypt;
use PHPUnit\Framework\TestCase;

class PasswordEncryptTest extends TestCase
{
    private PasswordEncrypt $passwordEncrypt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordEncrypt = new PasswordEncrypt();
    }

    public function testEncrypt(): void
    {
        $password = 'password';

        $result = $this->passwordEncrypt->encrypt($password);

        self::assertEquals(md5($password), $result);
    }
}