<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AppFixtures;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixturesTest extends KernelTestCase
{
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Purger la base de données pour un état propre avant de charger les fixtures
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->entityManager);
        $purger->purge();
    }

    public function testLoadFixtures()
    {
        // Charger les fixtures
        $fixtures = new AppFixtures($this->passwordHasher);
        $fixtures->load($this->entityManager);

        // Vérifier les entités User
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'user@gmail.com']);
        $this->assertNotNull($user);
        $this->assertEquals('user', $user->getUsername());
        $this->assertTrue($this->passwordHasher->isPasswordValid($user, 'password'));
        $this->assertContains('ROLE_USER', $user->getRoles());

        $admin = $userRepository->findOneBy(['email' => 'admin@gmail.com']);
        $this->assertNotNull($admin);
        $this->assertEquals('admin', $admin->getUsername());
        $this->assertTrue($this->passwordHasher->isPasswordValid($admin, 'password'));
        $this->assertContains('ROLE_ADMIN', $admin->getRoles());

        // Vérifier les entités Task
        $taskRepository = $this->entityManager->getRepository(Task::class);
        $tasks = $taskRepository->findAll();
        $this->assertCount(3, $tasks);

        $taskUser = $taskRepository->findOneBy(['title' => 'Task title', 'owner' => $user]);
        $this->assertNotNull($taskUser);
        $this->assertEquals('Existing content', $taskUser->getContent());

        $taskAdmin = $taskRepository->findOneBy(['title' => 'Task title admin', 'owner' => $admin]);
        $this->assertNotNull($taskAdmin);
        $this->assertEquals('Existing content admin', $taskAdmin->getContent());

        $task = $taskRepository->findOneBy(['title' => 'Task title', 'owner' => null]);
        $this->assertNotNull($task);
        $this->assertEquals('Existing content', $task->getContent());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}