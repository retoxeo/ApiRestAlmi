<?php
// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    // Inyección de dependencias a través del constructor
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/create-telefonia-user', name: 'create_telefonia_user')]
    public function createTelefoniaUser(UserPasswordHasherInterface $passwordHasher): Response
    {
        // Crear el primer usuario con el rol 'ROLE_TELEFONIA'
        $user = new User();
        $user->setEmail('usuario_telefonia@example.com');  // Usar setEmail en lugar de setUsername
        $password = $passwordHasher->hashPassword($user, 'password_telefonia'); // Asegúrate de encriptar la contraseña
        $user->setPassword($password);
        $user->setRoles(['ROLE_USER', 'ROLE_TELEFONIA']); // Asignar el rol 'telefonia'

        // Guardar el usuario en la base de datos
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new Response('Usuario de telefonia creado con el rol ROLE_TELEFONIA');
    }

    #[Route('/create-consola-user', name: 'create_consola_user')]
    public function createConsolaUser(UserPasswordHasherInterface $passwordHasher): Response
    {
        // Crear el segundo usuario con el rol 'ROLE_CONSOLA'
        $user = new User();
        $user->setEmail('usuario_consola@example.com');  // Usar setEmail en lugar de setUsername
        $password = $passwordHasher->hashPassword($user, 'password_consola'); // Asegúrate de encriptar la contraseña
        $user->setPassword($password);
        $user->setRoles(['ROLE_USER', 'ROLE_CONSOLA']); // Asignar el rol 'consola'

        // Guardar el usuario en la base de datos
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new Response('Usuario de consola creado con el rol ROLE_CONSOLA');
    }
}
