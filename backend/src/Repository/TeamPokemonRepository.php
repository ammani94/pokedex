<?php

namespace App\Repository;

use App\Entity\TeamPokemon;
use App\Entity\Pokemons;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamPokemon>
 */
class TeamPokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamPokemon::class);
    }

    //    /**
    //     * @return TeamPokemon[] Returns an array of TeamPokemon objects
    //     */
    public function GetPokemonWithoutTeams($userID): array
{
    return $this->createQueryBuilder('p')
        ->select('tp')
        ->leftjoin(
            TeamPokemon::class,
            'tp',
            \Doctrine\ORM\Query\Expr\Join::WITH,
            'p.id = tp.pokemon_id'
        )
        ->andWhere('p.user_id = :userID')
        ->andWhere('tp.id IS NULL')
        ->setParameter('userID', $userID)
        ->getQuery()
        ->getResult();
}


public function GetPokemonWithTeams($userID, $team): array
{
    return $this->createQueryBuilder('t')
        ->select('p')
        ->join(
            Pokemons::class,
            'p',
            \Doctrine\ORM\Query\Expr\Join::WITH,
            'p.id = t.pokemon_id'
        )
        ->andWhere('p.user_id = :userID')
        ->setParameter('userID', $userID)
        ->andWhere('t.team_id = :team')
        ->setParameter('team', $team)
        ->getQuery()
        ->getResult();
}

    //    public function findOneBySomeField($value): ?TeamPokemon
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
