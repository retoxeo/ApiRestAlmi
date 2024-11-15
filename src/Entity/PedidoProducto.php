<?php

namespace App\Entity;

use App\Repository\PedidoProductoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PedidoProductoRepository::class)]
class PedidoProducto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column]
    private ?float $precio_final = null;

    #[ORM\Column(nullable: true)]
    private ?float $precio_final_alquiler = null;

    #[ORM\ManyToOne(inversedBy: 'pedidoProductos')]
    private ?Pedido $pedido = null;

    #[ORM\ManyToOne(inversedBy: 'pedidoProductos')]
    private ?Producto $producto = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getPrecioFinal(): ?float
    {
        return $this->precio_final;
    }

    public function setPrecioFinal(float $precio_final): static
    {
        $this->precio_final = $precio_final;

        return $this;
    }

    public function getPrecioFinalAlquiler(): ?float
    {
        return $this->precio_final_alquiler;
    }

    public function setPrecioFinalAlquiler(?float $precio_final_alquiler): static
    {
        $this->precio_final_alquiler = $precio_final_alquiler;

        return $this;
    }

    public function getPedido(): ?Pedido
    {
        return $this->pedido;
    }

    public function setPedido(?Pedido $pedido): static
    {
        $this->pedido = $pedido;

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
