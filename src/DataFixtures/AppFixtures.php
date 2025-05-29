<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
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
        $manager->flush();
    }
}
