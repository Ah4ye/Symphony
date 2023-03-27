<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Table(name: 'i23_paniers')]
#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;


    #[ORM\OneToOne( cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(
        name: 'client_id',
        referencedColumnName: 'id',    // inutile : valeur par défaut
        unique: true,                  // 1 seul panier par client
        nullable: true,                // panier vide/pas de panier
        options: ['default' => null],  // pas de panier par default
    )]
    private ?User $Client = null;


    #[ORM\OneToMany(mappedBy: 'id_panier', targetEntity: ProduitMagasin::class)]
    #[Assert\Valid]
    private Collection $paniersproduits;
    public function __construct()
    {
        $this->Panier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getPanier(): Collection
    {
        return $this->Panier;
    }

    public function addPanier(Produit $panier): self
    {
        if (!$this->Panier->contains($panier)) {
            $this->Panier->add($panier);
        }

        return $this;
    }

    public function removePanier(Produit $panier): self
    {
        $this->Panier->removeElement($panier);

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->Client;
    }

    public function setClient(?User $Client): self
    {
        $this->Client = $Client;

        return $this;
    }
}
