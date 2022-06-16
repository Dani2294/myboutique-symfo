<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Services\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(Panier $panier): Response
    {

        return $this->render('panier/index.html.twig', [
            'panier' => $panier->getPanier(),
            'panierProducts' => $panier->getPanierWithProducts(),
            'prixTotal' => $panier->getPrixTotal(),
        ]);
    }

    // add to cart
    #[Route('/add-to-cart/{id}', name: 'app_add_to_cart')]
    public function addToCart(Panier $panier, $id): Response
    {
        $panier->addProduct($id);
        return $this->redirectToRoute('app_panier');
    }

    // delete one quantity
    #[Route('/delete-one-quantity/{id}', name: 'app_delete_one_quantity')]
    public function deleteOneQuantity(Panier $panier, $id): Response
    {
        $panier->deleteOneQuantity($id);
        return $this->redirectToRoute('app_panier');
    }

    // delete product
    #[Route('/delete-product/{id}', name: 'app_delete_product')]
    public function deleteProduct(Panier $panier, $id): Response
    {
        $panier->deleteProduct($id);
        return $this->redirectToRoute('app_panier');
    }

    // delete all products
    #[Route('/delete-all-products', name: 'app_delete_all_products')]
    public function deleteAllProducts(Panier $panier): Response
    {
        $panier->deletePanier();
        return $this->redirectToRoute('app_panier');
    }
}
