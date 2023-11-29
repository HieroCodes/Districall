<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\RatingType;
use App\Entity\Rating;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArticleRepository;

class RatingController extends AbstractController
{
    #[Route('/article/rate/{id}', name: 'article_rate', methods: ['POST'])]
    public function rate(Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, $id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour noter un article.');
            return $this->redirectToRoute('app_login');
        }

        $article = $articleRepository->find($id);
        if (!$article) {
            $this->addFlash('error', 'Cet article n\'existe pas.');
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(RatingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($form->getData());
            $rateValue = $form->get('rate')->getData();

            // Rechercher une note existante
            $existingRating = $entityManager->getRepository(Rating::class)->findOneBy([
                'article' => $article,
                'user' => $user,
            ]);

            if ($existingRating) {
                $existingRating->setRate($rateValue);
                $entityManager->flush();
                $this->addFlash('success', 'Votre note a été mise à jour.');
            } else {
                // Créer une nouvelle note
                $rating = new Rating();
                $rating->setArticle($article);
                $rating->setUser($user);
                $rating->setRate($rateValue);

                $entityManager->persist($rating);
                $entityManager->flush();
                $this->addFlash('success', 'Votre note a été enregistrée.');
            }
            
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('home'));
        }

        // Gérer les erreurs de formulaire
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('home'));
    }
}
