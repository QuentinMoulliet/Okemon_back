<?php

namespace App\Controller\Back;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/articles")
 */
class ReviewController extends AbstractController
{
    /**
     * @Route("/", name="app_back_review_index", methods={"GET"})
     */
    public function index(ReviewRepository $reviewRepository): Response
    {
        return $this->render('back/review/index.html.twig', [
            'reviews' => $reviewRepository->findAllOrderByDesc(),
        ]);
        
    }

    // /**
    //  * @Route("/new", name="app_back_review_new", methods={"GET", "POST"})
    //  */
    // public function new(Request $request, ReviewRepository $reviewRepository): Response
    // {
    //     $review = new Review();
    //     $form = $this->createForm(ReviewType::class, $review);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $reviewRepository->add($review, true);

    //         return $this->redirectToRoute('app_back_review_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('back/review/new.html.twig', [
    //         'review' => $review,
    //         'form' => $form,
    //     ]);
    // }

    /**
     * @Route("/{id}", name="app_back_review_show", methods={"GET"})
     * Show a review
     * @param Review $review
     * @return Response
     */
    public function show(Review $review): Response
    {
        return $this->render('back/review/show.html.twig', [
            'review' => $review,
        ]);
    }

    /**
     * @Route("/{id}/modification", name="app_back_review_edit", methods={"GET", "POST"})
     * Edit a review
     * @param Request $request
     * @param Review $review
     * @param ReviewRepository $reviewRepository
     * @return Response
     */
    public function edit(Request $request, Review $review, ReviewRepository $reviewRepository): Response
    {
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reviewRepository->add($review, true);
            $this->addFlash("warning","L'article «". $review->getTitle()."» à bien été modifié.");
            return $this->redirectToRoute('app_back_review_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/review/edit.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_back_review_delete", methods={"POST"})
     * @param Request $request
     * @param Review $review
     * @param ReviewRepository $reviewRepository
     * @return Response
     */
    public function delete(Request $request, Review $review, ReviewRepository $reviewRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$review->getId(), $request->request->get('_token'))) {
            $reviewRepository->remove($review, true);
            $this->addFlash("success","L'article «". $review->getTitle()."» à bien été supprimé.");
        }

        return $this->redirectToRoute('app_back_review_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * @Route("/suppression/{id}", name="app_back_review_delete_from_main", methods={"POST"})
     * @param Request $request
     * @param Review $review
     * @param ReviewRepository $reviewRepository
     * @return Response
     */
    public function deleteFromMain(Request $request, Review $review, ReviewRepository $reviewRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$review->getId(), $request->request->get('_token'))) {
            $reviewRepository->remove($review, true);
            $this->addFlash("success","L'article «". $review->getTitle()."» à bien été supprimé.");
        }

        return $this->redirectToRoute('app_back_main', [], Response::HTTP_SEE_OTHER);
    }

}
