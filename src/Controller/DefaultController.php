<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('default/index.html.twig', [
            'tasks' => $entityManager->getRepository(Task::class)->findAll(),
        ]);
    }
}
