<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Rating;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class RatingController extends AbstractController
{
    #[Route('/article/rate/{id}', name: 'article_rate', methods: ['POST'])]
    public function rate(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 
        $rateValue = $request->request->getInt('rate');
        if ($rateValue < 0 || $rateValue > 10) {
            // Handle the error appropriately
            return $this->json(['message' => 'Note invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Verifions si l'utilisateur a déjà noté cet article
        $existingRating = $entityManager->getRepository(Rating::class)->findOneBy([
            'article' => $article,
            'user' => $user,
        ]);

        if ($existingRating) {
            //Permettre au User d'update sa note
            return $this->json(['message' => 'Vous avez déjà noté cet article'], Response::HTTP_FORBIDDEN);
        }

        $rating = new Rating();
        $rating->setArticle($article);
        $rating->setUser($user);
        $rating->setRate($rateValue);

        $entityManager->persist($rating);
        $entityManager->flush();

        return $this->json(['message' => 'Merci d\'avoir noté cet article']);
    }
}
