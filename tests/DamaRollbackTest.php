<?php

/**
 * Dama rollback tests.
 */

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class DamaRollbackTest.
 */
class DamaRollbackTest extends KernelTestCase
{
    /**
     * Test Dama Rollback.
     */
    public function testDamaRollback(): void
    {
        self::bootKernel();

        $em = self::getContainer()
            ->get('doctrine')
            ->getManager();

        $user = new User();
        $user->setEmail('test-dama@example.com');
        $user->setUsername('testuser');
        $user->setPassword('test-password');
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        $foundUser = $em->getRepository(User::class)
            ->findOneBy([
                'email' => 'test-dama@example.com',
            ]);

        $this->assertNotNull($foundUser);
    }
}
