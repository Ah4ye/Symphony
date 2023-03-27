<?php

namespace App\Controller;


use App\Entity\PanierProduit;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\User ;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index( EntityManagerInterface $em):Response
    {
        // refaire la Classe PanierProduits !
        $user = $this->getUser();
        $id_user = $user->getId();
        //Trouve le Panier du client
        $panierRepository = $em->getRepository(Panier::class);
        $panier = $panierRepository->findOneBy(['Client'=> $id_user]);
        $idpanier = $panier->getId();
        // Trouve le Panier du client
        $panierproduitRepository = $em->getRepository(PanierProduit::class);
        $panierproduit = $panierproduitRepository->findBy(['panier'=>$idpanier]);





        // Trouve les Produits du Panier
        $produitRepository = $em->getRepository(Produit::class);
       //$Panierproduits = $produitRepository->findBy(['id'=>$product]);
        //$Panierproduits = $produitRepository->findAll();
        $Panierproduits = [];


        if ($this->getUser() ){

        $name = $user->getUserIdentifier(); }



        $args = array(

            'username' => $name,
            'paniers' => $panier,
            'produits' => $Panierproduits,
            'panierproduit' => $panierproduit,
        );
        return $this->render('Panier/layout_panier_client.html.twig',$args);
    }
}
