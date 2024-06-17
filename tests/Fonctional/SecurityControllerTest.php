<?php

namespace App\Tests\Fonctional;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityControllerTest extends WebTestCase
{

    public function testLoginSuccess()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter('input[name="_username"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="_password"]')->count());

        $form = $crawler->selectButton('Se connecter')->form();
        $formValues = [
            '_username' => 'user',
            '_password' => 'password'
        ];
        $client->submit($form, $formValues);

        self::assertResponseRedirects('/');

        $client->followRedirect();

        $this->assertTrue($client->getContainer()->get('security.helper')->isGranted('ROLE_USER'));

        $client->request('GET', '/login');
        self::assertResponseRedirects('/');
    }

    public function testLoginFailed()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter('input[name="_username"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="_password"]')->count());

        $form = $crawler->selectButton('Se connecter')->form();
        $formValues = [
            '_username' => 'nonoland',
            '_password' => 'testt'
        ];
        $client->submit($form, $formValues);

        $client->followRedirect();

        $this->assertEquals('/login', $client->getRequest()->getPathInfo());
        $this->assertFalse($client->getContainer()->get('security.helper')->isGranted('ROLE_USER'));
    }

    public function testLogout()
    {
        $client = DefaultControllerTest::createAuthenticationClient();

        $client->request('GET','/logout');

        $this->assertResponseRedirects('/login');
    }
}