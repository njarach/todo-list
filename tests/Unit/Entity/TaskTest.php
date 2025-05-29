<?php

namespace Tests\Unit\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskToggle(): void
    {
        $task = new Task();
        $this->assertFalse($task->isDone());

        $task->toggle(true);
        $this->assertTrue($task->isDone());

        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }

    public function testTaskAuthorRelation(): void
    {
        $user = new User();
        $task = new Task();
        $task->setAuthor($user);

        $this->assertSame($user, $task->getAuthor());
    }

    public function testTaskBasicProperties(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setContent('Test Content');

        $this->assertEquals('Test Task', $task->getTitle());
        $this->assertEquals('Test Content', $task->getContent());
    }

    public function testTaskCreatedAtIsSet(): void
    {
        $task = new Task();
        $now = new \DateTime();
        $task->setCreatedAt($now);

        $this->assertSame($now, $task->getCreatedAt());
    }
}