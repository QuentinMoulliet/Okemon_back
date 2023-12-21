<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/utilisateurs")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="app_back_user_index", methods={"GET"})
     * Index of users in back office
     * @param UserRepository $userRepository
     * @return Response
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('back/user/index.html.twig', [
            'users' => $userRepository->findAllOrderByDesc(),
        ]);
    }

    // /**
    //  * @Route("/new", name="app_back_user_new", methods={"GET", "POST"})
    //  */
    // public function new(Request $request, UserRepository $userRepository): Response
    // {
    //     $user = new User();
    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $userRepository->add($user, true);

    //         return $this->redirectToRoute('app_back_user_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('back/user/new.html.twig', [
    //         'user' => $user,
    //         'form' => $form,
    //     ]);
    // }

    /**
     * @Route("/{id}", name="app_back_user_show", methods={"GET"})
     * Show a user
     * @param User $user
     * @param UserRepository $userRepository
     * @return Response
     */
    public function show(User $user, UserRepository $userRepository): Response
    {
        return $this->render('back/user/show.html.twig', [
            'user' => $user,
            'userCollection' => $userRepository->findUserNumberCardsInCollection($user),
            'userWishlist' => $userRepository->findUserNumberCardsInWishlist($user),
        ]);
    }

    /**
     * @Route("/{id}/modification", name="app_back_user_edit", methods={"GET", "POST"})
     * Edit a user
     * @param Request $request
     * @param User $user
     * @param UserRepository $userRepository
     * @return Response
     */
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->add($user, true);
            $this->addFlash("warning","L'utilisateur «". $user->getNickname()."» à bien été modifié.");
            return $this->redirectToRoute('app_back_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_back_user_delete", methods={"POST"})
     * Delete a user
     * @param Request $request
     * @param User $user
     * @param UserRepository $userRepository
     * @return Response
     */
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
            $this->addFlash("success","L'utilisateur «". $user->getNickname()."» à bien été supprimé.");
        }

        return $this->redirectToRoute('app_back_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/suppression/{id}", name="app_back_user_delete_from_main", methods={"POST"})
     * Delete a user from main page
     * @param Request $request
     * @param User $user
     * @param UserRepository $userRepository
     * @return Response
     */
    public function deleteFromMain(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
            $this->addFlash("success","L'utilisateur «". $user->getNickname()."» à bien été supprimé.");
        }

        return $this->redirectToRoute('app_back_main', [], Response::HTTP_SEE_OTHER);
    }
}
