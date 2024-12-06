<?php

namespace App\EntityListener;

use App\Entity\AdminUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: AdminUser::class)]
#[AsEntityListener(event: Events::preUpdate, entity: AdminUser::class)]
class AdminUserListener
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function prePersist(AdminUser $adminUser): void
    {
        $this->encodePassword($adminUser);
    }

    public function preUpdate(AdminUser $adminUser): void
    {
        $this->encodePassword($adminUser);
    }

    private function encodePassword(AdminUser $adminUser): void
    {
        if ($adminUser->getPlainPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($adminUser, $adminUser->getPlainPassword());
            $adminUser->setPassword($hashedPassword);
            $adminUser->setPlainPassword(null);
        }
    }
}
