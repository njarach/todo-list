<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskTestFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $adminUser = $manager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $regularUser = $manager->getRepository(User::class)->findOneBy(['username' => 'user']);

        $task1 = new Task();
        $task1->setTitle('Test Task 1');
        $task1->setContent('This is a test task');
        $task1->setAuthor($adminUser);
        $task1->setCreatedAt(new \DateTime());
        $manager->persist($task1);

        $task2 = new Task();
        $task2->setTitle('Completed Task');
        $task2->setContent('This task is done');
        $task2->setAuthor($adminUser);
        $task2->setCreatedAt(new \DateTime());
        $task2->toggle(true);
        $manager->persist($task2);

        $task3 = new Task();
        $task3->setTitle('User Task');
        $task3->setContent('Task created by regular user');
        $task3->setAuthor($regularUser);
        $task3->setCreatedAt(new \DateTime());
        $manager->persist($task3);

        $manager->flush();

        $this->addReference('test-task-1', $task1);
        $this->addReference('test-task-2', $task2);
        $this->addReference('test-task-3', $task3);
    }

    public function getDependencies(): array
    {
        return [UserTestFixture::class];
    }
}