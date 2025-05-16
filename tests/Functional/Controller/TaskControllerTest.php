<?php

namespace Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testCreateTask()
    {
        $client = static::createClient();
        // Log in as a regular user
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'user',
            'password' => 'password'
        ]);

        // Access task creation page
        $client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();

        // Create a new task
        $client->submitForm('Ajouter', [
            'task[title]' => 'Functional Test Task',
            'task[content]' => 'This is a task created during functional testing'
        ]);

        // Should redirect to task list after creation
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();

        // Check that the task appears in the list
        $this->assertSelectorTextContains('body', 'Functional Test Task');

        // Ensure flash message is displayed
        $this->assertSelectorTextContains('.alert-success', 'La tâche a été bien été ajoutée');
    }

    public function testToggleTask()
    {
        $client = static::createClient();

        // Log in as user
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'user',
            'password' => 'password'
        ]);

        // Go to task list
        $client->request('GET', '/tasks');

        // Find a toggle button and click it
        $client->clickLink('Marquer comme faite');

        // Should redirect back to task list
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();

        // Check success message
        $this->assertSelectorExists('.alert-success');
    }

    public function testDeleteOwnTask()
    {
        $client = static::createClient();

        // Log in as user
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'user',
            'password' => 'password'
        ]);

        // Go to task list
        $client->request('GET', '/tasks');

        // Find delete button for a task owned by the user and click it
        $crawler = $client->clickLink('Supprimer');

        // Should redirect back to task list
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();

        // Check success message
        $this->assertSelectorExists('.alert-success');
    }

    public function testCannotDeleteOtherUserTask()
    {
        $client = static::createClient();

        // Log in as regular user
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'user',
            'password' => 'password'
        ]);

        // Attempt to delete a task created by admin (assuming Task 2 is owned by admin)
        // Need to get the ID dynamically from the database
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $adminUser = $entityManager->getRepository('App:User')->findOneBy(['username' => 'admin']);
        $adminTask = $entityManager->getRepository('App:Task')->findOneBy(['author' => $adminUser]);

        if (!$adminTask) {
            $this->markTestSkipped('No admin task found to test with');
        }

        // Try to access delete URL directly
        $client->request('GET', '/tasks/' . $adminTask->getId() . '/delete');

        // Should get access denied
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanDeleteAnonymousTask()
    {
        $client = static::createClient();

        // Log in as admin
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'admin',
            'password' => 'password'
        ]);

        // Find task by anonymous user
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $anonymousUser = $entityManager->getRepository('App:User')->findOneBy(['username' => 'anonymous']);
        $anonymousTask = $entityManager->getRepository('App:Task')->findOneBy(['author' => $anonymousUser]);

        if (!$anonymousTask) {
            $this->markTestSkipped('No anonymous task found to test with');
        }

        // Delete the anonymous task
        $client->request('GET', '/tasks/' . $anonymousTask->getId() . '/delete');

        // Should redirect to task list
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();

        // Check success message
        $this->assertSelectorExists('.alert-success');
    }
}