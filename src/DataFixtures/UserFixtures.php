<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $adminUser = new User();
        $adminUser->setUsername('admin');
        $adminUser->setEmail('admin@example.com');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'adminpass');
        $adminUser->setPassword($hashedPassword);
        $manager->persist($adminUser);

        // Create regular user
        $regularUser = new User();
        $regularUser->setUsername('user');
        $regularUser->setEmail('user@example.com');
        $regularUser->setRoles(['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($regularUser, 'userpass');
        $regularUser->setPassword($hashedPassword);
        $manager->persist($regularUser);

        $manager->flush();
    }
}
