<?php

namespace App\Controller\Back;

use App\Repository\CommentRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="app_back_main")
     */
    public function index(ReviewRepository $reviewRepository, CommentRepository $commentRepository, UserRepository $userRepository): Response
    {
        return $this->render('back/main/home.html.twig', [
            'controller_name' => 'MainController',
            'reviews' => $reviewRepository->findAllWithMaxResult(),
            'comments' => $commentRepository->findAllWithMaxResult(),
            'users' => $userRepository->findAllWithMaxResult(),
        ]);
    }
}
