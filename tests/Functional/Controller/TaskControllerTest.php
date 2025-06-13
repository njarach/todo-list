<?php

namespace Tests\Functional\Controller;

use App\DataFixtures\TaskFixtures;
use App\DataFixtures\UserFixtures;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $databaseTool;
    private $client;
    private $userRepository;
    private $taskRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->taskRepository = static::getContainer()->get(TaskRepository::class);

        $this->databaseTool->get()->loadFixtures([UserFixtures::class, TaskFixtures::class]);
    }

    public function testTaskListShowsIncompleteTasks(): void
    {
        $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html', 'Test Task 1');
        $this->assertSelectorTextNotContains('html', 'Completed Task');
    }

    public function testFinishedTaskListShowsCompletedTasks(): void
    {
        $this->client->request('GET', '/finished_tasks');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html', 'Completed Task');
        $this->assertSelectorTextNotContains('html', 'Test Task 1');
    }

    public function testCreateTaskRequiresAuthentication(): void
    {
        $this->client->request('GET', '/tasks/create');

        $this->assertResponseRedirects('/login');
    }

    public function testCreateTaskAsLoggedInUser(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Ajouter', [
            'task[title]' => 'New Test Task',
            'task[content]' => 'This is a new task'
        ]);

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('html', 'La tâche a été bien été ajoutée.');
    }

    public function testEditTaskAsOwner(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $task = $this->taskRepository->findOneBy(['title' => 'Test Task 1']);
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Modifier', [
            'task[title]' => 'Updated Task Title',
            'task[content]' => 'Updated content'
        ]);

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('html', 'La tâche a bien été modifiée.');
    }

    public function testToggleTask(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $task = $this->taskRepository->findOneBy(['title' => 'Test Task 1']);
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/tasks/' . $task->getId() . '/toggle');

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('html', 'La tâche Test Task 1 a bien été marquée comme faite.');
    }

    public function testDeleteTaskAsOwner(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $task = $this->taskRepository->findOneBy(['title' => 'Test Task 1']);
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('html', 'La tâche a bien été supprimée.');
    }

    public function testEditTaskWithoutPermissionThrowsException(): void
    {
        $task = $this->taskRepository->findOneBy(['title' => 'Test Task 1']);

        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testDeleteTaskWithoutPermissionThrowsException(): void
    {
        $task = $this->taskRepository->findOneBy(['title' => 'Test Task 1']);

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');

        $this->assertResponseRedirects('/login');
    }
}