<?php

namespace Admin\Controller;

use Admin\Form\UserEditType;
use App\Entity\User;
use Admin\Form\UserCreateType;
use Admin\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/user', name: 'admin_user_')]
class UserController extends AbstractController
{

    public function __construct(
        private UserService $service
    ) {
    }

    #[Route(
        '',
        name: 'index',
        methods: ['GET']
    )]
    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->render('@Admin/user/index.html.twig', $this->service->index($request));
    }

    #[Route(
        '/create',
        name: 'create',
        methods: ['GET', 'POST']
    )]
    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $user = new User;

        $form = $this->createForm(UserCreateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->create($user, $form->get('plainPassword')->getData());

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->renderForm('@Admin/user/create.html.twig', compact('form'));
    }

    #[Route(
        '/{id}',
        name: 'edit',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+']
    )]
    /**
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function edit(User $user, Request $request): Response
    {
        $this->denyAccessUnlessGranted('user_admin_edit', $user);

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->edit($user, $form->get('plainPassword')->getData());

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->renderForm('user/edit.html.twig', compact('form', 'user'));
    }

    #[Route(
        '/{id}',
        name: 'delete',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    /**
     * @param User $user
     * @return Response
     */
    public function delete(User $user): Response
    {
        $this->denyAccessUnlessGranted('user_admin_edit', $user);

        $this->service->delete($user);

        return $this->redirectToRoute('admin_user_index');
    }
}
