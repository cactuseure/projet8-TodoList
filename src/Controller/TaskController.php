<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\Type\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $entityManager->getRepository(Task::class)->findAll(),
            'currentFilter' => 'all'
        ]);
    }

    #[Route('/tasks/pending', name: 'task_pending')]
    public function pendingTasks(EntityManagerInterface $entityManager): Response
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $entityManager->getRepository(Task::class)->findBy(['isDone' => false]),
            'currentFilter' => 'pending'
        ]);
    }

    #[Route('/tasks/completed', name: 'task_completed')]
    public function completedTasks(EntityManagerInterface $entityManager): Response
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $entityManager->getRepository(Task::class)->findBy(['isDone' => true]),
            'currentFilter' => 'completed'
        ]);
    }


    #[Route(path: '/tasks/create', name: 'task_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('app_default');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }


    #[Route(path: '/tasks/{id}/edit', name: 'task_edit')]
    #[IsGranted('edit', 'task','Vous ne pouvez pas éditer cette tâche.')]
    public function editAction(Task $task, Request $request,EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }


    #[Route(path: '/tasks/{id}/toggle', name: 'task_toggle', methods: ['POST'])]
    public function toggleTaskAction(Task $task, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager, Request $request): JsonResponse
    {
        $submittedToken = $request->headers->get('X-CSRF-Token');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('toggle_task', $submittedToken))) {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], 403);
        }

        $task->setDone(!$task->isDone());
        $entityManager->flush();

        if ($task->isDone()) {
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
        } else {
            $this->addFlash('notice', sprintf('La tâche %s a bien été rajoutée à la liste des tâches.', $task->getTitle()));
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route(path: '/tasks/{id}/delete', name: 'task_delete')]
    #[IsGranted('delete', 'task','Vous ne pouvez pas supprimer cette tâche.')]
    public function deleteTaskAction(Task $task, EntityManagerInterface $entityManager): RedirectResponse
    {
        try {
            $entityManager->remove($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');

            return $this->redirectToRoute('task_list');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la tâche.');

            return $this->redirectToRoute('task_list');
        }
    }
}
