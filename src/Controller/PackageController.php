<?php

namespace App\Controller;

use App\Entity\Package;
use App\Form\PackageType;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/package')]
#[IsGranted('ROLE_ADMIN')] // Secure the whole controller
class PackageController extends AbstractController
{
    #[Route('/', name: 'app_package_index', methods: ['GET'])]
    public function index(Request $request, PackageRepository $packageRepository, PaginatorInterface $paginator): Response
    {
        // Basic query, join livraison to potentially sort/filter by it later
        $queryBuilder = $packageRepository->createQueryBuilder('p')
            ->leftJoin('p.livraison', 'l')
            ->addSelect('l')
            ->orderBy('p.id', 'DESC'); // Example order

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20 // More items per page maybe
        );

        return $this->render('package/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    // Standalone 'new' might be disabled if packages are always added via Livraison form
    #[Route('/new', name: 'app_package_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->addFlash('info', 'Packages should typically be added via the Delivery form.');
        // Or redirect directly: return $this->redirectToRoute('app_livraison_index');

        $package = new Package();
        // Pass 'standalone' => true to show the Livraison selection field
        $form = $this->createForm(PackageType::class, $package, ['standalone' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($package);
            $entityManager->flush();
            $this->addFlash('success', 'Standalone Package created.');
            return $this->redirectToRoute('app_package_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('package/new.html.twig', [
            'package' => $package,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_package_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Package $package): Response
    {
        return $this->render('package/show.html.twig', [
            'package' => $package,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_package_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Package $package, EntityManagerInterface $entityManager): Response
    {
        // Pass 'standalone' => true to show/edit the Livraison association
        $form = $this->createForm(PackageType::class, $package, ['standalone' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Package updated.');
            return $this->redirectToRoute('app_package_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('package/edit.html.twig', [
            'package' => $package,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_package_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Package $package, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$package->getId(), $request->request->get('_token'))) {
            $entityManager->remove($package);
            $entityManager->flush();
            $this->addFlash('success', 'Package deleted.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_package_index', [], Response::HTTP_SEE_OTHER);
    }
}