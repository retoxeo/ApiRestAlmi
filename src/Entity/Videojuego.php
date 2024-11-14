<?php

namespace App\Entity;

use App\Repository\VideojuegoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideojuegoRepository::class)]
class Videojuego
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $precio_alquiler = null;

    #[ORM\Column(nullable: true)]
    private ?int $pegi = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Producto $producto = null;

    /**
     * @var Collection<int, Genero>
     */
    #[ORM\ManyToMany(targetEntity: Genero::class, inversedBy: 'videojuegos')]
    private Collection $generos; // Renombrado para eliminar redundancia

    public function __construct()
    {
        $this->generos = new ArrayCollection(); // Solo una colección de generos
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrecio_alquiler(): ?float
    {
        return $this->precio_alquiler;
    }

    public function setPrecio_alquiler(float $precio_alquiler): static
    {
        $this->precio_alquiler = $precio_alquiler;

        return $this;
    }

    public function getPegi(): ?int
    {
        return $this->pegi;
    }

    public function setPegi(?int $pegi): static
    {
        $this->pegi = $pegi;

        return $this;
    }

    public function getProducto(): ?Producto
    {
        return $this->producto;
    }

    public function setProducto(?Producto $producto): static
    {
        $this->producto = $producto;

        return $this;
    }

    /**
     * @return Collection<int, Genero>
     */
    public function getGeneros(): Collection
    {
        return $this->generos;
    }

    public function addGenero(Genero $genero): static
    {
        if (!$this->generos->contains($genero)) {
            $this->generos->add($genero);
            $genero->addVideojuego($this); // Sincronización bidireccional
        }

        return $this;
    }

    public function removeGenero(Genero $genero): static
    {
        if ($this->generos->removeElement($genero)) {
            $genero->removeVideojuego($this); // Sincronización bidireccional
        }

        return $this;
    }
}
