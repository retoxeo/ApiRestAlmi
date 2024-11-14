<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductoRepository::class)]
class Producto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column]
    private ?float $precio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fecha_lanzamiento = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $desarrollador = null;

    /**
     * @var Collection<int, FotosProducto>
     */
    #[ORM\OneToMany(targetEntity: FotosProducto::class, mappedBy: 'producto')]
    private Collection $fotosProductos;

    /**
     * @var Collection<int, PedidoProducto>
     */
    #[ORM\OneToMany(targetEntity: PedidoProducto::class, mappedBy: 'producto')]
    private Collection $pedidoProductos;

    /**
     * @var Collection<int, ProductoTienda>
     */
    #[ORM\OneToMany(targetEntity: ProductoTienda::class, mappedBy: 'producto')]
    private Collection $productoTiendas;

    public function __construct()
    {
        $this->fotosProductos = new ArrayCollection();
        $this->pedidoProductos = new ArrayCollection();
        $this->productoTiendas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getFechaLanzamiento(): ?\DateTimeInterface
    {
        return $this->fecha_lanzamiento;
    }

    public function setFechaLanzamiento(\DateTimeInterface $fecha_lanzamiento): static
    {
        $this->fecha_lanzamiento = $fecha_lanzamiento;

        return $this;
    }

    public function getDesarrollador(): ?string
    {
        return $this->desarrollador;
    }

    public function setDesarrollador(?string $desarrollador): static
    {
        $this->desarrollador = $desarrollador;

        return $this;
    }

    /**
     * @return Collection<int, FotosProducto>
     */
    public function getFotosProductos(): Collection
    {
        return $this->fotosProductos;
    }

    public function addFotosProducto(FotosProducto $fotosProducto): static
    {
        if (!$this->fotosProductos->contains($fotosProducto)) {
            $this->fotosProductos->add($fotosProducto);
            $fotosProducto->setProducto($this);
        }

        return $this;
    }

    public function removeFotosProducto(FotosProducto $fotosProducto): static
    {
        if ($this->fotosProductos->removeElement($fotosProducto)) {
            // set the owning side to null (unless already changed)
            if ($fotosProducto->getProducto() === $this) {
                $fotosProducto->setProducto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PedidoProducto>
     */
    public function getPedidoProductos(): Collection
    {
        return $this->pedidoProductos;
    }

    public function addPedidoProducto(PedidoProducto $pedidoProducto): static
    {
        if (!$this->pedidoProductos->contains($pedidoProducto)) {
            $this->pedidoProductos->add($pedidoProducto);
            $pedidoProducto->setProducto($this);
        }

        return $this;
    }

    public function removePedidoProducto(PedidoProducto $pedidoProducto): static
    {
        if ($this->pedidoProductos->removeElement($pedidoProducto)) {
            // set the owning side to null (unless already changed)
            if ($pedidoProducto->getProducto() === $this) {
                $pedidoProducto->setProducto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductoTienda>
     */
    public function getProductoTiendas(): Collection
    {
        return $this->productoTiendas;
    }

    public function addProductoTienda(ProductoTienda $productoTienda): static
    {
        if (!$this->productoTiendas->contains($productoTienda)) {
            $this->productoTiendas->add($productoTienda);
            $productoTienda->setProducto($this);
        }

        return $this;
    }

    public function removeProductoTienda(ProductoTienda $productoTienda): static
    {
        if ($this->productoTiendas->removeElement($productoTienda)) {
            // set the owning side to null (unless already changed)
            if ($productoTienda->getProducto() === $this) {
                $productoTienda->setProducto(null);
            }
        }

        return $this;
    }
}
