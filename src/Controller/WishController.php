<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/wishes', name: 'wishes_')]
class WishController extends AbstractController
{
    #[Route('/', name: 'list_wish', methods: ['GET'])]
    public function listWish(WishRepository $wishRepository): Response
    {
        $allWishes = $wishRepository->findBy([],['dateCreated' => 'ASC']);
        return $this->render('wish/list.html.twig',
        [
            'allWishes' => $allWishes,
            'test' => null
        ]);
    }

    #[Route('/details', name: 'no_wish', methods: ['GET'])]
    public function noWish(): Response
    {
        $this->addFlash('warning',"Selectionnez un voeux pour en voir le détail");
        return $this->redirectToRoute('wishes_list_wish');
    }

    #[Route('/details/{id}', name: 'wish_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function wishDetail(?int $id = null, WishRepository $wishRepository): Response
    {
        if($id === null) {
            $this->addFlash('warning',"Un id est nécessaire");
            return $this->redirectToRoute('main_home');
        }
        $wish = $wishRepository->find($id);

        return $this->render('wish/detail.html.twig', ["wish" => $wish]);
    }

    #[Route('/create', name: 'create_wish', methods: ['GET','POST'])]
    public function wishCreate(Request $request, EntityManagerInterface $em, SluggerInterface $slugger,#[Autowire('%kernel.project_dir%/public/uploads/images/wish')] string $imageDirectory): Response {

        $wish = new Wish();
        $wishForm = $this->createForm(WishType::class, $wish);
        $wishForm->handleRequest($request);

        if($wishForm->isSubmitted() && $wishForm->isValid()) {

            $wishFile = $wishForm->get('image')->getData();
            if($wishFile) {
                $originalFilename = pathinfo($wishFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$wishFile->guessExtension();

                try{
                    $wishFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                    var_dump($e->getMessage());
                }
                $wish->setPathImage($newFilename);
            }

            $em ->persist($wish);
            $em ->flush();
            $this->addFlash('succes','Idea successfully created');
            return $this->redirectToRoute("wishes_wish_detail", ["id" => $wish->getId()]);
        }

        return $this->render('wish/create.html.twig', [
            'wishForm' => $wishForm
        ]);
    }

    #[Route('/details/{id}/modify', name: 'modify_wish', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function modifyWish(?int $id,Request $request, EntityManagerInterface $em, WishRepository $wishRepository, SluggerInterface $slugger,#[Autowire('%kernel.project_dir%/public/uploads/images/wish')] string $imageDirectory): Response {
        $wish = $wishRepository->find($id);
        $wishForm = $this->createForm(WishType::class, $wish);
        $wishForm->handleRequest($request);
        if($wishForm->isSubmitted() && $wishForm->isValid()) {
            $wishFile = $wishForm->get('image')->getData();
            if($wishFile) {
                $originalFilename = pathinfo($wishFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$wishFile->guessExtension();

                try{
                    $wishFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                    var_dump($e->getMessage());
                }
                $wish->setPathImage($newFilename);
            }
            $wish->setDateUpdated(new \DateTimeImmutable());
            $em ->flush();
            $this->addFlash('succes','Idea successfully updated');
            return $this->redirectToRoute('wishes_list_wish');
        }

        return $this->render('wish/edit.html.twig', [
            'wishForm' => $wishForm,
            'wish' => $wish
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_wish', methods: ['POST'])]
    public function deleteWish(?int $id, WishRepository $wishRepository,EntityManagerInterface $em): Response {
        $wish = $wishRepository->find($id);
        $em->remove($wish);
        $em->flush();
        $this->addFlash('success', 'Le souhait a bien été supprimé.');

        // Rediriger vers la liste des souhaits après suppression
        return $this->redirectToRoute('wishes_list_wish');
    }



    #[Route('/demo', name: 'demo', methods: ['GET'])]
    public function demo(EntityManagerInterface $em): Response
    {
        $wish = new Wish();

        $wish -> setTitle("Chat")
            -> setDescription("Je veux des chats")
            -> setAuthor("Juliette")
            -> setPublished(true)
            -> setDateCreated(new \DateTimeImmutable());

            dump($wish);


        $em -> persist($wish);

        $em->flush();

        $wish = new Wish();

        $wish -> setTitle("Manger")
            -> setDescription("J'ai faim!!!!")
            -> setAuthor("Vincent")
            -> setPublished(true)
            -> setDateCreated(new \DateTimeImmutable());

        dump($wish);

        $em -> persist($wish);

        $em->flush();

        $wish = new Wish();

        $wish -> setTitle("Un stage")
            -> setDescription("Je veux trouver un stage")
            -> setAuthor("Jérôme")
            -> setPublished(true)
            -> setDateCreated(new \DateTimeImmutable());


        dump($wish);

        $em -> persist($wish);

        $em->flush();

        return $this->render('wish/list.html.twig');
    }


}
