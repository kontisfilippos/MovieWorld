<?php

namespace App\Repository;

use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vote>
 *
 * @method Boolean isVoteUnique(Vote $vote)
 * @method Vote[] findOneByUserIdAndMovieIdField($movieId, $userId)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function isVoteUnique(Vote $vote): bool
    {
        $existingVote = $this->findOneBy([
            'user_id' => $vote->getUserId(),
            'movie_id' => $vote->getMovieId(),
            'vote' => $vote->getVote()
        ]);

        return $existingVote === null;
    }

   public function findOneByUserIdAndMovieIdField($movieId, $userId): ?Vote
   {   
       return $this->createQueryBuilder('v')
            ->andWhere('v.movie_id = :movie_id')
            ->andWhere('v.user_id = :user_id')
            ->setParameter('movie_id', $movieId)
            ->setParameter('user_id', $userId)
            ->getQuery()
            ->getOneOrNullResult()
       ;
   }
}
