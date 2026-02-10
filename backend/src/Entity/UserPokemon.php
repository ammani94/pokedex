<?php

namespace App\Entity;

use App\Repository\UserPokemonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPokemonRepository::class)]
class UserPokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $user_id = null;

    #[ORM\Column]
    private ?int $pokemon_id = null;

    #[ORM\Column]
    private ?\DateTime $captured_at = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPokemonId(): ?int
    {
        return $this->pokemon_id;
    }

    public function setPokemonId(int $pokemon_id): static
    {
        $this->pokemon_id = $pokemon_id;

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
