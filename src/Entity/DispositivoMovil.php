<?php

namespace App\Entity;

use App\Repository\DispositivoMovilRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DispositivoMovilRepository::class)]
class DispositivoMovil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $sistema_operativo = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo = null;

    #[ORM\Column(length: 255)]
    private ?string $ram = null;

    #[ORM\Column(length: 255)]
    private ?string $procesador = null;

    #[ORM\Column(length: 255)]
    private ?string $almacenamiento = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Producto $producto = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSistemaOperativo(): ?string
    {
        return $this->sistema_operativo;
    }

    public function setSistemaOperativo(string $sistema_operativo): static
    {
        $this->sistema_operativo = $sistema_operativo;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getRam(): ?string
    {
        return $this->ram;
    }

    public function setRam(string $ram): static
    {
        $this->ram = $ram;

        return $this;
    }

    public function getProcesador(): ?string
    {
        return $this->procesador;
    }

    public function setProcesador(string $procesador): static
    {
        $this->procesador = $procesador;

        return $this;
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
