<?php

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Repository\ReviewRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentController extends AbstractController
{
     /**
     * @Route("/api/commentaires", name="app_comments", methods={"GET"})
     * Method to get all comments
     * @param CommentRepository $commentRepository
     * @return JsonResponse
     */
    public function list(CommentRepository $commentRepository): JsonResponse
    {
        $comments = $commentRepository->findAll();

        return $this->json($comments, Response::HTTP_OK, [], ['groups' => 'comments']   );
    }

    /**
     * @Route("/api/commentaires/{id}", name="app_comments_show", methods={"GET"})
     * Method to get one comment
     * @param CommentRepository $commentRepository
     * @param int $id
     * @return JsonResponse
     */
    public function show(CommentRepository $commentRepository, int $id): JsonResponse
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            return $this->json([
                'error' => 'Commentaire non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($comment, Response::HTTP_OK, [], ['groups' => 'comments']);
    }

    /**
     * @Route("/api/commentaires/ajout/{review_id}/{user_id}", name="app_api_comment_add", methods={"POST"})
     * Method to add a comment
     * @param ValidatorInterface $validator
     * @param ReviewRepository $reviewRepository
     * @param Request $request
     * @param int $user_id
     * @param int $review_id
     * @param UserRepository $userRepository
     * @param CommentRepository $commentRepository
     * @return JsonResponse
     */
    public function addComment(ValidatorInterface $validator, ReviewRepository $reviewRepository, Request $request, int $user_id, int $review_id, UserRepository $userRepository, CommentRepository $commentRepository): JsonResponse
    {   
        $content = $request->getContent();
        $user = $userRepository->find($user_id);
        $review = $reviewRepository->find($review_id);

        $contentArray = json_decode($content, true);
        $commentContent = $contentArray["content"];

        $comment = new Comment();
        $comment->setContent($commentContent);
        $comment->setReview($review);
        $comment->setUser($user);

        // Check if there is errors in validator
        $errors = $validator->validate($user);
        
        if (count($errors) > 0) {
            
            $errorsString = (string) $errors;
            
            return new Response(($errorsString), Response::HTTP_BAD_REQUEST);
        }

        // "true" as second parameter means persist and flush simultaneously
        $commentRepository->add($comment, true);

        return $this->json(["message" => "Commentaire ajouté"],Response::HTTP_OK);
    }

    /**
     * @Route("/api/commentaires/modification/contenu/{id}", name="app_comment_content_edit", methods={"PUT"})
     * Method to update the content of a comment
     * @param Request $request
     * @param CommentRepository $commentRepository
     * @param int $id
     * @return JsonResponse
     */
    public function updateContent(Request $request, CommentRepository $commentRepository, int $id)
    {
        $content = $request->getContent();

        $comment = $commentRepository->find($id);

        $contentArray = json_decode($content, true);

        $newContent = $contentArray["content"];

        $comment->setContent($newContent);

        $commentRepository->add($comment,true);

        return $this->json(["message" => "contenu modifié"],Response::HTTP_OK);

    }

    /**
     * @Route("/api/commentaires/suppression/{id}", name="app_api_comment_delete", methods={"DELETE"})
     * Method to delete a comment
     * @param int $id
     * @param CommentRepository $commentRepository
     * @return JsonResponse
     */
    public function deleteComment(int $id, CommentRepository $commentRepository)
    {   
        $comment = $commentRepository->find($id);
        $commentRepository->remove($comment, true);

        return $this->json(["message" => "Commentaire supprimé"],Response::HTTP_OK);
    }
}
