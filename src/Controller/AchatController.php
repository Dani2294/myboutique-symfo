<?php

namespace App\Controller;

use App\Services\CommandeManager;
use App\Services\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            dd($detailCommande);
            $manager->persist($detailCommande);
        }
        $manager->flush();
        $panier->deletePanier();
        return $this->redirectToRoute('app_home');
    }
}
