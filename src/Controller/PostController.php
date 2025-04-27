<?php

namespace App\Controller;

use App\Entity\Commentaire; // Added for Commentaire entity
use App\Entity\Post;
use App\Form\CommentaireType; // Added for Commentaire form
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/forum/post')] // Base route for forum posts
#[IsGranted('ROLE_ADMIN')] // Secure the whole controller (adjust role if needed)
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(Request $request, PostRepository $postRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $postRepository->createQueryBuilder('p')
            ->leftJoin('p.author', 'a') // Join author to allow sorting/display
            ->addSelect('a')
            ->orderBy('p.createdAt', 'DESC'); // Default order: newest first

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15 // Items per page
        );

        return $this->render('post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        // Automatically set the currently logged-in user as the author
        $currentUser = $this->getUser();
        if (!$currentUser) {
            // IsGranted should prevent anonymous access, but good practice to check
            $this->addFlash('error', 'You must be logged in to create a post.');
            // Adjust login route name if different
            return $this->redirectToRoute('app_login');
        }
        // Ensure the user object is the correct User entity type
        if (!$currentUser instanceof \App\Entity\User) {
            $this->addFlash('error', 'Invalid user session.');
            return $this->redirectToRoute('app_login'); // Or an error page
        }
        $post->setAuthor($currentUser);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Timestamps are handled by Lifecycle Callbacks in the entity
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Forum post created successfully.');

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(), // Pass form view
        ]);
    }

    // Modified 'show' action to include the comment form creation
    #[Route('/{id}', name: 'app_post_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Post $post, Request $request, EntityManagerInterface $entityManager): Response // Inject Request & EM
    {
        // --- Create Add Comment Form ---
        $commentaire = new Commentaire();
        $commentaire->setPost($post); // Pre-associate with the current post

        // We need to pass the action URL for the form explicitly if handling in another method
        $commentForm = $this->createForm(CommentaireType::class, $commentaire, [
            'action' => $this->generateUrl('app_post_add_comment', ['id' => $post->getId()]),
            'method' => 'POST',
        ]);

        // Pass the post and the comment form to the template
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm->createView(), // Pass the form view
        ]);
    }


    // --- Add Action to HANDLE Comment Submission ---
    #[Route('/{id}/comment', name: 'app_post_add_comment', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Ensure user is logged in to comment (could be ROLE_USER)
    public function addComment(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $commentaire->setPost($post);
        $currentUser = $this->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'You must be logged in to comment.');
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }
        // Ensure the user object is the correct User entity type
        if (!$currentUser instanceof \App\Entity\User) {
            $this->addFlash('error', 'Invalid user session.');
            return $this->redirectToRoute('app_login'); // Or an error page
        }
        $commentaire->setAuthor($currentUser);

        // Use a clean form instance for handling the request
        // Pass only the data object, not action/method here as it's handled by the route
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire); // Persist the new comment
            $entityManager->flush(); // Save to database
            $this->addFlash('success', 'Comment added successfully.');
        } else {
            // Handle validation errors - display them back on the show page via flash messages
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            // Add a generic warning if submission failed but specific errors weren't caught
            if (!$form->isSubmitted() || !$form->isValid()){
                $this->addFlash('warning', 'Could not add comment. Please check the content.');
            }
        }

        // Always redirect back to the post show page after attempting to add comment
        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
    }


    #[Route('/{id}/edit', name: 'app_post_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Optional: Authorization check (example)
        // $this->denyAccessUnlessGranted('EDIT', $post); // Using Voters
        // Or simpler check:
        // if ($post->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
        //     throw $this->createAccessDeniedException('You cannot edit this post.');
        // }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // UpdatedAt is handled by Lifecycle Callbacks
            $entityManager->flush();

            $this->addFlash('success', 'Forum post updated successfully.');

            // Redirect back to the show page for this post
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(), // Pass form view
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Optional: Authorization check (example)
        // $this->denyAccessUnlessGranted('DELETE', $post); // Using Voters

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            // cascade: remove on relationship in Post entity handles comment deletion
            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'Forum post (and its comments) deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Deletion failed.');
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}