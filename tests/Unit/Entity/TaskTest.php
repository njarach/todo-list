<?php

namespace Tests\Unit\Entity;

use App\Entity\Task;
use App\Entity\User;

class TaskTest extends \PHPUnit\Framework\TestCase
{
    public function testTaskCreation()
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setContent('Task content test');

        $this->assertEquals('Test Task', $task->getTitle());
        $this->assertEquals('Task content test', $task->getContent());
        $this->assertFalse($task->isDone());
    }

    public function testTaskToggle()
    {
        $task = new Task();

        // Default state is not done
        $this->assertFalse($task->isDone());

        // Toggle to done
        $task->toggle(true);
        $this->assertTrue($task->isDone());

        // Toggle back to not done
        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }

    public function testTaskAuthor()
    {
        $user = new User();
        $user->setUsername('taskauthor');

        $task = new Task();
        $task->setAuthor($user);

        $this->assertSame($user, $task->getAuthor());
    }
}