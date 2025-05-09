<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list')]
    public function listAction(TaskRepository $taskRepository): Response
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $taskRepository->findAll()
        ]);
    }

    #[Route('/tasks/create', name: 'task_create')]
    public function createAction(Request $request, TaskRepository $taskRepository): Response
    {
        if (!$this->isGranted('ROLE_USER' && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException('Veuillez vous connecter pour créer une tâche.');
        }
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Here we added the binding of the task to the User
            $task->setAuthor($this->getUser());
            $taskRepository->save($task, true);

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function editAction(Task $task, Request $request, TaskRepository $taskRepository): Response
    {
        if (($this->getUser() !== $task->getAuthor()) && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne disposez pas de l'autorisation nécessaire pour éditer cette tâche.");
        }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task, true);

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTaskAction(Task $task, TaskRepository $taskRepository): Response
    {
        $task->toggle(!$task->isDone());
        $taskRepository->save($task, true);

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function deleteTaskAction(Task $task, TaskRepository $taskRepository): Response
    {
        if ($task->getAuthor()->getUsername() === 'anonymous' && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne disposez pas de l'autorisation nécessaire pour supprimer cette tâche.");
        }
        if (($this->getUser() !== $task->getAuthor()) && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne disposez pas de l'autorisation nécessaire pour supprimer cette tâche.");
        }

        $taskRepository->remove($task, true);

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}