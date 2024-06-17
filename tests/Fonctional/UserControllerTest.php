<?php

namespace App\Tests\Fonctional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function testListUserWithNoAdminAccount(): void
    {
        $client = DefaultControllerTest::createAuthenticationClient('user');
        $client->request('GET', '/users');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    public function testListUserWithAdminAccount()
    {
        $client = DefaultControllerTest::createAuthenticationClient('admin');
        $client->request('GET', '/users');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testCreateUser()
    {
        $client = DefaultControllerTest::createAuthenticationClient("admin");
        $crawler = $client->request('GET', '/users/create');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter('input[name="user[username]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="user[password][first]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="user[password][second]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="user[email]"]')->count());

        $username = substr(md5(mt_rand()), 0, 25);
        $password = substr(md5(mt_rand()), 0, 25);

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => $username,
            'user[password][first]' => $password,
            'user[password][second]' => $password,
            'user[email]' => $username.'@test.com',
        ]);
        $client->submit($form);

        $client->followRedirect();

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['username' => $username]);
        $this->assertNotNull($user);
    }

    public function testEditUser()
    {
        $client = DefaultControllerTest::createAuthenticationClient('admin');

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByNot('username', 'user')[0];
        $this->assertNotNull($user);

        $crawler = $client->request('GET', '/users/'.$user->getId().'/edit');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter('input[name="user[username]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="user[email]"]')->count());

        $randomValue = substr(md5(mt_rand()), 0, 25);
        $email = $randomValue . '@test.com';

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => $user->getUsername(),
            'user[email]' => $email,
        ]);
        $client->submit($form);

        $client->followRedirect();

        $newUser = $userRepository->findOneBy(['username' => $user->getUsername()]);
        $this->assertNotNull($newUser);
    }

    public function testEditUserWithAdminAccount()
    {
        $client = DefaultControllerTest::createAuthenticationClient('admin');

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByNot('username', 'user')[0];
        $this->assertNotNull($user);

        $crawler = $client->request('GET', '/users/'.$user->getId().'/edit');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter('input[name="user[username]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="user[email]"]')->count());

        $randomValue = substr(md5(mt_rand()), 0, 25);
        $email = $randomValue . '@test.com';

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => $user->getUsername(),
            'user[email]' => $email,
            'user[roles]' => 'ROLE_ADMIN'
        ]);
        $client->submit($form);

        $client->followRedirect();

        $newUser = $userRepository->findOneBy(['username' => $user->getUsername()]);
        $this->assertNotNull($newUser);
        $this->assertContains('ROLE_ADMIN', $newUser->getRoles());
    }
}
