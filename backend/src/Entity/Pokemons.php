<?php

namespace App\Entity;

use App\Repository\PokemonsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokemonsRepository::class)]
class Pokemons
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $api_id = null;

    #[ORM\Column]
    private ?int $user_id = null;

    #[ORM\Column]
    private ?\DateTime $captured_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiId(): ?int
    {
        return $this->api_id;
    }

    public function setApiId(int $api_id): static
    {
        $this->api_id = $api_id;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCapturedAt(): ?\DateTime
    {
        return $this->captured_at;
    }

    public function setCapturedAt(\DateTime $captured_at): static
    {
        $this->captured_at = $captured_at;

        return $this;
    }
}
