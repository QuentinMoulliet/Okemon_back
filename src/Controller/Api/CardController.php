<?php

namespace App\Controller\Api;

use App\Entity\Card;
use App\Repository\UserRepository;
use App\Repository\CardRepository;
use App\Repository\CommentRepository;
use App\Repository\ReviewRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CardController extends AbstractController
{
    /**
     * @Route("/api/cartes/ajout/collection/{api_id}/{user_id}", name="app_api_cardCollection_add")
     * Method to add a card in collection
     * @param string $api_id
     * @param int $user_id
     * @param UserRepository $userRepository
     * @param CardRepository $cardRepository
     * @return JsonResponse
     * 
     */
    public function addCollection(string $api_id, int $user_id, UserRepository $userRepository, CardRepository $cardRepository): JsonResponse
    {   
        // Get the user and set card properties
        $user = $userRepository->find($user_id);

        $card = new Card();
        $card->setApiId($api_id);
        $card->setOwn(1);
        $card->setWish(0);
        $card->addUser($user);

        // "true" as second parameter means persist and flush simultaneously
        $cardRepository->add($card, true);

        return $this->json(["message" => "Carte bien ajouté en collection"],Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/cartes/ajout/wishlist/{api_id}/{user_id}", name="app_api_cardWishlist_add")
     * Method to add a card in wishlist
     * @param string $api_id
     * @param int $user_id
     * @param UserRepository $userRepository
     * @param CardRepository $cardRepository
     * @return JsonResponse
     */
    public function addWishlist(string $api_id, int $user_id, UserRepository $userRepository, CardRepository $cardRepository): JsonResponse
    {   
        // Get the user and set card properties
        $user = $userRepository->find($user_id);

        $card = new Card();
        $card->setApiId($api_id);
        $card->setOwn(0);
        $card->setWish(1);
        $card->addUser($user);

        // "true" as second parameter means persist and flush simultaneously
        $cardRepository->add($card, true);

        return $this->json(["message" => "Carte bien ajouté en wishlist"],Response::HTTP_CREATED);
    }

     /**
     * @Route("/api/cartes/suppression/collection/{api_id}/{user_id}", name="app_api_cardCollection_delete", methods={"DELETE"})
     * Method to delete a card from collection
     * @param string $api_id
     * @param int $user_id
     * @param UserRepository $userRepository
     * @param CardRepository $cardRepository
     * @return JsonResponse
     */
    public function deleteCardFromCollection(string $api_id, int $user_id, UserRepository $userRepository, CardRepository $cardRepository)
    {   
        $card = $cardRepository->findCardByApiIdAndUserIdInCollection($api_id, $user_id);
        $cardId = $card[0]['id'];
        $cardToDelete = $cardRepository->find($cardId);
        $cardRepository->remove($cardToDelete, true);

        return $this->json(["message" => "La carte bien été supprimée de la collection"],Response::HTTP_OK);

    }

    /**
     * @Route("/api/cartes/suppression/wishlist/{api_id}/{user_id}", name="app_api_cardWishlist_delete", methods={"DELETE"})
     * Method to delete a card from wishlist
     * @param string $api_id
     * @param int $user_id
     * @param CardRepository $cardRepository
     * @return JsonResponse
     */
    public function deleteCardFromWishlist(string $api_id, int $user_id,CardRepository $cardRepository)
    {   
        $card = $cardRepository->findCardByApiIdAndUserIdInWishlist($api_id, $user_id);
        $cardId = $card[0]['id'];
        $cardToDelete = $cardRepository->find($cardId);
        $cardRepository->remove($cardToDelete, true);
        return $this->json(["message" => "La carte bien été supprimée de la wishlist"],Response::HTTP_OK);

    }

    /**
     * @Route("/api/carte/details/{apiId}", name="app_card_detail", methods={"GET"})
     * Method to find the details of a card by api_id such as list of owner and wisher (and the count of them), reviews and comments
     * @param UserRepository $userRepository
     * @param string $apiId
     * @param ReviewRepository $reviewRepository
     * @param CommentRepository $commentRepository
     * @return JsonResponse
     */
    public function findDetailsCardByApiID(UserRepository $userRepository, string $apiId, ReviewRepository $reviewRepository, CommentRepository $commentRepository): JsonResponse
    {
        $countOwner = $userRepository->countOwnerFromApiId($apiId);
        $countWisher = $userRepository->countWisherFromApiId($apiId);
        $cardReview = $reviewRepository->findAllReviewsByCardApiID($apiId);
        $cardComment = $commentRepository->findAllCommentsByCardApiID($apiId);
        
        $data = [
            'owners' => $userRepository->findOwnerFromApiId($apiId),
            'wishers' => $userRepository->findWisherFromApiId($apiId),
            'countOwner' => $countOwner[0][1],
            'countWisher' => $countWisher[0][1],
            'reviews' => $cardReview,
            'comments' => $cardComment,
            
        ];
        
        return $this->json($data, Response::HTTP_OK, [], ['groups' => 'users']   );
    }
}
