<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/forum/commentaire')] // Base route
#[IsGranted('ROLE_ADMIN')] // Secure the whole controller
class CommentaireController extends AbstractController
{
    #[Route('/', name: 'app_commentaire_index', methods: ['GET'])]
    public function index(Request $request, CommentaireRepository $commentaireRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $commentaireRepository->createQueryBuilder('c')
            ->leftJoin('c.author', 'a')->addSelect('a') // Include author
            ->leftJoin('c.post', 'p')->addSelect('p') // Include post
            ->orderBy('c.createdAt', 'DESC'); // Newest comments first

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20 // Show more comments per page maybe
        );

        return $this->render('commentaire/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    // Creating comments globally might not be intuitive.
    // Consider removing '/new' route or redirecting if accessed directly.
    // Typically comments are added via a form on the Post show page.
    #[Route('/new', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->addFlash('info', 'Comments should typically be added from the specific post page.');
        // For now, let's allow creation but it requires selecting a post if EntityType is used in Form
        // Or remove this route entirely if not needed:
        // throw $this->createNotFoundException('Global comment creation not supported.');

        $commentaire = new Commentaire();
        $currentUser = $this->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'You must be logged in.');
            return $this->redirectToRoute('app_login'); // Adjust if needed
        }
        $commentaire->setAuthor($currentUser);

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($commentaire->getPost() === null && !$form->has('post')) {
                $this->addFlash('error', 'Cannot create a comment without assigning it to a post.');
                // Handle error - perhaps redirect back with form errors shown
                return $this->render('commentaire/new.html.twig', [
                    'commentaire' => $commentaire,
                    'form' => $form,
                ]);
            }
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Comment created successfully.');
            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // No updatedAt for comments in this design, but could be added
            $entityManager->flush();
            $this->addFlash('success', 'Comment updated successfully.');
            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
            $this->addFlash('success', 'Comment deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
    }
}