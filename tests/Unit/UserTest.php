<?php

namespace App\Tests\Unit;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    /** @var User */
    private User $user;
    /** @var EntityManager */
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
    }

    public function testSetUsername()
    {
        $this->user->setUsername('test');
        $this->assertSame('test', $this->user->getUsername());
    }

    public function testSetPassword(): void
    {
        $this->user->setPassword('testpassword');
        $this->assertSame('testpassword', $this->user->getPassword());
    }

    public function testSetEmail(): void
    {
        $this->user->setEmail('test@gmail.com');
        $this->assertSame('test@gmail.com', $this->user->getEmail());
    }

    public function testSetRoles(): void
    {
        $this->user->setRoles(['ROLE_TEST']);
        $this->assertSame(['ROLE_TEST', 'ROLE_USER'], $this->user->getRoles());

        $this->user->setRoles(['ROLE_TEST', 'ROLE_TEST_2']);
        $this->assertSame(['ROLE_TEST', 'ROLE_TEST_2', 'ROLE_USER'], $this->user->getRoles());
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testAddTask(): void
    {
        $task = new Task();
        $task->setTitle('Test task');
        $task->setContent('task content');
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->user->addTask($task);

        $tasks = $this->user->getTasks();

        $this->assertContains($task, $tasks);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testRemoveTask(): void
    {
        $task = new Task();
        $task->setTitle('Test task');
        $task->setContent('task content');
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->user->addTask($task);

        $tasks = $this->user->getTasks();

        $this->assertContains($task, $tasks);

        $this->user->removeTask($task);

        $this->assertNotContains($task, $tasks);
    }
}