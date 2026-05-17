<?php

namespace App\Repository;

use App\Entity\Pokemons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\TeamPokemon;
use App\Entity\Users;

/**
 * @extends ServiceEntityRepository<Pokemons>
 */
class PokemonsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokemons::class);
    }

    /**
     * Récupère les Pokémon d'un utilisateur qui ne sont dans aucune équipe.
     *
     * @param int $userId ID de l'utilisateur
     * @return Pokemons[] Tableau des Pokémon non associés à une équipe
     */
    public function getPokemonsWithoutTeams(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin(
                TeamPokemon::class,
                'tp',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'tp.pokemon_id = p.id'
            )
            ->andWhere('p.user_id = :userId')
            ->andWhere('tp.id IS NULL')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Pokemons[] Returns an array of Pokemons objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Pokemons
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
