<?php

namespace App\Tests\Unit;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRepositoryTest extends KernelTestCase
{

    /** @var EntityManager */
    private $entityManager;
    private null|UserPasswordHasherInterface $passwordEncoder;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->passwordEncoder = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testCreateUser(): void
    {
        $username = substr(md5(time()), 0, 20);

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username.'@gmail.com');
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'password_test'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $foundUser = $this->entityManager->getRepository(User::class)->find($user->getId());
        $this->assertSame($username, $foundUser->getUsername());
    }

}