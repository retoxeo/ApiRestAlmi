<?php

namespace App\Entity;

use App\Repository\FotosTiendaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FotosTiendaRepository::class)]
class FotosTienda
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\ManyToOne(inversedBy: 'fotosTiendas')]
    private ?Tienda $tienda = null;

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

    public function getTienda(): ?Tienda
    {
        return $this->tienda;
    }

    public function setTienda(?Tienda $tienda): static
    {
        $this->tienda = $tienda;

        return $this;
    }
}
