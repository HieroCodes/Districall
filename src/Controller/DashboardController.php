<?php

namespace App\Controller;
use App\Entity\Article;
use App\Form\ArticleType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
        #[Route('/dashboard', name: 'dashboard')]
    public function index(): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        return $this->render('dashboard/index.html.twig', [
            'articleForm' => $form->createView(),
        ]);
    }

}
