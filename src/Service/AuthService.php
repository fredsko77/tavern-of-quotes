<?php

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $manager,
        private UserRepository $user
    ) {
    }
}
