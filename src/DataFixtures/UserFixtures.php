<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
public function __construct(private UserPasswordHasherInterface $hasher)
{
}

public function load(ObjectManager $manager): void
{
$admin = new User();
$admin->setEmail('admin@example.com');
$admin->setUserName('admin');
$admin->setRoles(['ROLE_ADMIN']);
$admin->setPassword(
$this->hasher->hashPassword($admin, 'your_secure_password')
);

$manager->persist($admin);
$manager->flush();
}
}