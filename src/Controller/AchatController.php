<?php

namespace App\Controller;

use App\Services\CommandeManager;
use App\Services\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AchatController extends AbstractController
{
    #[Route('/achat', name: 'app_confirme_panier')]
    public function index(Panier $panier, CommandeManager $commandeManager, EntityManagerInterface $manager): Response
    {
        // si l'utilisateur n'est pas connecté ont le redirige vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

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
            $manager->persist($detailCommande);
            //dd($row_panier);
        }


        $manager->flush();
        $panier->deletePanier();
        return $this->redirectToRoute('app_home');
    }




    #[Route('/payement', name: 'app_payement')]
    public function payement(Panier $panier)
    {
        // si l'utilisateur n'est pas connecté ont le redirige vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        // ====================== STRIPE SECTION ===================== //

        // This is your test secret API key.
        \Stripe\Stripe::setApiKey('sk_test_51LBD35Foa9gE9N10xg7XDEoDGHaMBOLLkhdHvy9o0lLXbSTVQzak5r88ho3v2UCooqC1HbmqEDhHGkN6pFcoGSSk00Ws3pG5Sk');

        //header('Content-Type: application/json');

        $YOUR_DOMAIN = 'http://127.0.0.1:8000';

        $stripe = new \Stripe\StripeClient('sk_test_51LBD35Foa9gE9N10xg7XDEoDGHaMBOLLkhdHvy9o0lLXbSTVQzak5r88ho3v2UCooqC1HbmqEDhHGkN6pFcoGSSk00Ws3pG5Sk');

        // je recup le tableau détail panier
        $detailPanierTab = $panier->getPanierWithProducts();

        // J'initialise des tableaux vides pour stocker les données de la commande pour Stripe
        $products = [];
        $prices = [];
        $quantity = [];

        /* foreach ($detailPanierTab as $row_panier) {
            $products[] = $stripe->products->create([
                'name' => $row_panier['product']->getName(),
            ]);
            $quantity[] = $row_panier['quantity'];
        } */

        $i = 0;
        foreach ($detailPanierTab as $row_panier) {
            $products[] = $stripe->products->create([
                'name' => $row_panier['product']->getName(),
            ]);
            $prices[] = $stripe->prices->create([
                'product' => $products[$i]->id,
                'unit_amount' => $row_panier['product']->getPrix() * 100,
                'currency' => 'eur',
            ]);
            $quantity[] = $row_panier['quantity'];
            $i++;
        }

        // j'initialise nu tableau vide pour acceuillir les données (price_ID et quantity) de la commande pour Stripe
        $lineItems = [];
        $i = 0;
        foreach ($prices as $price) {
            $lineItems[] = [
                'price' => $price->id,
                'quantity' => $quantity[$i],
            ];
            $i++;
        }


        $checkout_session = \Stripe\Checkout\Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/templates/home/success.html.twig',
            'cancel_url' => $YOUR_DOMAIN . '/templates/home/cancel.html.twig',
            'automatic_tax' => [
                'enabled' => false,
            ],
        ]);

        /* header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url); */
        $response = new RedirectResponse($checkout_session->url);
        return $response;

        // ====================== STRIPE SECTION ===================== //
    }
}
