<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/api/utilisateurs", name="app_users", methods={"GET"})
     * Method to get all users
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function list(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'users']);
    }

    /**
     * @Route("/api/utilisateurs/{id}", name="app_users_show", methods={"GET"})
     * Method to get one user with his wishlist and collection and 4 random cards
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function show(UserRepository $userRepository, int $id): JsonResponse
    {
        $collectionCard = $userRepository->findWishlistById($id);
        $wishlistCard = $userRepository->findCollectionById($id);
        shuffle($collectionCard);
        $randomCollectionCard = array_slice($collectionCard, 0, 4);
        shuffle($wishlistCard);
        $randomWishlistCard = array_slice($wishlistCard, 0, 4);

        $data = [
            'user' => $userRepository->find($id),
            'userCountWishlist' => $userRepository->findUserNumberCardsInWishlist($id),
            'userCountCollection' => $userRepository->findUserNumberCardsInCollection($id),
            'userWishlist' => $userRepository->findWishlistById($id),
            'userCollection' => $userRepository->findCollectionById($id),
            'randomCollectionCard'  => $randomCollectionCard,
            'randomWishlistCard' => $randomWishlistCard
        ];

        if (!$data['user']) {
            return $this->json([
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($data, Response::HTTP_OK, [], ['groups' => 'users']);
    }

    /**
     * @Route("/api/utilisateurs/recherche/{search}", name="app_users_search", methods={"GET"})
     * Method to fin one user by nickname
     * @param UserRepository $userRepository
     * @param string $search
     * @return JsonResponse
     */
    public function showByNickname(UserRepository $userRepository, string $search): JsonResponse
    {
        $user = $userRepository->findByNickname($search);

        if (!$user) {
            return $this->json([
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'users']);
    }



    /**
     * @Route("/api/utilisateurs/ajout", name="app_users_create" , methods={"POST"})
     * Method to add a user
     * @param ValidatorInterface $validator
     * @param Request $request
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param UserPasswordHasherInterface $passwordhasher
     * @return JsonResponse
     */
    public function new(ValidatorInterface $validator, Request $request, UserRepository $userRepository, SerializerInterface $serializer, UserPasswordHasherInterface $passwordhasher)
    {
        // Get request content
        $content = $request->getContent();

        $user = new User;
        $contentArray = json_decode($content, true);

        $plaintextPassword = $contentArray["password"];

        // Validation of the password lenght, special character and uppercase character
        if (strlen($plaintextPassword) < 8) {
            return $this->json([
                'error' => 'Votre mot de passe doit contenir au moins 8 caractères'
            ], Response::HTTP_UNAUTHORIZED);
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $plaintextPassword)) {
            return $this->json([
                'error' => 'Votre mot de passe doit contenir au moins un caractère spécial'
            ], Response::HTTP_UNAUTHORIZED);
        }
        if (!preg_match('/[A-Z]/', $plaintextPassword)) {
            return $this->json([
                'error' => 'Votre mot de passe doit contenir au moins une majuscule'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $hashPassword = $passwordhasher->hashPassword(
            $user,
            $plaintextPassword
        );

        $contentArray["password"] = $hashPassword;
        $content = json_encode($contentArray);

        // I need to translate json in symfo entity
        try {
            // If json is not conform I get an exception
            $user = $serializer->deserialize($content, User::class, "json");
        } catch (NotEncodableValueException $err) {
            // Send back a json when there is an exception
            return $this->json(["message" => "JSON invalide"], Response::HTTP_BAD_REQUEST);
        }

        // Check if there is errors in validator
        $errors = $validator->validate($user);

        if (count($errors) > 0) {

            $errorsString = $errors[0]->getMessageTemplate();

            return new Response(($errorsString), Response::HTTP_BAD_REQUEST);
        }

        // Add to database
        $userRepository->add($user, true);

        // * premier param = message de succès
        // * deuxième param = code 201
        // * troisième param = header avec la location de la nouvelle ressource (NORME REST)
        return $this->json(["message" => "creation successfull"], Response::HTTP_CREATED, ["Location" => ($this->generateUrl("app_users_create", ["id" => $user->getId()]))]);
    }

    /**
     * @Route("/api/classement", name="app_users_ranking", methods={"GET"})
     * Method to get all users sorted by number of cards
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function ranking(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAllUserSortByNumberOfCards();

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'users']);
    }

    /**
     * @Route("/api/wishlist/{id}", name="app_users_wish", methods={"GET"})
     * Method to get all cards in wishlist of one user
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function findWishlist(UserRepository $userRepository, int $id): JsonResponse
    {
        $users = $userRepository->findWishlistById($id);

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'users']);
    }

    /**
     * @Route("/api/collection/{id}", name="app_users_collection", methods={"GET"})
     * Method to get all cards in collection of one user
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function findCollection(UserRepository $userRepository, int $id): JsonResponse
    {
        $users = $userRepository->findCollectionById($id);

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'users']);
    }

    /**
     * @Route("/api/articles/utilisateurs/{id}", name="app_users_reviews", methods={"GET"})
     * Method to get all reviews of one user by id
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function findUserReviews(UserRepository $userRepository, int $id): JsonResponse
    {
        $users = $userRepository->findAllReviewsById($id);

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'users']);
    }

    /**
     * @Route("/api/commentaires/utilisateurs/{id}", name="app_users_comments", methods={"GET"})
     * Method to get all comments of one user by id
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function findUserComments(UserRepository $userRepository, int $id): JsonResponse
    {
        $users = $userRepository->findAllCommentsById($id);

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'users']);
    }

    /**
     * @Route("/api/utilisateurs/modification/pseudo/{id}", name="app_users_nickname_edit", methods={"PUT"})
     * Method to update nickname of one user
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateNickname(ValidatorInterface $validator, Request $request, UserRepository $userRepository, int $id)
    {
        $content = $request->getContent();

        $user = $userRepository->find($id);

        $contentArray = json_decode($content, true);

        $newNickname = $contentArray["nickname"];

        $user->setNickname($newNickname);

        // Check if there is errors in validator
        $errors = $validator->validate($user);

        if (count($errors) > 0) {

            $errorsString = $errors[0]->getMessageTemplate();

            return new Response(($errorsString), Response::HTTP_BAD_REQUEST);
        }

        $userRepository->add($user, true);

        return $this->json(["message" => "pseudo modifié"], Response::HTTP_OK);
    }

    /**
     * @Route("/api/utilisateurs/modification/email/{id}", name="app_users_email_edit", methods={"PUT"})
     * Method to update email of one user
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateEmail(ValidatorInterface $validator, Request $request, UserRepository $userRepository, int $id)
    {
        $content = $request->getContent();

        $user = $userRepository->find($id);

        $contentArray = json_decode($content, true);

        $newEmail = $contentArray["email"];

        $user->setEmail($newEmail);

        // Check if there is errors in validator
        $errors = $validator->validate($user);

        if (count($errors) > 0) {

            $errorsString = $errors[0]->getMessageTemplate();

            return new Response(($errorsString), Response::HTTP_BAD_REQUEST);
        }

        $userRepository->add($user, true);

        return $this->json(["message" => "email modifié"], Response::HTTP_OK);
    }

    /**
     * @Route("/api/utilisateurs/modification/mot_de_passe/{id}", name="app_users_password_edit", methods={"PUT"})
     * Method to update password of one user
     * @param UserPasswordHasherInterface $passwordhasher
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updatePassword(UserPasswordHasherInterface $passwordhasher, Request $request, UserRepository $userRepository, int $id)
    {
        $content = $request->getContent();

        $user = $userRepository->find($id);

        $contentArray = json_decode($content, true);

        $newPassword = $contentArray["password"];

        // Validation of the password lenght, special character and uppercase character
        if (strlen($newPassword) < 8) {
            return $this->json([
                'error' => 'Votre mot de passe doit contenir au moins 8 caractères'
            ], Response::HTTP_UNAUTHORIZED);
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
            return $this->json([
                'error' => 'Votre mot de passe doit contenir au moins un caractère spécial'
            ], Response::HTTP_UNAUTHORIZED);
        }
        if (!preg_match('/[A-Z]/', $newPassword)) {
            return $this->json([
                'error' => 'Votre mot de passe doit contenir au moins une majuscule'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $hashPassword = $passwordhasher->hashPassword(
            $user,
            $newPassword
        );

        $user->setPassword($hashPassword);

        $userRepository->add($user, true);

        return $this->json(["message" => "mot de passe modifié"], Response::HTTP_OK);
    }

    /**
     * @Route("/api/utilisateurs/modification/age/{id}", name="app_users_age_edit", methods={"PUT"})
     * Method to update age of one user
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateAge(ValidatorInterface $validator, Request $request, UserRepository $userRepository, int $id)
    {
        $content = $request->getContent();

        $user = $userRepository->find($id);

        $contentArray = json_decode($content, true);

        $newAge = $contentArray["age"];

        $user->setAge($newAge);

        // Check if there is errors in validator
        $errors = $validator->validate($user);

        if (count($errors) > 0) {

            $errorsString = $errors[0]->getMessageTemplate();

            return new Response(($errorsString), Response::HTTP_BAD_REQUEST);
        }

        $userRepository->add($user, true);

        return $this->json(["message" => "age modifié"], Response::HTTP_OK);
    }

    /**
     * @Route("/api/utilisateurs/modification/pays/{id}", name="app_users_country_edit", methods={"PUT"})
     * Method to update country of one user
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateCountry(ValidatorInterface $validator, Request $request, UserRepository $userRepository, int $id)
    {
        $content = $request->getContent();

        $user = $userRepository->find($id);

        $contentArray = json_decode($content, true);

        $newCountry = $contentArray["country"];

        $user->setCountry($newCountry);

        // Check if there is errors in validator
        $errors = $validator->validate($user);

        if (count($errors) > 0) {

            $errorsString = $errors[0]->getMessageTemplate();

            return new Response(($errorsString), Response::HTTP_BAD_REQUEST);
        }

        $userRepository->add($user, true);

        return $this->json(["message" => "pays modifié"], Response::HTTP_OK);
    }

    /**
     * @Route("/api/utilisateurs/modification/description/{id}", name="app_users_description_edit", methods={"PUT"})
     * Method to update description of one user
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateDescription(ValidatorInterface $validator, Request $request, UserRepository $userRepository, int $id)
    {
        $content = $request->getContent();

        $user = $userRepository->find($id);

        $contentArray = json_decode($content, true);

        $newDescription = $contentArray["description"];

        $user->setDescription($newDescription);

        // Check if there is errors in validator
        $errors = $validator->validate($user);

        if (count($errors) > 0) {

            $errorsString = $errors[0]->getMessageTemplate();

            return new Response(($errorsString), Response::HTTP_BAD_REQUEST);
        }

        $userRepository->add($user, true);

        return $this->json(["message" => "modification de la description"], Response::HTTP_OK);
    }
    /**
     * @Route("/api/utilisateurs/modification/phrase/{id}", name="app_users_catchphrase_edit", methods={"PUT"})
     * Method to update catchphrase of one user
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateCatchphrase(ValidatorInterface $validator, Request $request, UserRepository $userRepository, int $id)
    {
        $content = $request->getContent();

        $user = $userRepository->find($id);

        $contentArray = json_decode($content, true);

        $newCatchphrase = $contentArray["catchphrase"];

        $user->setCatchphrase($newCatchphrase);

        // Check if there is errors in validator
        $errors = $validator->validate($user);

        if (count($errors) > 0) {

            $errorsString = $errors[0]->getMessageTemplate();

            return new Response(($errorsString), Response::HTTP_BAD_REQUEST);
        }

        $userRepository->add($user, true);

        return $this->json(["message" => "modification de la phrase d'accroche"], Response::HTTP_OK);
    }

    /**
     * @Route("/api/utilisateurs/modification/image/{id}", name="app_users_image_edit", methods={"PUT"})
     * Method to update image of one user
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateImage(Request $request, UserRepository $userRepository, int $id)
    {
        $content = $request->getContent();

        $user = $userRepository->find($id);

        $contentArray = json_decode($content, true);

        $newImage = $contentArray["image"];

        $user->setImage($newImage); 

        $userRepository->add($user, true);

        return $this->json(["message" => "modification de l'image"], Response::HTTP_OK);
    }

    /**
     * @Route("/api/utilisateurs/modification/contact/{id}", name="app_users_contact_edit", methods={"PUT"})
     * Method to update contact of one user
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateContact(ValidatorInterface $validator, Request $request, UserRepository $userRepository, int $id)
    {
        $content = $request->getContent();

        $user = $userRepository->find($id);

        $contentArray = json_decode($content, true);

        $newContact = $contentArray["contact"];

        $user->setContact($newContact);

        // Check if there is errors in validator
        $errors = $validator->validate($user);

        if (count($errors) > 0) {

            $errorsString = $errors[0]->getMessageTemplate();

            return new Response(($errorsString), Response::HTTP_BAD_REQUEST);
        }

        $userRepository->add($user, true);

        return $this->json(["message" => "modification du lien ok"], Response::HTTP_OK);
    }

    /**
     * @Route("/api/utilisateurs/suppression/{id}", name="app_users_delete", methods={"DELETE"})
     * Method to delete one user
     * @param int $id
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function deleteUser(int $id, UserRepository $userRepository)
    {
        $user = $userRepository->find($id);
        $userRepository->remove($user, true);

        return $this->json(["message" => "Utilisateur supprimé"], Response::HTTP_OK);
    }
}
