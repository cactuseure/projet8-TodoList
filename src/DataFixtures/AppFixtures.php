<?php
// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@gmail.com');
        $user->setUsername('user');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $admin = new User();
        $admin->setEmail('admin@gmail.com');
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $taskUser = new Task();
        $taskUser->setTitle('Task title');
        $taskUser->setContent('Existing content');
        $taskUser->setOwner($user);
        $manager->persist($taskUser);

        $taskAdmin = new Task();
        $taskAdmin->setTitle('Task title admin');
        $taskAdmin->setContent('Existing content admin');
        $taskAdmin->setOwner($admin);
        $manager->persist($taskAdmin);

        $task = new Task();
        $task->setTitle('Task title');
        $task->setContent('Existing content');
        $manager->persist($task);

        $manager->flush();
    }
}