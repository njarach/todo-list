<?php

namespace Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessControlTest extends WebTestCase
{
    public function testAccessAsAnonymous()
    {
        $client = static::createClient();

        // Anonymous users can access homepage
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        // Anonymous users are redirected from task list
        $client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');

        // Anonymous users are redirected from user management
        $client->request('GET', '/users');
        $this->assertResponseRedirects('/login');
    }

    public function testAccessAsUser()
    {
        $client = static::createClient();

        // Log in as a regular user
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'user',
            'password' => 'password'
        ]);

        // Regular users can access task list
        $client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();

        // Regular users cannot access user management
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(403); // Access denied
    }

    public function testAccessAsAdmin()
    {
        $client = static::createClient();

        // Log in as admin
        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'admin',
            'password' => 'password'
        ]);

        // Admins can access task list
        $client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();

        // Admins can access user management
        $client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
    }
}