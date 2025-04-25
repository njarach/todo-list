<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:assign-anonymous-to-tasks',
    description: 'Assign anonymous user to tasks without an author',
)]
class AssignAnonymousToTasksCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private TaskRepository $taskRepository;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        TaskRepository $taskRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $anonymousUser = $this->userRepository->findOneBy(['username' => 'anonymous']);

        // TODO : this create anonymous user is temporary, and really the anonymous user should be handled by admin or fixture before release
        if (!$anonymousUser) {
            $anonymousUser = new User();
            $anonymousUser->setUsername('anonymous');
            $anonymousUser->setEmail('anonymous@example.com');

            $hashedPassword = $this->passwordHasher->hashPassword(
                $anonymousUser,
                'anonymous_password'
            );
            $anonymousUser->setPassword($hashedPassword);

            $this->entityManager->persist($anonymousUser);
            $this->entityManager->flush();

            $io->success('Anonymous user created');
        }

        // Find tasks without author
        $tasks = $this->taskRepository->findBy(['author' => null]);
        $count = count($tasks);

        if ($count === 0) {
            $io->info('No tasks without author found');
            return Command::SUCCESS;
        }

        foreach ($tasks as $task) {
            $task->setAuthor($anonymousUser);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Assigned anonymous user to %d tasks', $count));

        return Command::SUCCESS;
    }
}