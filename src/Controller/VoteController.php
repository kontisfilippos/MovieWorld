<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\Movie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class VoteController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[Route('/vote', name: 'app_vote')]
    public function addVote(Request $request): Response
    {   
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['user_id'], $data['movie_id'], $data['vote'])) {
            $entityManager = $this->entityManager;
            // Process the vote data
            $userId = intval($data['user_id']);
            $movieId = intval($data['movie_id']);
            $voteType = $data['vote'];
            
            if ($voteType != "" && $voteType != "like" && $voteType != "hate") {
                return new JsonResponse(['error' => 'Vote not accepted'], Response::HTTP_BAD_REQUEST);
            }
            
            $userRepository = $entityManager->getRepository(User::class);
            $user = $userRepository->find($userId);
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            $movieRepository = $this->entityManager->getRepository(Movie::class);
            $movie = $movieRepository->findOneByMovieId(['id' => $movieId]);
            if (!$movie) {
                return new JsonResponse(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND);
            }

            $voteRepository = $this->entityManager->getRepository(Vote::class);
            $vote = $voteRepository->findOneByUserIdAndMovieIdField($movieId, $userId);
            
            if (!$vote) {
                if ($voteType == 'like') {
                    $likes = $movie->getLikes() + 1;
                    $movie->setLikes($likes);
                }
                else if ($voteType == 'hate') {
                    $hates = $movie->getDislikes() + 1;
                    $movie->setDislikes($hates);
                }
                $vote = new Vote();
            }
            else {
                if ($voteType == 'like' && $vote->getVote() != '') {
                    $likes = $movie->getLikes() + 1;
                    $hates = $movie->getDislikes() - 1;
                    $movie->setLikes($likes);
                    $movie->setDislikes($hates);
                }
                else if ($voteType == 'hate' && $vote->getVote() != '') {
                    $hates = $movie->getDislikes() + 1;
                    $likes = $movie->getLikes() - 1;
                    $movie->setDislikes($hates);
                    $movie->setLikes($likes);
                }
                else if ($voteType == 'like' && $vote->getVote() == '') {
                    $likes = $movie->getLikes() + 1;
                    $movie->setLikes($likes);
                }
                else if ($voteType == 'hate' && $vote->getVote() == '') {
                    $hates = $movie->getDislikes() + 1;
                    $movie->setDislikes($hates);
                }
                else {
                    if ($vote->getVote() == 'like') {
                        $likes = $movie->getLikes() - 1;
                        $movie->setLikes($likes);
                    }
                    else
                    {   
                        $hates = $movie->getDislikes() - 1;
                        $movie->setDislikes($hates);
                    }
                }
            }
            
            $entityManager->persist($movie);
            
            // Now you can use $userId, $movieId, and $voteType as needed
    
            // Example: Save the vote to the database
            $vote->setUserId($user);
            $vote->setMovieId($movie);
            $vote->setVote($voteType);
            $entityManager->persist($vote);
            $entityManager->flush();
    
            // Return a JSON response indicating success
            return new JsonResponse(['message' => 'Vote received successfully'], Response::HTTP_OK);
        }
    
        // If the request data is incomplete or missing, return an error response
        return new JsonResponse(['error' => 'Incomplete or missing vote data'], Response::HTTP_BAD_REQUEST);
    }
}
