<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    /**
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $users = [
            ['email' => 'admin@example.fr', 'roles' => ['ROLE_ADMIN']],
            ['email' => 'user1@example.fr', 'roles' => ['ROLE_USER']],
            ['email' => 'user2@example.fr', 'roles' => ['ROLE_USER']],
            ['email' => 'user3@example.fr', 'roles' => ['ROLE_USER']],
            ['email' => 'user4@example.fr', 'roles' => ['ROLE_USER']],
            ['email' => 'user5@example.fr', 'roles' => ['ROLE_USER']],
        ];

        foreach ($users as $i => $userData) {
            $user = new User();
            $user
                ->setEmail($userData['email'])
                ->setRoles($userData['roles'])
                ->setPassword($this->passwordHasher->hashPassword($user, 'xxx'))
                ->setSubscribedToNewsletter(random_int(0,1) === 1);

            $manager->persist($user);
            $this->addReference('user_' .$i, $user);
        }

        $manager->flush();
    }

}
