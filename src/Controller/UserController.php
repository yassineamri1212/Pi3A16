<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
         return $this->render('user/index.html.twig', [
              'users' => $userRepository->findAll(),
         ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
         $user = new User();
         $form = $this->createForm(UserType::class, $user);
         $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()) {
              $plainPassword = $form->get('plainPassword')->getData();
              if ($plainPassword) {
                   $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
                   $user->eraseCredentials();
              } else {
                   $this->addFlash('error', 'Password cannot be empty for a new user.');
                   return $this->render('user/new.html.twig', ['user' => $user, 'form' => $form->createView()]);
              }
              $entityManager->persist($user);
              $entityManager->flush();
              $this->addFlash('success', sprintf('User %s created successfully!', $user->getUserName()));
              return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
         }
         return $this->render('user/new.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'app_user_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(User $user): Response
    {
         return $this->render('user/show.html.twig', ['user' => $user]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
         $form = $this->createForm(UserType::class, $user);
         $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()) {
              $plainPassword = $form->get('plainPassword')->getData();
              if ($plainPassword) {
                   $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
                   $user->eraseCredentials();
              }
              $entityManager->flush();
              $this->addFlash('success', sprintf('User %s updated successfully!', $user->getUserName()));
              return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
         }
         return $this->render('user/edit.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }

    #[Route('/{id}/toggle-block', name: 'app_user_toggle_block', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleBlock(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
         $tokenName = 'toggle_block' . $user->getId();
         if ($this->isCsrfTokenValid($tokenName, $request->request->get('_token'))) {
              if ($this->getUser() === $user) {
                   $this->addFlash('error', 'You cannot block your own account.');
              } else {
                   // Do not allow blocking an admin account.
                   if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                        $this->addFlash('error', 'You cannot block an admin account.');
                   } else {
                        $user->setBlocked(!$user->isBlocked());
                        $entityManager->flush();
                        $this->addFlash('success', sprintf('User %s has been %s.', $user->getUserName(), $user->isBlocked() ? 'blocked' : 'unblocked'));
                   }
              }
         } else {
              $this->addFlash('error', 'Invalid security token. Action aborted.');
         }
         return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete', name: 'app_user_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
         if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
              if ($this->getUser() === $user) {
                   $this->addFlash('error', 'You cannot delete your own account.');
                   return $this->redirectToRoute('app_user_index');
              }
              $username = $user->getUserName();
              $entityManager->remove($user);
              $entityManager->flush();
              $this->addFlash('success', sprintf('User %s deleted successfully.', $username));
         } else {
              $this->addFlash('error', 'Invalid security token. User deletion failed.');
         }
         return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}