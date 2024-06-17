<?php

namespace App\Tests\EventListener;

use App\EventListener\RedirectOnAccessDeniedListener;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RedirectOnAccessDeniedListenerTest extends WebTestCase
{
    public function testOnKernelException()
    {
        // Créer un client et un noyau pour simuler une requête
        $client = static::createClient();
        $container = static::getContainer();

        // Simuler une AccessDeniedException
        $request = Request::create('/users');
        $event = new ExceptionEvent($client->getKernel(), $request, HttpKernelInterface::MAIN_REQUEST, new AccessDeniedHttpException());

        // Créer et appeler le listener
        $listener = new RedirectOnAccessDeniedListener($container->get('router'));
        $listener->onKernelException($event);

        // Vérifier que la réponse est une redirection
        $response = $event->getResponse();
        $this->assertTrue($response->isRedirection());
        $this->assertEquals('/', $response->getTargetUrl());
    }
}