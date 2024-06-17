<?php

namespace App\Tests\Command;

use App\Command\CreateUserCommand;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommandTest extends KernelTestCase
{
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Purger la base de données pour un état propre avant de charger les fixtures
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->entityManager);
        $purger->purge();
    }

    public function testExecute()
    {
        // Configurer l'application de test
        $application = new Application(self::$kernel);

        // Ajouter la commande à l'application
        $application->add(new CreateUserCommand($this->entityManager, $this->passwordHasher));

        // Récupérer la commande
        $command = $application->find('projet8:create-user');
        $commandTester = new CommandTester($command);

        // Simuler les entrées utilisateur
        $commandTester->setInputs([
            'user@test.com', // Email
            'username', // Username
            'password', // Password
            'password', // Repeat password
            'no' // Admin
        ]);

        // Exécuter la commande
        $commandTester->execute([]);

        // Vérifier le statut de sortie et les messages de succès
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('User successfully created', $output);
        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());

        // Vérifier que l'utilisateur a été créé dans la base de données
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'user@test.com']);
        $this->assertNotNull($user);
        $this->assertEquals('username', $user->getUsername());
        $this->assertTrue($this->passwordHasher->isPasswordValid($user, 'password'));
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}