<?php

namespace Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessControlTest extends WebTestCase
{
    public function testAccessAsAnonymous()
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/users');
        $this->assertResponseRedirects('/login');
    }

    public function testAccessAsUser()
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'user',
            'password' => 'userpass'
        ]);

        $client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAccessAsAdmin()
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Connexion', [
            'username' => 'admin',
            'password' => 'adminpass'
        ]);

        $client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
    }
}