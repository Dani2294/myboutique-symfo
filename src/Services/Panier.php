<?php

namespace App\Services;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Panier
{
    private $session;
    private $productRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    /**
     * methode pour ajouter un produit au panier
     * on ajoute seulement un id au tableau
     * @return void
     */
    public function addProduct($id)
    {
        $panier = $this->session->get('panier', []);
        if (!empty($panier[$id])) {
            // si le tableau contient déjà l'id, je rajoute 1 à la quantité
            $panier[$id]++;
        } else {
            // si le panier ne contient pas l'id
            $panier[$id] = 1;
        }
        $this->session->set('panier', $panier);
    }

    /**
     * methode pour renvoyer le panier
     * @return array
     */
    public function getPanier()
    {
        return $this->session->get('panier', []);
    }

    /**
     * methode pour renvoyer le panier avec les produits
     * @return array
     */
    public function getPanierWithProducts()
    {
        $panier = $this->getPanier();
        $panierWithProducts = [];
        foreach ($panier as $id => $quantity) {
            $panierWithProducts[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity,
            ];
        }

        return $panierWithProducts;
    }

    /**
     * methode pour calculer le prix total du panier
     * @return float
     */
    public function getPrixTotal()
    {
        $panierWithProducts = $this->getPanierWithProducts();
        $prixTotal = 0;
        foreach ($panierWithProducts as $product) {
            $prixTotal += $product['product']->getPrix() * $product['quantity'];
        }

        return $prixTotal;
    }


    /**
     * methode pour supprimer une quantité d'un produit
     * @return void
     */
    public function deleteOneQuantity($id)
    {
        // je récup le panier
        $panier = $this->getPanier();

        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                // si il y a au moins une quantité du produit, je soustrait 1
                $panier[$id]--;
            } else {
                // si la quantité est à 1, je supprime l'id du tableau
                unset($panier[$id]);
            }
            $this->session->set('panier', $panier);
        }
    }

    /**
     * methode pour supprimer un produit du panier
     * @return void
     */
    public function deleteProduct($id)
    {
        $panier = $this->getPanier();
        if (!empty($panier[$id])) {
            unset($panier[$id]);
            $this->session->set('panier', $panier);
        }
    }

    /**
     * methode pour supprimer le panier
     * @return void
     */
    public function deletePanier()
    {
        $this->session->remove('panier');
    }
}
