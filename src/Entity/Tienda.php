<?php

namespace App\Entity;

use App\Repository\TiendaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TiendaRepository::class)]
class Tienda
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $pais = null;

    #[ORM\Column(length: 255)]
    private ?string $provincia = null;

    #[ORM\Column]
    private ?int $cp = null;

    #[ORM\Column(length: 255)]
    private ?string $ciudad = null;

    #[ORM\Column(length: 255)]
    private ?string $calle = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\Column(length: 255)]
    private ?string $telefono = null;

    #[ORM\Column(length: 255)]
    private ?string $correo = null;

    /**
     * @var Collection<int, FotosTienda>
     */
    #[ORM\OneToMany(targetEntity: FotosTienda::class, mappedBy: 'tienda')]
    private Collection $fotosTiendas;

    /**
     * @var Collection<int, ProductoTienda>
     */
    #[ORM\OneToMany(targetEntity: ProductoTienda::class, mappedBy: 'tienda')]
    private Collection $productoTiendas;

    public function __construct()
    {
        $this->fotosTiendas = new ArrayCollection();
        $this->productoTiendas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(string $pais): static
    {
        $this->pais = $pais;

        return $this;
    }

    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    public function setProvincia(string $provincia): static
    {
        $this->provincia = $provincia;

        return $this;
    }

    public function getCp(): ?int
    {
        return $this->cp;
    }

    public function setCp(int $cp): static
    {
        $this->cp = $cp;

        return $this;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(string $ciudad): static
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getCalle(): ?string
    {
        return $this->calle;
    }

    public function setCalle(string $calle): static
    {
        $this->calle = $calle;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): static
    {
        $this->correo = $correo;

        return $this;
    }

    /**
     * @return Collection<int, FotosTienda>
     */
    public function getFotosTiendas(): Collection
    {
        return $this->fotosTiendas;
    }

    public function addFotosTienda(FotosTienda $fotosTienda): static
    {
        if (!$this->fotosTiendas->contains($fotosTienda)) {
            $this->fotosTiendas->add($fotosTienda);
            $fotosTienda->setTienda($this);
        }

        return $this;
    }

    public function removeFotosTienda(FotosTienda $fotosTienda): static
    {
        if ($this->fotosTiendas->removeElement($fotosTienda)) {
            // set the owning side to null (unless already changed)
            if ($fotosTienda->getTienda() === $this) {
                $fotosTienda->setTienda(null);
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
            $productoTienda->setTienda($this);
        }

        return $this;
    }

    public function removeProductoTienda(ProductoTienda $productoTienda): static
    {
        if ($this->productoTiendas->removeElement($productoTienda)) {
            // set the owning side to null (unless already changed)
            if ($productoTienda->getTienda() === $this) {
                $productoTienda->setTienda(null);
            }
        }

        return $this;
    }
}
