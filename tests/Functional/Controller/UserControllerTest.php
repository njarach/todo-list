<?php

namespace Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testCreateUser()
    {
        $client = static::createClient();

        // Log in as admin
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'admin',
            'password' => 'password'
        ]);

        // Access user creation page
        $client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();

        // Create a new user
        $client->submitForm('Ajouter', [
            'user[username]' => 'testuser',
            'user[password][first]' => 'password123',
            'user[password][second]' => 'password123',
            'user[email]' => 'test@example.com',
            'user[roles]' => 'ROLE_USER'
        ]);

        // Should redirect to user list
        $this->assertResponseRedirects('/users');
        $client->followRedirect();

        // Check that new user appears in the list
        $this->assertSelectorTextContains('body', 'testuser');
        $this->assertSelectorTextContains('body', 'test@example.com');
    }

    public function testEditUser()
    {
        $client = static::createClient();

        // Log in as admin
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'admin',
            'password' => 'password'
        ]);

        // Find a user to edit
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository('App:User')->findOneBy(['username' => 'user']);

        if (!$user) {
            $this->markTestSkipped('User not found for testing');
        }

        // Access user edit page
        $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        // Edit the user
        $client->submitForm('Modifier', [
            'user[username]' => 'updateduser',
            'user[password][first]' => 'newpassword',
            'user[password][second]' => 'newpassword',
            'user[email]' => 'updated@example.com',
            'user[roles]' => 'ROLE_ADMIN'
        ]);

        // Should redirect to user list
        $this->assertResponseRedirects('/users');
        $client->followRedirect();

        // Check that updated user appears in the list
        $this->assertSelectorTextContains('body', 'updateduser');
        $this->assertSelectorTextContains('body', 'updated@example.com');
    }

    public function testDeleteUser()
    {
        $client = static::createClient();

        // Log in as admin
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'admin',
            'password' => 'password'
        ]);

        // Create a user to delete
        $client->request('GET', '/users/create');
        $client->submitForm('Ajouter', [
            'user[username]' => 'usertodelete',
            'user[password][first]' => 'password',
            'user[password][second]' => 'password',
            'user[email]' => 'delete@example.com',
            'user[roles]' => 'ROLE_USER'
        ]);
        $client->followRedirect();

        // Find the user to delete
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $userToDelete = $entityManager->getRepository('App:User')->findOneBy(['username' => 'usertodelete']);

        if (!$userToDelete) {
            $this->markTestSkipped('User not found for deletion test');
        }

        // Delete the user
        $client->request('GET', '/users/' . $userToDelete->getId() . '/delete');

        // Should redirect to user list
        $this->assertResponseRedirects('/users');
        $client->followRedirect();

        // Check success message
        $this->assertSelectorExists('.alert-success');

        // Verify user no longer exists in the list
        $this->assertSelectorNotExists('td:contains("usertodelete")');
    }
}