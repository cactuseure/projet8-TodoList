<?php
// src/Command/CreateUserCommand.php
namespace App\Command;

use AllowDynamicProperties;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// the name of the command is what users type after "php bin/console"
#[AllowDynamicProperties]
#[AsCommand(
    name: 'projet8:create-user',
    description: 'Add user'
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = new User();

        // Demande de l'email
        $user->setEmail($io->ask('Email '));

        $userRepository = $this->entityManager->getRepository(User::class);
        if ($userRepository->count(['email' => $user->getEmail()]) >= 1) {
            $io->error('An account already exists with this email address');
            return Command::FAILURE;
        }

        // Demande de l'username
        $username = $io->ask('Username ');
        if ($userRepository->count(['username' => $username]) >= 1) {
            $io->error('An account already exists with this username');
            return Command::FAILURE;
        }
        $user->setUsername($username);

        // Demande des mots de passe
        $password = $io->askHidden('Password ');
        $repeatPassword = $io->askHidden('Repeat password');

        if ($password !== $repeatPassword) {
            $io->error('Passwords don\'t match.');
            return Command::FAILURE;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        // Demande si l'utilisateur est admin
        $admin = $io->ask('User admin ? (yes/no)', 'no');
        if ($admin !== "no") {
            $user->setRoles(['ROLE_ADMIN']);
        }

        // Persist et flush
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User successfully created');

        return Command::SUCCESS;
    }

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            // the command description shown when running "php bin/console list"
            ->setDescription('Creates a new user.')
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to create a user...')
        ;
    }
}