<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Gallery;
use App\Entity\Category;
use App\Form\GalleryType;
use App\Form\GalleryEditType;
use App\Entity\ArtisticWork;
use App\Repository\UserRepository;
use App\Repository\GalleryRepository;
use App\Repository\CategoryRepository;
use App\Repository\ArtisticWorkRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


/**
 * @Route("/galeries")
 */
class GalleryController extends AbstractController
{
    
      public function __construct(private ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

        
    /**
     * @Route("/{id}", name="galery_index", methods={"GET"})
     */
    public function index(GalleryRepository $galleryRepository, User $user): Response
    {
       // dd($user);
        return $this->render('gallery/index.html.twig', [
          'galeries' => $galleryRepository->findBy(['user' => $user]),
        ]);
       
        }
        
        
    // ici je tente d'envoyer la bonne galerie au click sur la categorie voulue
    /**
     * @Route("/category/{id}", name="galery_category", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function category(Request $request, PaginatorInterface $paginator, Category $category, GalleryRepository $galleryRepository)
     {
        return $this->render('gallery/category.html.twig',[ 
         'galleries' => $paginator->paginate(
          $galleryRepository->findBy(['category' => $category]),
          $request->query->getInt('page' , 1 ),
          4),
          'category' =>$category,
        ]);
    }

     /**
     * @Route("/new/{id}", name="galery_new", methods={"GET","POST"}, requirements={"id": "\d+"})
     */
    public function new(Request $request, User $user): Response
    {
        $gallery = new Gallery();
        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre galerie a bien été crée!');
            $gallery->setUser($user);
            $em = $this->doctrine->getManager();
            $em->persist($gallery);
            $em->flush();

            return $this->redirectToRoute('galery_edit', ['id' => $gallery->getId()]);
        }

        return $this->render('gallery/new.html.twig', [
            'gallery' => $gallery,
            'form' => $form->createView(),
        ]);
    }

      /**
     * @Route("/edit/{id}", name="galery_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function edit(Request $request, GalleryRepository $galleryrepo): Response
    {
        $gallery = $galleryrepo->findBy(['user' => $this->getUser()]);
        //$gallery = new Gallery();
       // dd($gallery);
     if(!$gallery) {
         return $this->redirectToRoute('member_index');
     }
        $form = $this->createForm(GalleryEditType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre galerie a bien été mise à jour!');
            $this->doctrine->getManager()->flush();
           
            return $this->redirectToRoute('galery_edit', ['id' => $gallery->getId()]);
        }

        return $this->render('gallery/edit.html.twig', [
          
            'gallery' => $gallery,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="galery_showUser", methods={"GET"})
     */
    public function show(Gallery $gallery): Response
    {
      
        return $this->render('gallery/show.html.twig', [
     
         'gallery'=>$gallery,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="galery_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Gallery $gallery): Response
    {
        if ($this->isCsrfTokenValid('delete'.$gallery->getId(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($gallery);
            $entityManager->flush();
            $this->addFlash('success', 'Votre galerie a bien été supprimée!');
        }

        return $this->redirectToRoute('gallery_index');
    }
    
}
