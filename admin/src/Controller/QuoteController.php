<?php

namespace Admin\Controller;

use Admin\Form\QuoteType;
use App\Entity\Arc;
use App\Entity\Quote;
use Admin\Service\QuoteService;
use App\Entity\Answer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/quote', name: 'admin_quote_')]
class QuoteController extends AbstractController
{

    public function __construct(
        private QuoteService $service
    ) {
    }

    #[Route(
        '/{id}/create',
        name: 'create',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+']
    )]
    public function create(Arc $arc, Request $request): Response
    {
        $quote = new Quote;
        $answer = new Answer;
        $quote->addAnswer($answer);
        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->store($quote, $form->get('uploadedFile')->getData(), $arc);

            return $this->redirectToRoute('admin_quote_edit', [
                'id' => $quote->getId()
            ]);
        }

        return $this->renderForm('@Admin/quote/create.html.twig', compact('form'));
    }

    #[Route(
        '/{id}',
        name: 'edit',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+']
    )]
    public function edit(Quote $quote, Request $request): Response
    {
        if (is_null($quote->getAnswers())) {
            $answer = new Answer;
            $quote->addAnswer($answer);
        }

        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->store($quote, $form->get('uploadedFile')->getData());

            return $this->redirectToRoute('admin_quote_edit', [
                'id' => $quote->getId()
            ]);
        }

        return $this->renderForm('@Admin/quote/edit.html.twig', compact('form', 'quote'));
    }

    #[Route(
        '/{id}/delete',
        name: 'delete',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+']
    )]
    public function delete(Quote $quote, Request $request): RedirectResponse
    {
        $token = $request->request->get('_token');

        $arc = $quote->getArc();

        // 'delete-item' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete_quote_' . $quote->getId(), $token)) {
            $this->service->delete($quote);

            if (is_null($arc)) {
                return $this->redirectToRoute('admin_arc_index');
            }
        }

        return $this->redirectToRoute('admin_arc_quotes', [
            'id' => $arc->getId()
        ]);
    }
}
