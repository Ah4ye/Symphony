<?php

namespace App\Entity\Sandbox;

use App\Repository\Sandbox\CritiqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'sb_critiques')]
#[ORM\Entity(repositoryClass: CritiqueRepository::class)]
class Critique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(
        type: Types::INTEGER,
        nullable: true,
        options: ['default' => null, 'comment' => 'entre 0 et 5'],
    )]
    #[Assert\Range(
        notInRangeMessage: 'la note doit être comprise entre {{ min }} et {{ max }}',
        min: 0,
        max: 5,
    )]
    private ?int $note = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(
        min: 3,
        minMessage: 'Il faut au moins {{ limit }} caractères',
    )]
    private ?string $avis = null;

    #[ORM\ManyToOne(targetEntity: Film::class, inversedBy: 'critiques')]
    #[ORM\JoinColumn(name: 'id_film', nullable: false)]
    #[Assert\NotNull]
    #[Assert\Valid]
    private ?Film $film = null;


    /**
     * Critique constructor
     */
    public function __construct()
    {
        $this->note = null;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getAvis(): ?string
    {
        return $this->avis;
    }

    public function setAvis(string $avis): self
    {
        $this->avis = $avis;

        return $this;
    }

    public function getFilm(): ?Film
    {
        return $this->film;
    }

    public function setFilm(?Film $film): self
    {
        $this->film = $film;

        return $this;
    }
}
