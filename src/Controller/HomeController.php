<?php

namespace App\Controller;
use App\Form\RatingType;
use App\Entity\Rating;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ArticleRepository $articleRepository,EntityManagerInterface $entityManager): Response
    {
         // Récupère tous les articles
        $articles = $articleRepository->findAll();
        $ratingForms = [];
        $ratingAverages = [];


        foreach ($articles as $article) {
            $form = $this->createForm(RatingType::class);
            $ratingForms[$article->getId()] = $form->createView();
            //
            $average = $entityManager->getRepository(Rating::class)->getAverageRating($article);
            $ratingAverages[$article->getId()] = $average;
        }

        return $this->render('home/index.html.twig', [
            'articles' => $articles,
            'ratingForms' => $ratingForms,
            'ratingAverages' => $ratingAverages,
        ]);
    }
}
