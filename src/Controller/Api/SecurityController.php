<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="app_api_login", methods={"POST"})
     * Method to login in front with API
     * @param Security $security
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function login(Security $security, Request $request, UserRepository $userRepository): Response
    {
        $user = $security->getUser();

        $status = $user->getStatus();
        
        if ($status > 1){
            return $this->json([
                'error' => 'Votre compte à été désactivé'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (null === $user) {
            return $this->json([
                'error' => 'Identifiants manquants',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userId = ($user->getId());

        // Count user wishlist & collection 

        $arrayOfUserCountCardCollection = $userRepository->findUserNumberCardsInCollection($userId);
        $userCountCardCollection = $arrayOfUserCountCardCollection[0][1];

        $arrayOfUserCountCardWishlist = $userRepository->findUserNumberCardsInWishlist($userId);
        $userCountCardWishlist = $arrayOfUserCountCardWishlist[0][1];

        // Get the collection of the user
        $collection = $userRepository->findCollectionById($userId);
        $wishlist = $userRepository->findWishlistById($userId);

        // 4 ramdom card for profil 

        $collectionCard = $userRepository->findWishlistById($userId);
        $wishlistCard = $userRepository->findCollectionById($userId);
        shuffle($collectionCard);
        $randomCollectionCard = array_slice($collectionCard, 0, 4);
        shuffle($wishlistCard);
        $randomWishlistCard = array_slice($wishlistCard,0,4);

        // Get content of $request in array and get the data of this array in variables
        $content = $request->toArray();

        $email = $content["username"];
        $password = $content["password"];

        // Create a HTTP client
        $client = HttpClient::create();

        // Data to send in JSON in request
        $data = [
            'email' => $email,
            'password' => $password
        ];

        // Convert this data in JSON
        $jsonData = json_encode($data);

        // Using $client to make POST request to login_check route, with $jsonData in body
        // Response for server online

        $response = $client->request('POST', 'https://quentinmoulliet-server.eddi.cloud/login_check', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $jsonData
        ]);

        // Response for local 
        // Quentin : 'http://localhost/3.Apotheose/Okemon_back/public/login_check'
        // Julien & Julien : 'http://localhost/Apo/projet-04-pokemon-card-social-network-back/public/login_check'

        // $response = $client->request('POST', 'http://localhost/Apo/projet-04-pokemon-card-social-network-back/public/login_check', [
        //     'headers' => [
        //         'Content-Type' => 'application/json'
        //     ],
        //     'body' => $jsonData
        // ]);

        // Get the request response which is the token
        $tokenJSON = $response->getContent();

        $token = (json_decode($tokenJSON));

        return $this->json([
          'nickname' => $user->getNickname(),
          'id' => $user->getId(),
          'age' => $user->getAge(),
          'country' => $user->getCountry(),
          'description' => $user->getDescription(),
          'catchphrase' => $user->getCatchphrase(),
          'image' => $user->getImage(),
          'status'=> $user->getStatus(),
          'contact' => $user->getContact(),
          'user'  => $user->getUserIdentifier(),
          "roles" => $user->getRoles(),
          'token' => $token,
          'user_number_card_collection' => $userCountCardCollection,
          'user_number_card_wishlist' => $userCountCardWishlist,
          'collection'=>$collection,
          'wishlist'=>$wishlist,
          'randomCollectionCard'  => $randomCollectionCard,
          'randomWishlistCard' => $randomWishlistCard
        ]);
    }
}
