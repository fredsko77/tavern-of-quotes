<?php

namespace Admin\Controller;

use App\Entity\Arc;
use Admin\Form\ArcType;
use Import\Service\ImportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/admin/arc', name: 'admin_arc_')]
class ArcController extends AbstractController
{

    public function __construct(
        private ImportService $service
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
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('uploadedFile')->getData();

            if ($file instanceof UploadedFile) {
                // this condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploaded
                $this->service->importQuotes($file, $arc);
            }

            return $this->redirectToRoute('admin_arc_import');
        }

        return $this->renderForm('@Admin/arc/import.html.twig', compact('form'));
    }
}
