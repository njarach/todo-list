<?php
// tests/Unit/Repository/TaskRepositoryTest.php
namespace Tests\Unit\Repository;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $taskRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->taskRepository = $this->entityManager->getRepository(Task::class);
    }

    public function testSaveTask()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'user']);

        $task = new Task();
        $task->setTitle('New Task');
        $task->setContent('Task content');
        $task->setAuthor($user);

        $this->taskRepository->save($task, true);

        $savedTask = $this->taskRepository->findOneBy(['title' => 'New Task']);

        $this->assertNotNull($savedTask);
        $this->assertEquals('New Task', $savedTask->getTitle());
        $this->assertEquals('Task content', $savedTask->getContent());
        $this->assertEquals($user->getId(), $savedTask->getAuthor()->getId());
    }

    public function testRemoveTask()
    {
        $task = $this->taskRepository->findOneBy(['title' => 'Task 1']);

        if (!$task) {
            $this->markTestSkipped('Task not found, check fixtures.');
        }

        $this->taskRepository->remove($task, true);

        $removedTask = $this->taskRepository->findOneBy(['title' => 'Task 1']);
        $this->assertNull($removedTask);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}