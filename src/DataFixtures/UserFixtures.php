<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    
    public static function getGroups(): array
    {
        return ['users'];
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'username' => 'hamza',
                'password' => 'farhani',
                'roles' => ['ROLE_ADMIN']
            ],
            [
                'username' => 'SAMEH',
                'password' => 'BAMOUNA',
                'roles' => ['ROLE_ADMIN']
            ]
            // Vous pouvez ajouter d'autres utilisateurs ici
        ];

        foreach ($users as $userData) {
            $existingUser = $manager->getRepository(User::class)
                ->findOneBy(['username' => $userData['username']]);

            if (!$existingUser) {
                $user = new User();
                $user->setUsername($userData['username']);
                $user->setPassword(
                    $this->passwordHasher->hashPassword($user, $userData['password'])
                );
                $user->setRoles($userData['roles']);
                
                $manager->persist($user);
            }
        }

        $manager->flush();
    }
}