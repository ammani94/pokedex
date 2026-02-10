<?php

namespace App\Entity;

use App\Repository\TeamPokemonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamPokemonRepository::class)]
class TeamPokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $team_id = null;

    #[ORM\Column]
    private ?int $pokemon_id = null;

    #[ORM\Column]
    private ?int $position = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeamId(): ?int
    {
        return $this->team_id;
    }

    public function setTeamId(int $team_id): static
    {
        $this->team_id = $team_id;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
