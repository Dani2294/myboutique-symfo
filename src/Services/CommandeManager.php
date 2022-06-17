<?php

namespace App\Services;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class CommandeManager
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getUser(): User
    {
        $user = $this->security->getUser();
        return $user;
    }

    public function getCommande(Panier $panier)
    {
        // On récupère l'utilisateur connecté
        $user = $this->getUser();

        // on instancie une nouvelle commande
        $commande = new Commande();

        // je rajoute le user a mon objet commande
        $commande->setUser($user);

        // je rajoute le nom du client
        $commande->setNom($user->getName() . ' ' . $user->getFirstName());

        // je rajoute l'adresse du client
        $commande->setAdresse($user->getAdress() . ' ' . $user->getCity() . ' ' . $user->getPostalCode());

        // je rajoute la date de la commande
        $commande->setDateCommande(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        // je rajoute le total de la commande
        $commande->setTotalCommande($panier->getPrixTotal());

        // je met le statut de la commande à false
        $commande->setStatusLivraison(false);

        return $commande;
    }

    public function getDetailCommande(Commande $commande, $row_panier)
    {
        // j'instantie un nouvel objet detail commande
        $detailCommande = new DetailCommande();

        // je rajoute la commande a mon objet detail commande
        $detailCommande->setCommande($commande);

        // je rajoute le nom du produit
        $detailCommande->setName($row_panier['product']->getName());

        // je rajoute la référence du produit
        $detailCommande->setRef($row_panier['product']->getRef());

        // je rajoute la quantité du produit
        $detailCommande->setQuantity($row_panier['quantity']);

        // je rajoute le prix total du produit
        $total = $row_panier['product']->getPrix() * $row_panier['quantity'];
        $detailCommande->setTotal($total);

        return $detailCommande;
    }
}
