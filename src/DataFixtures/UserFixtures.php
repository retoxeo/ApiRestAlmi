<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Cambio aquí

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher; // Cambio aquí

    // Inyectamos el servicio del password hasher en el constructor
    public function __construct(UserPasswordHasherInterface $passwordHasher) // Cambio aquí
    {
        $this->passwordHasher = $passwordHasher; // Cambio aquí
    }

    public function load(ObjectManager $manager): void
    {
        // Crear usuario 1: Telefonía
        $user1 = new User();
        $user1->setEmail('telefonia@gmail.com');
        // Hasheamos la contraseña antes de guardarla
        $password1 = $this->passwordHasher->hashPassword($user1, 'Almi123'); // Cambio aquí
        $user1->setPassword($password1);
        $user1->setRoles(['ROLE_TELEFONIA']);
        $manager->persist($user1);

        // Crear usuario 2: Consola
        $user2 = new User();
        $user2->setEmail('consola@gmail.com');
        // Hasheamos la contraseña antes de guardarla
        $password2 = $this->passwordHasher->hashPassword($user2, 'Almi123'); // Cambio aquí
        $user2->setPassword($password2);
        $user2->setRoles(['ROLE_CONSOLAS']);
        $manager->persist($user2);

        // Guardar en la base de datos
        $manager->flush();
    }
}

