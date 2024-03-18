<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\User;
use App\Form\MovieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{   
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/movies', name: 'app_movies')]
    public function getMovies(): Response
    {   
        $user = $this->getUser();
        // Get the repository for the Movie entity
        $movieRepository = $this->entityManager->getRepository(Movie::class);
        $likeIds = [];
        $hateIds = [];
        if ($user instanceof User && $user->getId() !== null) {
            $currentUserLikedMovies = $movieRepository->findByLikesByUser($user->getId());
            foreach ($currentUserLikedMovies as $movie) {
                $likeIds[] = $movie->getId();
            }

            $currentUserHatedMovies = $movieRepository->findByHatesByUser($user->getId());
            foreach ($currentUserHatedMovies as $movie) {
                $hateIds[] = $movie->getId();
            }
        }
        // Get movies data sorted by date, using DESC order if specified
        $movies = $movieRepository->findBy([], ['date_added' => 'DESC']);
        $moviesCount = count($movies);
        // Render the movies template and pass the movies data
        return $this->render('movie/index.html.twig', [
            'movies' => $movies,
            'movies_count'=> $moviesCount,
            'like_ids' => $likeIds,
            'hate_ids' => $hateIds,
            'user'=> $user,
        ]);
    }

    #[Route('/movies/like_sort', name: 'app_movies_like_sort')]
    public function getMoviesSortedByLikes(): Response
    {   
        $user = $this->getUser();
       
        // Get the repository for the Movie entity
        $movieRepository = $this->entityManager->getRepository(Movie::class);
        // Get movies data sorted by date, using DESC order if specified
        $movies = $movieRepository->findBy([], ['likes' => 'DESC']);
        $likeIds = [];
        $hateIds = [];
        if ($user instanceof User && $user->getId() !== null) {
            $currentUserLikedMovies = $movieRepository->findByLikesByUser($user->getId());
            foreach ($currentUserLikedMovies as $movie) {
                $likeIds[] = $movie->getId();
            }

            $currentUserHatedMovies = $movieRepository->findByHatesByUser($user->getId());
            foreach ($currentUserHatedMovies as $movie) {
                $hateIds[] = $movie->getId();
            }
        }
        $moviesCount = count($movies);
 
        // Render the movies template and pass the movies data
        return $this->render('movie/index.html.twig', [
            'movies' => $movies,
            'movies_count'=> $moviesCount,
            'like_ids' => $likeIds,
            'hate_ids' => $hateIds,
            'user'=> $user,
        ]);
    }

    #[Route('/movies/dislike_sort', name: 'app_movies_dislike_sort')]
    public function getMoviesSortedByDislikes(): Response
    {   
        $user = $this->getUser();
        // Get the repository for the Movie entity
        $movieRepository = $this->entityManager->getRepository(Movie::class);
        // Get movies data sorted by date, using DESC order if specified
        $movies = $movieRepository->findBy([], ['dislikes' => 'DESC']);
        $likeIds = [];
        $hateIds = [];
        if ($user instanceof User && $user->getId() !== null) {
            $currentUserLikedMovies = $movieRepository->findByLikesByUser($user->getId());
            foreach ($currentUserLikedMovies as $movie) {
                $likeIds[] = $movie->getId();
            }

            $currentUserHatedMovies = $movieRepository->findByHatesByUser($user->getId());
            foreach ($currentUserHatedMovies as $movie) {
                $hateIds[] = $movie->getId();
            }
        }
        $moviesCount = count($movies);
 
        // Render the movies template and pass the movies data
        return $this->render('movie/index.html.twig', [
            'movies' => $movies,
            'movies_count'=> $moviesCount,
            'like_ids' => $likeIds,
            'hate_ids' => $hateIds,
            'user'=> $user,
        ]);
    }

    #[Route('/movie/add', name: 'movie_add')]
    public function addMovie(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the current user
        $user = $this->getUser();

        // Check if the user is authenticated
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Create a new instance of the Movie entity
        $movie = new Movie();

        // Set the user ID of the current user
        $movie->setUser($user);

        // Handle form submission
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the movie to the database
            $entityManager->persist($movie);
            $entityManager->flush();

            // Redirect to a success page or show a success message
            return $this->redirectToRoute('app_movies');
        }

        // Render the add movie form template
        return $this->render('movie/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
