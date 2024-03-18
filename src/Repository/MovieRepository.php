<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 *
 * @method [] getMoviesWithVoteCounts()
 * @method [] findByLikesByUser($userId)
 * @method [] findByHatesByUser($userId)
 * @method Movie[]|null findOneByMovieId($movie_id)
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function getMoviesWithVoteCounts(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.id', 'm.title', 'm.description', 'm.date_added', 
                'COUNT(DISTINCT CASE WHEN v.vote = :like THEN v.id ELSE 0 END) AS likes', 
                'COUNT(DISTINCT CASE WHEN v.vote = :dislike THEN v.id ELSE 0 END) AS dislikes'
            )
            ->leftJoin('App\Entity\Vote', 'v', 'WITH', 'v.movie_id = m.id')
            ->setParameter('like', 'like')
            ->setParameter('dislike', 'dislike')
            ->groupBy('m.id')
            ->getQuery()
            ->getResult();
    }

    public function findByLikesByUser($userId): array
    {   
        $like = "like";
        return $this->createQueryBuilder('m')
            ->select('m')
            ->leftJoin('App\Entity\Vote', 'v', 'WITH', 'v.movie_id = m.id')
            ->andWhere('v.user_id = :user_id')
            ->andWhere('v.vote = :like')
            ->setParameter('user_id', $userId)
            ->setParameter('like', $like)
            ->getQuery()
            ->getResult();
    }

   public function findByHatesByUser($userId): array
   {    
        $hate = "hate";
        return $this->createQueryBuilder('m')
            ->select('m')
            ->leftJoin('App\Entity\Vote', 'v', 'WITH', 'v.movie_id = m.id')
            ->andWhere('v.user_id = :user_id')
            ->andWhere('v.vote = :like')
            ->setParameter('user_id', $userId)
            ->setParameter('like', $hate)
            ->getQuery()
            ->getResult();
   }

   public function findOneByMovieId($movie_id): ?Movie
   {
       return $this->createQueryBuilder('m')
           ->andWhere('m.id = :val')
           ->setParameter('val', $movie_id)
           ->getQuery()
           ->getOneOrNullResult()
       ;
   }
}
