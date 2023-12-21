<?php

namespace App\Controller\Back;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/commentaires")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/", name="app_back_comment_index", methods={"GET"})
     * Index of comments in back office
     * @param CommentRepository $commentRepository
     * @return Response
     */
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('back/comment/index.html.twig', [
            'comments' => $commentRepository->findAllOrderByDesc(),
        ]);
    }

    // /**
    //  * @Route("/new", name="app_back_comment_new", methods={"GET", "POST"})
    //  */
    // public function new(Request $request, CommentRepository $commentRepository): Response
    // {
    //     $comment = new Comment();
    //     $form = $this->createForm(CommentType::class, $comment);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $commentRepository->add($comment, true);

    //         return $this->redirectToRoute('app_back_comment_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('back/comment/new.html.twig', [
    //         'comment' => $comment,
    //         'form' => $form,
    //     ]);
    // }

    /**
     * @Route("/{id}", name="app_back_comment_show", methods={"GET"})
     * Show a comment
     * @param Comment $comment
     * @return Response
     */
    public function show(Comment $comment): Response
    {
        return $this->render('back/comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    /**
     * @Route("/{id}/modification", name="app_back_comment_edit", methods={"GET", "POST"})
     * Edit a comment
     * @param Request $request
     * @param Comment $comment
     * @param CommentRepository $commentRepository
     * @return Response
     */
    public function edit(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentRepository->add($comment, true);
            $this->addFlash("warning","Le commentaire à bien été modifié");
            return $this->redirectToRoute('app_back_comment_index', [], Response::HTTP_SEE_OTHER);
            
        }

        return $this->renderForm('back/comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_back_comment_delete", methods={"POST"})
     * Delete a comment
     * @param Request $request
     * @param Comment $comment
     * @param CommentRepository $commentRepository
     * @return Response
     */
    public function delete(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $commentRepository->remove($comment, true);
            $this->addFlash("success","Le commentaire à bien été supprimé");
        }
        return $this->redirectToRoute('app_back_comment_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/suppression/{id}", name="app_back_comment_delete_from_main", methods={"POST"})
     * Delete a comment from main page
     * @param Request $request
     * @param Comment $comment
     * @param CommentRepository $commentRepository
     * @return Response
     */
    public function deleteFromMain(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $commentRepository->remove($comment, true);
            $this->addFlash("success","Le commentaire à bien été supprimé");
        }

        return $this->redirectToRoute('app_back_main', [], Response::HTTP_SEE_OTHER);
    }
}
