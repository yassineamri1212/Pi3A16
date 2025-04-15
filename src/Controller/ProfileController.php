<?php
// src/Controller/ProfileController.php
namespace App\Controller;

use App\Entity\User; // Assuming this is your User entity
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')] // Base route for profile actions
#[IsGranted('IS_AUTHENTICATED_FULLY')] // Ensure user is logged in for all actions here
class ProfileController extends AbstractController
{
    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser(); // Get the currently logged-in user object

        if (!$user) {
            // Should not happen due to IsGranted, but belts and braces
            throw $this->createAccessDeniedException('You must be logged in to edit your profile.');
        }

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle optional password update
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                // Hash and set the new password
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
                // It's important to erase the plain password after hashing
                $user->eraseCredentials();
            }

            // Persist changes (only flush needed as user object is managed)
            $entityManager->flush();

            $this->addFlash('success', 'Your profile has been updated successfully!');

            // Redirect back to the edit page (or a dedicated profile view page if you create one)
            return $this->redirectToRoute('app_profile_edit', [], Response::HTTP_SEE_OTHER);

        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->render('profile/edit.html.twig', [
            'profileForm' => $form->createView(), // Pass form view to template
            'user' => $user // Pass user object if needed in template
        ]);
    }

    // Add other profile-related actions here if needed (e.g., view profile)
    // #[Route('/', name: 'app_profile_view', methods: ['GET'])]
    // public function view(): Response
    // {
    //     $user = $this->getUser();
    //     return $this->render('profile/view.html.twig', ['user' => $user]);
    // }
}