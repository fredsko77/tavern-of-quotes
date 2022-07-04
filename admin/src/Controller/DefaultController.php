<?php

namespace Admin\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin', name: 'admin_')]
class DefaultController extends AbstractController
{
    #[Route('', name: 'default')]
    public function default(Request $request): Response
    {
        return $this->render('@Admin/base.html.twig');
    }
}
