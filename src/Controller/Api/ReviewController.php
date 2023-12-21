<?php

namespace App\Controller\Api;

use App\Entity\Review;
use App\Repository\CardRepository;
use App\Repository\UserRepository;
use App\Repository\ReviewRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends AbstractController
{
    /**
     * @Route("/api/articles", name="app_reviews", methods={"GET"})
     * Method to get all reviews
     * @param ReviewRepository $reviewRepository
     * @return JsonResponse
     */
    public function list(ReviewRepository $reviewRepository): JsonResponse
    {
        $reviews = $reviewRepository->findAll();

        return $this->json($reviews, Response::HTTP_OK, [], ['groups' => 'reviews']   );
    }

    /**
     * @Route("/api/articles/{id}", name="app_reviews_show", methods={"GET"})
     * Method to get one review
     * @param ReviewRepository $reviewRepository
     * @param int $id
     */
    public function show(ReviewRepository $reviewRepository, int $id): JsonResponse
    {
        $review = $reviewRepository->find($id);

        if (!$review) {
            return $this->json([
                'error' => 'Article non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($review, Response::HTTP_OK, [], ['groups' => 'reviews']);
    }

    /**
     * @Route("/api/articles/ajout/{card_id}/{user_id}", name="app_api_review_add", methods={"POST"})
     * Method to add a review
     * @param CardRepository $cardRepository
     * @param Request $request
     * @param int $user_id
     * @param int $card_id
     * @param UserRepository $userRepository
     * @param ReviewRepository $reviewRepository
     * @return JsonResponse
     */
    public function addReview(CardRepository $cardRepository, Request $request, int $user_id, int $card_id, UserRepository $userRepository, ReviewRepository $reviewRepository): JsonResponse
    {   
        $content = $request->getContent();
        $user = $userRepository->find($user_id);
        $card = $cardRepository->find($card_id);

        
        $contentArray = json_decode($content, true);
        $reviewTitle = $contentArray["title"];
        $reviewContent = $contentArray["content"];

        
        $review = new Review();
        $review->setTitle($reviewTitle);
        $review->setContent($reviewContent);
        $review->setCard($card);
        $review->setUser($user);


        // "true" as second parameter means persist and flush simultaneously
        $reviewRepository->add($review, true);

        return $this->json(["message" => "Article ajouté"],Response::HTTP_OK);
    }

    /**
     * @Route("/api/articles/modification/titre/{id}", name="app_review_title_edit", methods={"PUT"})
     * Method to update the title of a review
     * @param Request $request
     * @param ReviewRepository $reviewRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateTitle(Request $request, ReviewRepository $reviewRepository, int $id)
    {
        $content = $request->getContent();

        $review = $reviewRepository->find($id);

        $contentArray = json_decode($content, true);

        $newTitle = $contentArray["title"];

        $review->setTitle($newTitle);

        $reviewRepository->add($review,true);

        return $this->json(["message" => "Titre modifié"],Response::HTTP_OK);

    }

    /**
     * @Route("/api/articles/modification/contenu/{id}", name="app_review_content_edit", methods={"PUT"})
     * Method to update the content of a review
     * @param Request $request
     * @param ReviewRepository $reviewRepository
     * @param int $id
     * @return JsonResponse
     *
    */
    public function updateContent(Request $request, ReviewRepository $reviewRepository, int $id)
    {
        $content = $request->getContent();

        $review = $reviewRepository->find($id);

        $contentArray = json_decode($content, true);

        $newContent = $contentArray["content"];

        $review->setContent($newContent);

        $reviewRepository->add($review,true);

        return $this->json(["message" => "Contenu modifié"],Response::HTTP_OK);

    }

    /**
     * @Route("/api/articles/suppression/{id}", name="app_api_review_delete", methods={"DELETE"})
     * Method to delete a review
     * @param int $id
     * @param ReviewRepository $reviewRepository
     * @return JsonResponse
     */
    public function deleteReview(int $id, ReviewRepository $reviewRepository)
    {   
        $review = $reviewRepository->find($id);
        $reviewRepository->remove($review, true);

        return $this->json(["message" => "Article supprimé"],Response::HTTP_OK);

    }
}
