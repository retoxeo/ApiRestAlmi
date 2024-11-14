<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/', name: 'login')]
    public function login(): Response
    {
        // Este método renderiza el formulario de login.
        // Symfony se encarga de la lógica de autenticación cuando se envía el formulario.
        return $this->render('api/login.html.twig');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // Symfony maneja el logout automáticamente
    }
}
