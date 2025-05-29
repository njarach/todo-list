<?php

namespace Tests\Functional\Controller;

use App\DataFixtures\UserTestFixture;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $databaseTool;
    private $client;
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);

        // Load fixtures before each test
        $this->databaseTool->get()->loadFixtures([UserTestFixture::class]);
    }

    public function testListUsersAsAdmin(): void
    {
        // Login as admin
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($adminUser);

        $crawler = $this->client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
        // Check if users are displayed in the table
        $this->assertGreaterThan(0, $crawler->filter('tbody tr')->count());
    }

    public function testCreateUser(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($adminUser);

        // Get the create form
        $crawler = $this->client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();

        // Submit the form
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'newuser',
            'user[password][first]' => 'password123',
            'user[password][second]' => 'password123',
            'user[email]' => 'newuser@example.com',
            'user[roles]' => 'ROLE_USER',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/users');

        // Check if user was created
        $newUser = $this->userRepository->findOneBy(['username' => 'newuser']);
        $this->assertNotNull($newUser);
        $this->assertEquals('newuser@example.com', $newUser->getEmail());
    }

    public function testEditUser(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($adminUser);

        $userToEdit = $this->userRepository->findOneBy(['username' => 'user']);

        // Get the edit form
        $crawler = $this->client->request('GET', '/users/'.$userToEdit->getId().'/edit');
        $this->assertResponseIsSuccessful();

        // Submit the form with new data
        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'updateduser',
            'user[password][first]' => 'newpassword123',
            'user[password][second]' => 'newpassword123',
            'user[email]' => 'updated@example.com',
            'user[roles]' => 'ROLE_ADMIN',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/users');

        // Check if user was updated
        $updatedUser = $this->userRepository->find($userToEdit->getId());
        $this->assertEquals('updateduser', $updatedUser->getUsername());
        $this->assertEquals('updated@example.com', $updatedUser->getEmail());
        $this->assertContains('ROLE_ADMIN', $updatedUser->getRoles());
    }

    public function testDeleteUser(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($adminUser);

        $userToDelete = $this->userRepository->findOneBy(['username' => 'user']);
        $userId = $userToDelete->getId();

        // Delete the user
        $this->client->request('GET', '/users/'.$userId.'/delete');
        $this->assertResponseRedirects('/users');

        // Check if user was deleted
        $deletedUser = $this->userRepository->find($userId);
        $this->assertNull($deletedUser);
    }

    public function testAccessDeniedForNonAdmin(): void
    {
        // Login as regular user
        $regularUser = $this->userRepository->findOneBy(['username' => 'user']);
        $this->client->loginUser($regularUser);

        // Try to access admin-only pages
        $this->client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('GET', '/users/create');
        $this->assertResponseStatusCodeSame(403);
    }
}