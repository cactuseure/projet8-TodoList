<?php

namespace App\Tests\Unit;

use App\Entity\Task;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{

    /** @var EntityManager */
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testCreateTask()
    {
        $task = new Task();
        $task->setTitle('Test task');
        $task->setContent('task content');

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $foundTask = $this->entityManager->getRepository(Task::class)->find($task->getId());
        $this->assertSame('Test task', $foundTask->getTitle());
    }

}