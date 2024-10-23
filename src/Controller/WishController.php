<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WishController extends AbstractController
{
    #[Route('/wishes', name: 'list_wish', methods: ['GET'])]
    public function listWish(): Response
    {
        return $this->render('wish/list.html.twig');
    }

//    #[Route('/wishes/', name: 'no_wish', methods: ['GET'])]
//    public function noWish(): Response
//    {
//        $this->addFlash('warning',"Un id est nécessaire");
//        return $this->redirectToRoute('main_home');
//    }

    #[Route('/wishes/details/{id}', name: 'wish_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function wishDetail(?int $id = null): Response
    {
        if($id === null) {
            $this->addFlash('warning',"Un id est nécessaire");
            return $this->redirectToRoute('main_home');
        }
        return $this->render('wish/detail.html.twig');
    }
}
