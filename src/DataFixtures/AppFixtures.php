<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $adminRole = ['ROLE_ADMIN', 'role_user'];
        $userRole = ['ROLE_USER'];
        $superAdminRole = ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'];

        for ($i = 0; $i < random_int(50, 120); $i++) {
            $user = new User;

            $user->setEmail($faker->email())
                ->setRoles($userRole)
                ->setPassword($this->hasher->hashPassword($user, 'magic'))
                ->setUsername($faker->userName)
                ->setCreatedAt((new DateTimeImmutable)->modify('-' . random_int(60, 400) . ' days'))
                ->setUpdatedAt($i % 15 ? (new DateTimeImmutable)->modify('-' . random_int(1, 60) . ' days') : null)
                ->setImageProfile($i % random_int(3, 5) ? $faker->imageUrl(640, 480, null, true, substr($user->getUsername(), 0, 1)) : null)
                ->setBiography($i % random_int(3, 5) ? $faker->text(random_int(30, 80))  : null);

            $manager->persist($user);
        }

        for ($i = 0; $i < random_int(0, 2); $i++) {
            $user = new User;

            $user->setEmail($faker->email())
                ->setRoles($adminRole)
                ->setPassword($this->hasher->hashPassword($user, 'magic'))
                ->setUsername($faker->userName)
                ->setCreatedAt((new DateTimeImmutable)->modify('-' . random_int(60, 400) . ' days'))
                ->setUpdatedAt($i % 15 ? (new DateTimeImmutable)->modify('-' . random_int(1, 60) . ' days') : null)
                ->setImageProfile(null)
                ->setBiography(null);

            $manager->persist($user);
        }

        $admin = new User;

        $admin->setEmail($faker->email())
            ->setRoles($superAdminRole)
            ->setPassword($this->hasher->hashPassword($admin, 'magic'))
            ->setUsername($faker->userName)
            ->setCreatedAt((new DateTimeImmutable)->modify('-' . random_int(60, 400) . ' days'))
            ->setUpdatedAt($i % 15 ? (new DateTimeImmutable)->modify('-' . random_int(1, 60) . ' days') : null)
            ->setImageProfile(null)
            ->setBiography(null);

        $manager->persist($admin);

        $manager->flush();
    }
}
