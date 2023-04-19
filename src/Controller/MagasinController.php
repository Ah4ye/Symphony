<?php

namespace App\Controller;
use App\Entity\Produit;

use App\Entity\Magasin;
use App\Entity\ProduitMagasin;
use App\Form\EditProduitType;
use App\Form\MagasinType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Count;

#[Route('/magasin', name: 'magasin')]
class MagasinController extends AbstractController
{
    #[Route('/add', name: '_add')]
    public function addAction(EntityManagerInterface $em, Request $request): Response
    {
        $magasin = new Magasin();

        $form = $this->createForm(MagasinType::class, $magasin);
        $form->add('send', SubmitType::class, ['label' => 'add magasin']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($magasin);
            $em->flush();
            $this->addFlash('info', 'ajout magasin réussi');
            return $this->redirectToRoute('produit_list');    // il faudrait l'action qui liste les magasins
        }

        if ($form->isSubmitted())
            $this->addFlash('info', 'formulaire ajout magasin incorrect');

        $args = array(
            'myform' => $form->createView(),
        );
        return $this->render('Magasin/add.html.twig', $args);
    }

    #[Route(
        '/valeur-stock/{id}',
        name: '_valeur_stock',
        requirements: ['id' => '[1-9]\d*'],
    )]
    public function valeurStockAction(int $id, EntityManagerInterface $em): Response
    {
        $Magasin_produitRepository = $em->getRepository(ProduitMagasin::class);
        $ProduitMagasin = $Magasin_produitRepository->findAll(['magasin' => $id]);
        $total = 0 ;
        foreach ($ProduitMagasin as $produit_magasin) {
            $prix = $produit_magasin->getPrixUnitaire() ;
            $quantite = $produit_magasin->getQuantite() ;
            $total += $prix*$quantite ;
        }

        $args = array(
            'id' => $id,
            'total' => $total,
        );
        return $this->render('Magasin/valeurStock.html.twig', $args);
    }

    #[Route(
        '/stock/{id}/{valinf}/{valsup}',
        name: '_stock',
        requirements: [
            'id' => '[1-9]\d*',
            'valinf' => '0|[1-9]\d*',
            'valsup' => '-1|0|[1-9]\d*',
        ],
        defaults: [
            'valinf' => 0,
            'valsup' => -1,
        ],
    )]
    public function stockAction(int $id, int $valinf, int $valsup , EntityManagerInterface $em): Response
    {
        // liste n'est pas fictive
        $Magasin_produitRepository = $em->getRepository(ProduitMagasin::class);
        $ProduitMagasin = $Magasin_produitRepository->findBy(['magasin' => $id]);

        $MagasinRepository = $em->getRepository(Magasin::class);
        $Magasin = $MagasinRepository->findAll();
        $max = count($Magasin);


        $produits = array() ;
        if ( !empty($ProduitMagasin)) {
            foreach ($ProduitMagasin as $produit_magasin) {
                $prix = $produit_magasin->getPrixUnitaire();
                $nom = $produit_magasin->getProduit()->getDenomination();
                $quantite = $produit_magasin->getQuantite();
                $idProduit = $produit_magasin->getProduit()->getId();
                $produit = [
                    'id' => $idProduit,
                    'denomination' => $nom,
                    'quantite' => $quantite,
                    'prixUnitaire' => $prix,
                ];
                array_push($produits,$produit);
            }
        }
        $args = array(
            'id' => $id,
            'valinf' => $valinf,
            'valsup' => $valsup,
            'produits' => $produits,
            'page' => $Magasin,
            'max' => $max,
        );
        return $this->render('Magasin/stock.html.twig', $args);
    }

    #[Route(
        '/edit/{idProduit}/{idMagasin}',
        name: '_edit',
        requirements: ['idProduit' => '[1-9]\d*', 'idMagasin' => '[1-9]\d*'],
    )]
    public function editAction(int $idProduit, int $idMagasin, EntityManagerInterface $em, Request $request): Response
    {
        $ProduitRepository = $em->getRepository(Produit::class);
        $Produit = $ProduitRepository->findOneBy(['id'=>$idProduit]);
        $MagasinRepository = $em->getRepository(Magasin::class);
        $Magasin = $MagasinRepository->findOneBy(['id'=>$idMagasin]);
        $ProduitMagasinRepository = $em->getRepository(ProduitMagasin::class);
        $ProduitMagasin = $ProduitMagasinRepository->findOneBy(['produit' => $idProduit, 'magasin'=>$idMagasin]);
        $form = $this->createForm(EditProduitType::class, $ProduitMagasin);
        $form->add('send', SubmitType::class, ['label' => 'Modifier']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ProduitMagasin);
            $em->flush();
            $this->addFlash('info', 'Modification du produit dans le Magasin ' . $idProduit);
            return $this->redirectToRoute('produit_view', ['id' => $idProduit]);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('info', 'échec modification produit ' . $idProduit);
        }
        $form->setData($ProduitMagasin);
        $args = array(
            'myform' => $form->createView(),
            'magasin'=> $Magasin,
            'produit' => $Produit,
        );
        return $this->render('Magasin/EditProduit.html.twig',$args);
    }
}
