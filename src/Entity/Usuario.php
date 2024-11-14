<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("user:read", "incidencia:read")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups("user:read", "incidencia:read")]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    #[Groups("user:read")]
    private ?string $apellido1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("user:read")]
    private ?string $apellido2 = null;

    #[ORM\Column(length: 255)]
    #[Groups("user:read")]
    private ?string $correo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("user:read")]
    private ?string $telefono = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("user:read")]
    private ?string $foto = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("user:read")]
    private ?string $pais = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("user:read")]
    private ?string $provincia = null;

    #[ORM\Column(nullable: true)]
    #[Groups("user:read")]
    private ?int $cp = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("user:read")]
    private ?string $ciudad = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("user:read")]
    private ?string $calle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("user:read")]
    private ?string $numero = null;
    /**
     * @var Collection<int, Incidencia>
     */
    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: Incidencia::class)]
    #[Groups("user:read")]
    private Collection $incidencias;

    /**
     * @var Collection<int, Pedido>
     */
    #[ORM\OneToMany(targetEntity: Pedido::class, mappedBy: 'usuario')]
    private Collection $pedidos;

    #[ORM\Column(length: 255)]
    private ?string $contrasena = null;

    public function __construct()
    {
        $this->incidencias = new ArrayCollection();
        $this->pedidos = new ArrayCollection();
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

    public function getApellido1(): ?string
    {
        return $this->apellido1;
    }

    public function setApellido1(string $apellido1): static
    {
        $this->apellido1 = $apellido1;

        return $this;
    }

    public function getApellido2(): ?string
    {
        return $this->apellido2;
    }

    public function setApellido2(?string $apellido2): static
    {
        $this->apellido2 = $apellido2;

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

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): static
    {
        $this->foto = $foto;

        return $this;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(?string $pais): static
    {
        $this->pais = $pais;

        return $this;
    }

    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    public function setProvincia(?string $provincia): static
    {
        $this->provincia = $provincia;

        return $this;
    }

    public function getCp(): ?int
    {
        return $this->cp;
    }

    public function setCp(?int $cp): static
    {
        $this->cp = $cp;

        return $this;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(?string $ciudad): static
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getCalle(): ?string
    {
        return $this->calle;
    }

    public function setCalle(?string $calle): static
    {
        $this->calle = $calle;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return Collection<int, Incidencia>
     */
    public function getIncidencias(): Collection
    {
        return $this->incidencias;
    }

    public function addIncidencia(Incidencia $incidencia): static
    {
        if (!$this->incidencias->contains($incidencia)) {
            $this->incidencias->add($incidencia);
            $incidencia->setUsuario($this);
        }

        return $this;
    }

    public function removeIncidencia(Incidencia $incidencia): static
    {
        if ($this->incidencias->removeElement($incidencia)) {
            // set the owning side to null (unless already changed)
            if ($incidencia->getUsuario() === $this) {
                $incidencia->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Pedido>
     */
    public function getPedidos(): Collection
    {
        return $this->pedidos;
    }

    public function addPedido(Pedido $pedido): static
    {
        if (!$this->pedidos->contains($pedido)) {
            $this->pedidos->add($pedido);
            $pedido->setUsuario($this);
        }

        return $this;
    }

    public function removePedido(Pedido $pedido): static
    {
        if ($this->pedidos->removeElement($pedido)) {
            // set the owning side to null (unless already changed)
            if ($pedido->getUsuario() === $this) {
                $pedido->setUsuario(null);
            }
        }

        return $this;
    }

    public function getContrasena(): ?string
    {
        return $this->contrasena;
    }

    public function setContrasena(string $contrasena): static
    {
        $this->contrasena = $contrasena;

        return $this;
    }

    
}
