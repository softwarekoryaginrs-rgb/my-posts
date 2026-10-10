<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Set\RoleSet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User()
            ->setUsername('root')
            ->setRoles([RoleSet::ROLE_ADMIN]);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'root'));

        $manager->persist($user);
        $manager->flush();
    }
}
