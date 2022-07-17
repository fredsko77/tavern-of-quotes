<?php

namespace Admin\Controller;

use App\Entity\Arc;
use Admin\Form\ArcEditType;
use Admin\Form\ArcCreateType;
use Admin\Service\ArcService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/arc', name: 'admin_arc_')]
class ArcController extends AbstractController
{

    public function __construct(
        private ArcService $service
    ) {
    }

    #[Route(
        '',
        name: 'index',
        methods: ['GET']
    )]
    public function index(): Response
    {

        return $this->render('@Admin/arc/index.html.twig', $this->service->index());
    }

    #[Route(
        '/create',
        name: 'create',
        methods: ['GET', 'POST']
    )]
    public function create(Request $request): Response
    {
        $arc = new Arc;

        $form = $this->createForm(ArcCreateType::class, $arc);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->store(
                $arc,
                $form->get('uploadedCsv')->getData()
            );

            return $this->redirectToRoute('admin_arc_edit', [
                'id' => $arc->getId(),
            ]);
        }
        return $this->renderForm('@Admin/arc/create.html.twig', compact('form'));
    }

    #[Route(
        '/{id}',
        name: 'edit',
        methods: ['GET', 'POST']
    )]
    public function edit(Arc $arc, Request $request): Response
    {
        $form = $this->createForm(ArcEditType::class, $arc);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->store(
                $arc,
                $form->get('uploadedCsv')->getData(),
                $form->get('uploadedImage')->getData()
            );

            return $this->redirectToRoute('admin_arc_edit', [
                'id' => $arc->getId()
            ]);
        }
        return $this->renderForm('@Admin/arc/edit.html.twig', compact('form'));
    }

    #[Route(
        '/{id}/delete',
        name: 'delete',
        methods: ['GET', 'POST']
    )]
    public function delete(Arc $arc): RedirectResponse
    {
        $this->service->delete($arc);

        return $this->redirectToRoute('admin_arc_index');
    }

    #[Route(
        '/{id}/quotes',
        name: 'quotes',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    public function quotes(Arc $arc, Request $request): Response
    {
        return $this->render('@Admin/quote/index.html.twig', $this->service->quotes($arc, $request));
    }
}
