<?php

namespace App\Tests\Unit;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{

    private $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->task = new Task();
    }

    public function testSetTitle()
    {
        $this->task->setTitle('New task');
        $this->assertEquals('New task', $this->task->getTitle());
    }

    public function testSetContent(): void
    {
        $this->task->setContent('New content task');
        $this->assertEquals('New content task', $this->task->getContent());
    }

    public function testSetCreatedAt(): void
    {
        $now = new \DateTimeImmutable();
        $this->task->setCreatedAt($now);
        $this->assertEquals($now, $this->task->getCreatedAt());
    }

    public function testIsDone(): void
    {
        $this->task->setDone(true);
        $this->assertTrue($this->task->isDone());
    }
}