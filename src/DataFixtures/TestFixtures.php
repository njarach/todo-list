<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create anonymous user
        $anonymousUser = new User();
        $anonymousUser->setUsername('anonymous');
        $anonymousUser->setEmail('anonymous@example.com');
        $anonymousUser->setRoles(['ROLE_USER']);
        $anonymousUser->setPassword($this->passwordHasher->hashPassword(
            $anonymousUser,
            'password'
        ));
        $manager->persist($anonymousUser);
        $this->addReference('anonymous-user', $anonymousUser);

        // Create regular user
        $user = new User();
        $user->setUsername('user');
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            'password'
        ));
        $manager->persist($user);
        $this->addReference('regular-user', $user);

        // Create admin user
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword(
            $admin,
            'password'
        ));
        $manager->persist($admin);
        $this->addReference('admin-user', $admin);

        // Create tasks
        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task1->setContent('Content for task 1');
        $task1->setAuthor($user);
        $manager->persist($task1);

        $task2 = new Task();
        $task2->setTitle('Task 2');
        $task2->setContent('Content for task 2');
        $task2->setAuthor($admin);
        $manager->persist($task2);

        $task3 = new Task();
        $task3->setTitle('Anonymous Task');
        $task3->setContent('This task belongs to anonymous');
        $task3->setAuthor($anonymousUser);
        $manager->persist($task3);

        $manager->flush();
    }
}