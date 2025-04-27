<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Form\CommentaireType;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression; // For IsGranted expression

#[Route('/forum')] // Base route for the public forum section
class ForumController extends AbstractController
{
    // ===== Forum Index (List Posts) =====
    #[Route('/', name: 'app_forum_index', methods: ['GET'])]
    // #[IsGranted('IS_AUTHENTICATED_FULLY')] // Uncomment if forum requires login to view
    public function index(Request $request, PostRepository $postRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $postRepository->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')->addSelect('a')
            ->leftJoin('p.commentaires', 'c') // Optional: join comments to potentially get counts efficiently
            ->orderBy('p.createdAt', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10 // Posts per page on public listing
        );

        // RENDER A *FRONTEND* TEMPLATE
        return $this->render('forum/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    // ===== Create New Post =====
    #[Route('/post/new', name: 'app_forum_post_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')] // Or IS_AUTHENTICATED_FULLY - user must be logged in
    public function newPost(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $currentUser = $this->getUser();
        // Ensure we have a logged-in user entity
        if (!$currentUser instanceof \App\Entity\User) {
            $this->addFlash('error', 'You must be logged in to create a post.');
            return $this->redirectToRoute('app_login'); // Adjust login route if needed
        }
        $post->setAuthor($currentUser);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Post created successfully!');
            // Redirect to the newly created post's page
            return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
        }

        // RENDER A *FRONTEND* TEMPLATE
        return $this->render('forum/new_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ===== Show Single Post (and Comments) =====
    #[Route('/post/{id}', name: 'app_forum_post_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    // #[IsGranted('IS_AUTHENTICATED_FULLY')] // Uncomment if viewing posts requires login
    public function showPost(Post $post): Response // ParamConverter fetches the Post
    {
        // Comment form creation logic - passed to the template
        $comment = new Commentaire();
        $comment->setPost($post); // Associate with this post
        $commentForm = $this->createForm(CommentaireType::class, $comment, [
            'action' => $this->generateUrl('app_forum_comment_add', ['postId' => $post->getId()]),
            'method' => 'POST',
        ]);

        // RENDER A *FRONTEND* TEMPLATE
        return $this->render('forum/show_post.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm->createView(),
            'commentaires' => $post->getCommentaires() // Pass comments collection
        ]);
    }

    // ===== Add Comment (Handles POST Submission) =====
    #[Route('/post/{postId}/comment', name: 'app_forum_comment_add', requirements: ['postId' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')] // Must be logged in to comment
    public function addComment(int $postId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Find the post the comment belongs to
        $post = $entityManager->getRepository(Post::class)->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found.');
        }

        $comment = new Commentaire();
        $comment->setPost($post);
        $currentUser = $this->getUser();
        if (!$currentUser instanceof \App\Entity\User) {
            $this->addFlash('error', 'You must be logged in to comment.');
            return $this->redirectToRoute('app_login'); // Adjust login route if needed
        }
        $comment->setAuthor($currentUser);

        // Use a clean form instance for handling the request
        $form = $this->createForm(CommentaireType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Comment added.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            if (!$form->isSubmitted() || !$form->isValid()){
                $this->addFlash('warning', 'Could not add comment.');
            }
        }
        // Redirect back to the post show page regardless of success/failure
        return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
    }

    // ===== Edit Post =====
    #[Route('/post/{id}/edit', name: 'app_forum_post_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    // Grant access if user is the author OR has ROLE_ADMIN
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or (is_granted("ROLE_USER") and user === subject.getAuthor())'), subject: 'post')]
    public function editPost(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // UpdatedAt handled by lifecycle callback
            $this->addFlash('success', 'Post updated.');
            return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
        }

        // RENDER A *FRONTEND* TEMPLATE
        return $this->render('forum/edit_post.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    // ===== Delete Post =====
    #[Route('/post/{id}/delete', name: 'app_forum_post_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    // Grant access if user is the author OR has ROLE_ADMIN
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or (is_granted("ROLE_USER") and user === subject.getAuthor())'), subject: 'post')]
    public function deletePost(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Use a unique token based on route name and ID
        if ($this->isCsrfTokenValid('delete_forum_post'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post); // Comments deleted via cascade remove
            $entityManager->flush();
            $this->addFlash('success', 'Post deleted successfully.');
            return $this->redirectToRoute('app_forum_index'); // Redirect to forum index
        } else {
            $this->addFlash('error', 'Invalid security token.');
            // Redirect back to where delete was attempted (e.g., post show page)
            return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
        }
    }

    // Note: Editing/Deleting comments by users is often handled via AJAX/Stimulus on the show_post page
    // or left to admin moderation via the separate admin controllers.
    // Adding separate routes/pages for user comment editing adds complexity.
}