<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Set\RoleSet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Creates an admin user, or resets the password of an existing one')]
class CreateAdminCommand
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Login of the admin')] string $username,
        #[Argument('Plain password; asked interactively when omitted')] ?string $password = null,
    ): int {
        $password ??= $io->askHidden('Password');
        if (null === $password || '' === $password) {
            $io->error('Password must not be empty.');

            return Command::FAILURE;
        }

        $user = $this->users->findOneBy(['username' => $username]) ?? (new User())->setUsername($username);
        $user->setRoles(array_values(array_unique([...$user->getRoles(), RoleSet::ROLE_ADMIN])));
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin "%s" is ready.', $username));

        return Command::SUCCESS;
    }
}
