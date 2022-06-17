<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Services\CommandeManager;
use App\Services\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $products,
            "categorys" => $categoryRepository->findAll()
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function productShow(ProductRepository $productRepository, $id): Response
    {
        $product = $productRepository->find($id);
        return $this->render('home/product_show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('categories-produit/{id}', name: 'app_filtre_product')]
    public function filtreCategory($id, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('home/index.html.twig', [
            "products" => $productRepository->findBy(['category' => $id]),
            "categorys" => $categoryRepository->findAll()
        ]);
    }

    #[Route('/templates/home/success.html.twig', name: 'app_success_payement')]
    public function successPayement(Panier $panier, CommandeManager $commandeManager, EntityManagerInterface $manager, ProductRepository $productRepository): Response
    {
        // on récupère la commande
        $commande = $commandeManager->getCommande($panier);

        // je mets en attente l'object commande prêt à être envoyé à la bdd
        $manager->persist($commande);

        // je recup le tableau détail panier
        $detailPanierTab = $panier->getPanierWithProducts();
        // je parcours le tableau
        foreach ($detailPanierTab as $row_panier) {
            $detailCommande = $commandeManager->getDetailCommande($commande, $row_panier);
            // je mets en attente l'object detail commande prêt à être envoyé à la bdd

            // gérer la quantité du produit
            // TODO : Condition en cas de commande + grande que la quantité en stock
            $produit = $productRepository->find($row_panier['product']->getId());
            $quantity = $produit->getQuantity();
            $new_quantity = $quantity - $row_panier['quantity'];
            $produit->setQuantity($new_quantity);
            $productRepository->add($produit);


            $manager->persist($detailCommande);
        }

        $articlesAchete = $panier->getPanierWithProducts();
        $total = $panier->getPrixTotal();

        $manager->flush();
        $panier->deletePanier();

        return $this->render('home/success.html.twig', [
            'articlesAchete' => $articlesAchete,
            'total' => $total
        ]);
    }

    #[Route('/templates/home/cancel.html.twig', name: 'app_cancel_payement')]
    public function cancelPayement(): Response
    {
        return $this->render('home/cancel.html.twig');
    }
}
