<?php

namespace App\Entity;

use App\Repository\ConsolaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsolaRepository::class)]
class Consola
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $almacenamiento = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Producto $producto = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlmacenamiento(): ?string
    {
        return $this->almacenamiento;
    }

    public function setAlmacenamiento(string $almacenamiento): static
    {
        $this->almacenamiento = $almacenamiento;

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
}
