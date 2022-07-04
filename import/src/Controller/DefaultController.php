<?php

namespace Import\Controller;

use App\Entity\Arc;
use Import\Form\ArcType;
use Import\Service\ImportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/import', name: 'import_')]
class DefaultController extends AbstractController
{

    private ImportService $service;

    public function __construct(ImportService $service)
    {
        $this->service = $service;
    }

    #[Route('/arc', name: 'default')]
    public function default(Request $request): Response
    {
        $arc = new Arc;

        $form = $this->createForm(ArcType::class, $arc);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('uploadedFile')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            $this->service->importQuestions($file, $arc);
        }

        return $this->renderForm('@Import/random/index.html.twig', compact('form'));
    }
}
