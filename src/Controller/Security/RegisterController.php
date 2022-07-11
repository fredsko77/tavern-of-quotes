<?php

namespace App\Controller\Security;

use Admin\Service\UserService;
use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegisterController extends AbstractController
{

    public function __construct(
        private UserService $service
    ) {
    }

    /**
     * @param Request $request
     * 
     * @return Response
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_default');
        }

        $user = new User;
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->create($user, $form->get('plainPassword')->getData());

            return $this->redirectToRoute('app_login');
        }

        return $this->renderForm('auth/register.html.twig', compact('form'));
    }
}
