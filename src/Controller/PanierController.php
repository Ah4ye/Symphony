<?php

namespace App\Controller;


use App\Entity\PanierProduit;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\ProduitMagasin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/panier', name: 'panier')]
class PanierController extends AbstractController
{
    #[Route('', name: '')]
    public function index( EntityManagerInterface $em):Response
    {
        $user = $this->getUser();
        //Trouve le panier du client ( 1 seul donc findOnebye)
        $panierRepository = $em->getRepository(Panier::class);
        $panier = $panierRepository->findOneBy(['Client'=> $user]);

        // Trouve les produits relier au panier du client ( plusieurs donc findby)
        $panierproduitRepository = $em->getRepository(PanierProduit::class);
        $panierproduit = $panierproduitRepository->findBy(['panier'=>$panier]);
        // pour le prix Unitaire
        $MagasinProduitRepository = $em->getRepository(ProduitMagasin::class);

        // Affecte les produits du panier à afficher
        $product = array() ;
        $produitRepository = $em->getRepository(Produit::class);
        if ( !empty($panierproduit)) {
            foreach ($panierproduit as $produit_magasin) {
                $produitfind = $produit_magasin->getProduit();
                $produits = $produitRepository->findOneBy(['id'=>$produitfind]);
                $quantitepanier = $produit_magasin->getQuantite();
                $denomination =$produits->getDenomination();

                $ProduitDuMagasin = $MagasinProduitRepository->findBy(['produit'=>$produits]);
                foreach ($ProduitDuMagasin as $stock) {
                    $PrixUnit = $stock->getPrixUnitaire();
                }
                // Verifie la Rupture de stock avant affichage
                if (empty($ProduitDuMagasin)){
                    $this->addFlash('info', 'Produit :'.$denomination.' en rupture de stock dans nos magasin');
                    $em->remove($produit_magasin);
                    $em->flush();
                }
                else {
                    $id = $produits->getId();
                    $products = ['id' => $id ,
                        'denomination' => $denomination,
                        'quantite' => $quantitepanier,
                        'Prix' => $PrixUnit*$quantitepanier,
                        ];
                    array_push($product,$products);
                }
            }
        }
        $nbElemPanier = count($product);
        $name = $user->getUserIdentifier();
        $args = array(

            'username' => $name,
            'paniers' => $panier,
            'produits' => $product,
            'panierproduit' => $panierproduit,
            'nbElements'=> $nbElemPanier,
        );
        return $this->render('Panier/layout_panier_client.html.twig',$args);
    }




    #[Route(
        '/moins/{id}',
        name: '_moins',
        requirements: ['id' => '[1-9]\d*'],
    )]
    public function moinsAction(int $id, EntityManagerInterface $em): Response
    {
        $User = $this->getUser();
        $idUser = $User->getId();
        $panierRepository = $em->getRepository(Panier::class);
        $Panier = $panierRepository->findOneBy(['Client'=>$idUser]);
        $panierproduitRepository = $em->getRepository(PanierProduit::class);
        $produit =$panierproduitRepository->findOneBy(['produit'=>$id, 'panier'=> $Panier]);

        if (is_null($produit))
            throw new NotFoundHttpException('erreur quantité produit ' . $id);
        $new_quantite =  $produit->getQuantite();
        if ( ($new_quantite-1) == 0 )
        {
            return $this->redirectToRoute('panier_delete', ['id' => $id]);
        }
        $new_quantite -= 1 ;
        $produit->setQuantite($new_quantite);
        $em->flush();
        $this->addFlash('info', 'Vous avez reduit la quantité du produit de 1');

        return $this->redirectToRoute('panier');
    }


    #[Route(
        '/plus/{id}',
        name: '_plus',
        requirements: ['id' => '[1-9]\d*'],
    )]
    public function plusAction(int $id, EntityManagerInterface $em): Response
    {
        $User = $this->getUser();
        $idUser = $User->getId();
        $panierRepository = $em->getRepository(Panier::class);
        $Panier = $panierRepository->findOneBy(['Client'=>$idUser]);
        $panierproduitRepository = $em->getRepository(PanierProduit::class);
        $produit =$panierproduitRepository->findOneBy(['produit'=>$id, 'panier'=> $Panier]);

        if (is_null($produit))
            throw new NotFoundHttpException('erreur quantité produit ' . $id);
        $new_quantite =  $produit->getQuantite();

        $produit->setQuantite($new_quantite+1);
        $em->flush();
        $this->addFlash('info', 'Vous avez augmenter la quantité du produit de 1');

        return $this->redirectToRoute('panier');
    }


    #[Route(
        '/delete/{id}',
        name: '_delete',
        requirements: ['id' => '[1-9]\d*'],
    )]
    public function deleteAction(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $idUser = $user->getId();
        $panierRepository = $em->getRepository(Panier::class);
        $panierUser =  $panierRepository->findOneBy(['Client'=>$idUser]);

        $panierproduitRepository = $em->getRepository(PanierProduit::class);
        $panierproduit = $panierproduitRepository->findOneBy(['panier' => $panierUser, 'produit' => $id]);

        if (is_null($panierproduit))
            throw new NotFoundHttpException('erreur suppression de produit dans le panier ' );

        $em->remove($panierproduit);
        $em->flush();
        $this->addFlash('info', 'suppression de votre panier de réussie');

        return $this->redirectToRoute('panier');
    }

    #[Route(
        '/vider',
        name : '_vider',
    )]
    public function videPanier( EntityManagerInterface $em): Response
    {
        $id = $this->getUser()->getId();
        $panierRepository = $em->getRepository(Panier::class);
        $panier = $panierRepository->findOneBy(['Client'=> $id]);
        $panierProduits = $panier->getPaniersProduits();

        foreach ($panierProduits as $panierProduit) {
            $em->remove($panierProduit);
        }

        $em->flush();

        return $this->redirectToRoute('panier');
    }

    #[Route(
        '/commander',
        name: '_commander',
    )]
    public function commanderPanier(EntityManagerInterface $em): Response
    {
        $id = $this->getUser()->getId();
        $panierRepository = $em->getRepository(Panier::class);
        $panier = $panierRepository->findOneBy(['Client' => $id]);
        $panierProduits = $panier->getPaniersProduits()->toArray();

        foreach ($panierProduits as $panierProduit) {
            $this->updateStockPanierProduit($panierProduit, $em);
            $em->remove($panierProduit);
        }
        $em->flush();
        return $this->redirectToRoute('panier');
    }

    private function updateStockPanierProduit(PanierProduit $panierProduit, EntityManagerInterface $em)
    {
        $MagasinProduitRepository = $em->getRepository(ProduitMagasin::class);
        $ProduitDuMagasin = $MagasinProduitRepository->findBy(['produit' => $panierProduit->getProduit()]);
        $QuantitePanier = $panierProduit->getQuantite();

        foreach ($ProduitDuMagasin as $stock) {
            $QuantiteMax = $stock->getQuantite();
            $this->updateStock($stock, $QuantiteMax, $QuantitePanier, $em);
            if ( $QuantitePanier >=$QuantiteMax)
            {$QuantitePanier = $QuantitePanier - $QuantiteMax;
             $em->remove($stock);
                $em->flush();
            }
            if ($QuantitePanier <= 0){ break;}
        }
    }

    private function updateStock(ProduitMagasin $stock, $QuantiteMax, $QuantitePanier, EntityManagerInterface $em)
    {
        if ($QuantitePanier >= $QuantiteMax) {
            $stock->setQuantite(0);
            $em->persist($stock);
        } else {
            $stock->setQuantite($QuantiteMax - $QuantitePanier);
            $em->persist($stock);
        }
        $em->flush();
    }


}
