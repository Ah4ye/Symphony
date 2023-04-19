<?php

namespace App\Controller;

use App\Form\CreatProductType;
use App\Repository\ProduitRepository;
use App\DataFixtures\VenteFixtures;
use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Entity\Pays;
use App\Entity\Produit;
use App\Entity\ProduitMagasin;
use App\Form\ProduitMagasinType;
use App\Form\ProduitPaysType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produit', name: 'produit')]
class ProduitController extends AbstractController
{
    #[Route('', name: '')]
    public function indexAction(): Response
    {
        return $this->redirectToRoute('produit_list');
    }

    #[Route(
        '/list/{page}',
        name: '_list',
        requirements: ['page' => '[1-9]\d*'],
        defaults: [ 'page' => 1],
    )]
    public function listAction(int $page, EntityManagerInterface $em): Response
    {
        $produitRepository = $em->getRepository(Produit::class);
        $produits = $produitRepository->findAll();
        $MagasinProduitRepository = $em->getRepository(ProduitMagasin::class);
        $PrixUnit = 0 ; $quantite = 0 ;
        $products = array() ;
        if ( !empty($produits)) {
            foreach ($produits as $produit) {
                $PrixUnit = 0 ; $quantite = 0 ;
                $denomination =$produit->getDenomination();

                $ProduitDuMagasin = $MagasinProduitRepository->findBy(['produit'=>$produit]);
                foreach ($ProduitDuMagasin as $stock) {
                    $quantite += $stock->getQuantite();
                    $PrixUnit = $stock->getPrixUnitaire();
                }

                $id = $produit->getId();
                $product = ['id' => $id ,
                    'denomination' => $denomination,
                    'quantite' => $quantite,
                    'Prix' => $PrixUnit,
                ];
                array_push($products,$product);

            }
        }
        $args = array(
            'page' => $page,
            'produits' => $products,
        );
        return $this->render('Produit/list.html.twig', $args);
    }

    #[Route(
        '/view/{id}',
        name: '_view',
        requirements: ['id' => '[1-9]\d*'],
    )]
    public function viewAction(int $id, EntityManagerInterface $em): Response
    {
        $produitRepository = $em->getRepository(Produit::class);
        $produit = $produitRepository->find($id);

        if (is_null($produit))
        {
            $this->addFlash('info', 'view : produit ' . $id . ' inexistant');
            return $this->redirectToRoute('produit_list');
        }

        $args = array(
            'produit' => $produit,
        );
        return $this->render('Produit/view.html.twig', $args);
    }

    #[Route(
        '/add_fixture',
        name: '_add_fixture',
    )]
    public function addAction(EntityManagerInterface $em , VenteFixtures $venteFixtures): Response
    {
        $venteFixtures->load($em);
        $em->flush();
        return $this->redirectToRoute('produit_view', ['id' => 3]);
    }

    #[Route(
        '/add_product',
        name: '_add_product',
    )]
    public function addProductAction(EntityManagerInterface $em ,Request $request ): Response
    {
        $produit = new Produit();
        $form = $this->createForm(CreatProductType::class,$produit);
        $form->add('send', SubmitType::class, [
            'label' => 'CREATION',
            'attr' => ['class' => 'btn btn-warning']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();
            $pays = $form->get('pays')->getData();
            $manuel = $form->get('manuel')->getData();
            if (! $produit->getPayss()->contains($pays)) {$pays->addProduit($produit);}
            if ($manuel) {$produit->setManuel($manuel);}
            $args = array(
                'produit' => $produit,
            );
            return $this->render('Produit/view.html.twig', $args);
        }
        if ($form->isSubmitted()){
            $this->addFlash('info', 'formulaire Produit incorrect');}

        $args = array(
            'form' => $form->createView(),
        );
        return $this->render('Produit/add_product.html.twig',$args);
    }

    #[Route(
        '/add_panier/{id}',
        name: '_add_panier',
        requirements: ['id' => '[1-9]\d*'],

    )]
    public function addPanierAction(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $quantite = $request->request->get('quantite', 1);
        // Default : entree preselectionnee a “0”
        $user = $this->getUser()->getId();
        $produitRepository = $em->getRepository(Produit::class);
        $Produit =$produitRepository->find(['id'=>$id]);
        $panierRepository = $em->getRepository(Panier::class);
        $Panier =$panierRepository->findOneBy(['Client'=>$user]);


        $quantiteMax = 0 ;
        // Recherche de la quantité max autorisé
        $MagasinProduitRepository = $em->getRepository(ProduitMagasin::class);
        $ProduitDuMagasin = $MagasinProduitRepository->findBy(['produit'=>$Produit]);
        foreach ($ProduitDuMagasin as $stock) {
            $quantiteMax += $stock->getQuantite();
        }

        if(is_null($Panier)){ // Cas ou panier pas n'est pas crée
            $this->addFlash('info', 'Panier crée et Produit Ajouter');
            $CreatePanier = new Panier();
            $CreatePanier->setClient($this->getUser());

            $CreatePanierProduit = new PanierProduit();
            $CreatePanierProduit->setPanier($CreatePanier);
            $CreatePanierProduit->setProduit($Produit);
            if ( $quantite > 0 && ($quantite <= $quantiteMax)){
            $CreatePanierProduit->setQuantite($quantite);}
            else{  $this->addFlash('info', 'On respecte les stocks svp');   }

            $em->persist($CreatePanier);
            $em->persist($CreatePanierProduit);
            $em->flush();
        }
        else{ // Cas ou le Client possède deja un panier
            $PanierProduitRepository = $em->getRepository(PanierProduit::class);
            $PanierProduit = $PanierProduitRepository->findOneBy([
                'panier' => $Panier,
                'produit' => $Produit,

            ]);
            if ( is_null($PanierProduit)){ // Cas ou le produit n'est pas deja present dans le panier

                $CreatePanierProduit = new PanierProduit();
                $CreatePanierProduit->setPanier($Panier);
                $CreatePanierProduit->setProduit($Produit);
                if ( $quantite > 0 && ($quantite <= $quantiteMax)){
                $CreatePanierProduit->setQuantite($quantite);
                $this->addFlash('info', 'Produit crée au panier');
                $em->persist($CreatePanierProduit);
                $em->flush();}
                else {  $this->addFlash('info', 'On respecte les stocks svp');}
            }
            else{ // Cas ou le produit est déja present dans le panier
                $this->addFlash('info', 'Produit additionner au panier');
                $New_Quantite = $PanierProduit->getQuantite() + $quantite;
                if ( $New_Quantite <= 0){
                    return $this->redirectToRoute('panier_delete', ['id' => $id]);}
                $PanierProduit->setQuantite($New_Quantite);
                $em->persist($PanierProduit);
                $em->flush();
            }
        }


        return $this->redirectToRoute('produit_list');
    }

    #[Route(
        '/edit/{id}',
        name: '_edit',
        requirements: ['id' => '[1-9]\d*'],
    )]
    public function editAction(int $id): Response
    {
        $this->addFlash('info', 'échec modification produit ' . $id);
        return $this->redirectToRoute('produit_view', ['id' => $id]);
   }

    #[Route(
        '/delete/{id}',
        name: '_delete',
        requirements: ['id' => '[1-9]\d*'],
    )]
    public function deleteAction(int $id, EntityManagerInterface $em): Response
    {
        $produitRepository = $em->getRepository(Produit::class);
        $produit = $produitRepository->find($id);

        if (is_null($produit))
            throw new NotFoundHttpException('erreur suppression produit ' . $id);

        $em->remove($produit);
        $em->flush();
        $this->addFlash('info', 'suppression produit ' . $id . ' réussie');

        return $this->redirectToRoute('produit_list');
    }

    #[Route(
        '/pays/add',
        name: '_pays_add',
    )]
    public function paysAddAction(EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(ProduitPaysType::class);
        $form->add('send', SubmitType::class, ['label' => 'add produit/pays']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
             /* @var Produit $produit */
             /* @var Pays $pays */
            $produit = $form->get('produit')->getData();
            $pays = $form->get('pays')->getData();
            if (! $produit->getPayss()->contains($pays))
            {
                //$produit->addPays($pays);             // ne met pas à jour l'objet $pays
                $pays->addProduit($produit);            // met à jour les deux entités
                $em->flush();
                $this->addFlash('info', 'ajout produit/pays réussi');
                return $this->redirectToRoute('produit_view', ['id' => $produit->getId()]);
            }
        }

        if ($form->isSubmitted())
        {
            $this->addFlash('info', 'erreur formulaire produit/pays');
            if ($form->isValid())
                $form->addError(new FormError('l\'association existe déjà'));
        }

        $args = array(
            'myform' => $form->createView(),
        );
        return $this->render('Produit/pays_add.html.twig', $args);
    }

    #[Route(
        '/magasin/add',
        name: '_magasin_add',
    )]
    public function magasinAddAction(EntityManagerInterface $em, Request $request): Response
    {
        $produitMagasin = new ProduitMagasin();

        $form = $this->createForm(ProduitMagasinType::class, $produitMagasin);
        $form->add('send', SubmitType::class, ['label' => 'add produit/magasin']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($produitMagasin);
            $em->flush();
            $this->addFlash('info', 'ajout produit/magasin réussi');
            return $this->redirectToRoute('produit_view', ['id' => $produitMagasin->getProduit()->getId()]);
        }

        if ($form->isSubmitted())
            $this->addFlash('info', 'erreur formulaire produit/magasin');

        $args = array(
            'myform' => $form->createView(),
        );
        return $this->render('Produit/magasin_add.html.twig', $args);
    }



    /**
     * test de QueryBuilder
     */
    #[Route(
        '/recherche',
        name: '_recherche',
    )]
    public function rechercherProduit(Request $request, ProduitRepository $produitRepository, EntityManagerInterface $em)
    {
        $searchQuery = $request->query->get('search_query');
        $MagasinProduitRepository = $em->getRepository(ProduitMagasin::class);
        $produits = $produitRepository->findBy(['denomination' => $searchQuery]);

        $products = array();
        if ( !empty($produits)) {
            foreach ($produits as $produit) {
                $PrixUnit = 0 ; $quantite = 0 ;
                $denomination =$produit->getDenomination();

                $ProduitDuMagasin = $MagasinProduitRepository->findBy(['produit'=>$produit]);
                foreach ($ProduitDuMagasin as $stock) {
                    $quantite += $stock->getQuantite();
                    $PrixUnit = $stock->getPrixUnitaire();
                }
                $id = $produit->getId();
                $product = ['id' => $id ,
                    'denomination' => $denomination,
                    'quantite' => $quantite,
                    'Prix' => $PrixUnit,
                ];
                array_push($products,$product);

            }
        }
        $args = array(
            'page' => 1,
            'produits' => $products,
        );

        return $this->render('Produit/list.html.twig', $args);
    }

    #[Route(
        '/viewQB/{id}/{method}',
        name: '_view_qb',
        requirements: [
            'id' => '[1-9]\d*',
            'method' => 'avec|sans',
        ],
    )]
    public function viewQB(int $id, string $method, EntityManagerInterface $em)
    {
        $produitRepository = $em->getRepository(Produit::class);

        if ($method === 'avec')
            $produit = $produitRepository->findWithMagasins($id);
        else
            $produit = $produitRepository->find($id);
        if (is_null($produit))
            throw new NotFoundHttpException('erreur viewQB produit ' . $id);

        $args = array(
            'method' => $method,
            'produit' => $produit,
        );
        return $this->render('Produit/viewQB.html.twig', $args);
    }
}
