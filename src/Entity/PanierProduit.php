<?php

namespace App\Entity;

use App\Repository\PanierProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Table(name: 'i23_paniers_produits')]
#[ORM\Entity(repositoryClass: PanierProduitRepository::class)]


class PanierProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\Range(
        minMessage: 'la quantité minimale est {{ limit }}',
        min: 0,
    )]
    private ?int $quantite = null;


    #[ORM\ManyToOne(targetEntity: Panier::class, inversedBy: 'paniersproduits')]
    #[ORM\JoinColumn(name: 'id_panier', referencedColumnName: 'id',
        nullable: false,                // si il y a des produits,
        //Alors obligatoirement il y a un panier

    )]
    #[Assert\NotNull]
    #[Assert\Valid]
    private ?Panier $panier;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'paniersproduits')]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id',nullable: false)]
    #[Assert\NotNull]
    #[Assert\Valid]
    private ?Produit $produit ;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        // ne faudrait-il pas appeler $produit->addProduitMagasin($this); ?

        return $this;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): self
    {
        $this->panier = $panier;
        // ne faudrait-il pas appeler $magasin->addProduitMagasin($this); ?

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }
}