<?php

namespace Admin\Controller;

use App\Entity\Arc;
use Admin\Form\ArcType;
use Admin\Service\ArcService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/arc', name: 'admin_arc_')]
class ArcController extends AbstractController
{

    public function __construct(
        private ArcService $service
    ) {
    }

    #[Route(
        '/import',
        name: 'import',
        methods: ['GET', 'POST']
    )]
    public function import(Request $request): Response
    {
        $arc = new Arc;

        $form = $this->createForm(ArcType::class, $arc);
        $form->handleRequest;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->importArc($arc);

            return $this->redirectToRoute('admin_default');
        }

        return $this->render('@Admin/arc/import.html.twig', compact('form'));
    }
}
