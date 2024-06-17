<?php

namespace App\Tests\Fonctional;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{

    public function getTask()
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $query = $entityManager->createQuery(
            'SELECT t
            FROM App\Entity\Task t'
        )->setMaxResults(1);
        return $query->getOneOrNullResult();
    }

    public function testListAction(): void
    {
        $client = DefaultControllerTest::createAuthenticationClient();
        $crawler = $client->request('GET', '/tasks');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('ul.task-list'));
    }

    public function testCreateAction()
    {
        $client = DefaultControllerTest::createAuthenticationClient();
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $crawler = $client->request('GET', '/tasks/create');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Ajouter')->form();

        $formValues = [
            'task[title]' => 'Test title',
            'task[content]' => 'Test content',
        ];

        $client->submit($form, $formValues);

        $this->assertTrue($client->getResponse()->isRedirect('/tasks'));

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('Superbe !', $crawler->filter('aside.notifications')->text());

        $repository = $entityManager->getRepository(Task::class);
        $newTask = $repository->findOneBy([
            'title' => $formValues['task[title]'],
            'content' => $formValues['task[content]']
        ]);

        $this->assertNotNull($newTask, 'La tâche n\'est pas ajoutée');
    }

    public function testEditAction()
    {
        $client = DefaultControllerTest::createAuthenticationClient();
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $task = $this->getTask();
        $crawler = $client->request('GET', '/tasks/'.$task->getId().'/edit');

        $form = $crawler->selectButton('Modifier')->form();

        $formValues = [
            'task[title]' => 'test title edit',
            'task[content]' => 'test content edit',
        ];

        $client->submit($form, $formValues);

        $this->assertTrue($client->getResponse()->isRedirect('/tasks'));

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('Superbe !', $crawler->filter('aside.notifications')->text());

        $repository = $entityManager->getRepository(Task::class);
        $editTask = $repository->findOneBy([
            'title' => $formValues['task[title]'],
            'content' => $formValues['task[content]']
        ]);

        $this->assertNotNull($editTask, 'La tâche n\'est pas modifiée');
    }

    /**
     * @throws \JsonException
     */
    /*public function testToggleTaskAction(): void
    {
        $client = DefaultControllerTest::createAuthenticationClient();

        // Démarrer la session manuellement si nécessaire
        $client->request('GET', '/'); // Effectue une requête pour démarrer la session

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $repository = $entityManager->getRepository(Task::class);

        $lastTask = $this->getTask();
        $lastState = $lastTask->isDone();

        // Ajout d'un jeton CSRF valide
        $csrfTokenManager = self::getContainer()->get('security.csrf.token_manager');
        $csrfToken = $csrfTokenManager->getToken('toggle_task')->getValue();

        // Effectuer la requête avec le jeton CSRF
        $client->request('POST', '/tasks/' . $lastTask->getId() . '/toggle', [], [], [
            'HTTP_X-CSRF-Token' => $csrfToken,
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        // Vérifier que la réponse est un succès
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $responseContent = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Vérifier le contenu de la réponse JSON
        $this->assertArrayHasKey('success', $responseContent);
        $this->assertTrue($responseContent['success']);

        // Vérifier que l'état de la tâche a été modifié
        $currentTask = $repository->find($lastTask->getId());
        $this->assertNotEquals($lastState, $currentTask->isDone());
    }*/

    public function testDeleteAnonymTaskWithNoAdminAccount()
    {
        $client = DefaultControllerTest::createAuthenticationClient('user');

        /** @var TaskRepository $taskRepository */
        $taskRepository = $client->getContainer()->get(TaskRepository::class);
        $task = $taskRepository->findOneBy(['owner' => null]);
        $this->assertNotNull($task);

        $client->request('POST', '/tasks/' . $task->getId() . '/delete');

        // Vérifiez le code de statut de la réponse
        $response = $client->getResponse();
        $this->assertEquals(403, $response->getStatusCode(), 'Expected 403 Forbidden status code for unauthorized task deletion');

        // Vérifiez le contenu de la réponse pour le message d'erreur
        $this->assertStringContainsString('Vous ne pouvez pas supprimer cette tâche.', $response->getContent());
    }

    public function testDeleteAnonymTaskWithAdminAccount(): void
    {
        $client = DefaultControllerTest::createAuthenticationClient("admin");
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $task = new Task();
        $task->setContent('test content');
        $task->setTitle('test title');
        $entityManager->persist($task);
        $entityManager->flush();

        $client->request('GET', '/tasks/'.$task->getId().'/delete');
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('Superbe !', $crawler->filter('aside.notifications')->text());
    }

    public function testDeleteOwnTaskWithAccount(): void
    {
        $client = DefaultControllerTest::createAuthenticationClient();
        $token = self::getContainer()->get('security.token_storage')->getToken();

        if ($token === null) {
            $this->fail("No user connected");
        }

        $user = $token->getUser();

        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $task = new Task();
        $task->setContent('test content');
        $task->setTitle('test title');
        $task->setOwner($user);
        $entityManager->persist($task);
        $entityManager->flush();

        $client->request('GET', '/tasks/'.$task->getId().'/delete');
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('Superbe !', $crawler->filter('aside.notifications')->text());
    }
}
