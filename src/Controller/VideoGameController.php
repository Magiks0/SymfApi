<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Repository\VideoGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class VideoGameController extends AbstractController
{
    public function __construct(
        private readonly VideoGameRepository $videoGameRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly TagAwareCacheInterface $cachePool,
    ) {}

    #[Route('/api/video-games', name: 'game_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/video-games',
        summary: 'Retrieve a list of video games',
        tags: ['Video Games'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: VideoGame::class, groups: ['game:read']))
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $cacheIdentifier = sprintf("getAllVideoGames-%d-%d", $page, $limit);

        $videoGames = $this->cachePool->get($cacheIdentifier, function (ItemInterface $item) use ($page, $limit) {
            $item->tag("videoGameCache");

            return $this->videoGameRepository->findAllWithPagination($page, $limit);
        });
        return $this->json($videoGames, Response::HTTP_OK, [], ['groups' => 'game:read']);
    }

    #[Route('/api/video-game/new', name: 'game_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: '/api/video-game/new',
        summary: 'Create a new video game',
        security: [['bearerAuth' => []]],
        tags: ['Video Games'],
        requestBody: new OA\RequestBody(
            description: 'Video game data',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: VideoGame::class, groups: ['game:write']))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Video game created',
                content: new OA\JsonContent(ref: new Model(type: VideoGame::class, groups: ['game:read']))
            )
        ]
    )]
    public function new(Request $request, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $game = new VideoGame();
        $videoGameData = json_decode($request->getContent(), true);
        $videoGameData['release_date'] = new \DateTime($videoGameData['release_date']);
        $editor = $this->entityManager->getReference('App\\Entity\\Editor', $videoGameData['editor_id']);
        $game->setTitle($videoGameData['title'])
            ->setEditor($editor)
            ->setReleaseDate($videoGameData['release_date'])
            ->setDescription($videoGameData['description']);
        $this->entityManager->persist($game);
        $this->entityManager->flush();
        $location = $urlGenerator->generate('game_new', ['id' => $game->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        return $this->json($game, Response::HTTP_CREATED, ['Location' => $location], ['groups' => 'game:write']);
    }

    #[Route('/api/video-game/edit/{id}', name: 'game_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        path: '/api/video-game/edit/{id}',
        summary: 'Edit a video game',
        security: [['bearerAuth' => []]],
        tags: ['Video Games'],
        requestBody: new OA\RequestBody(
            description: 'Updated video game data',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: VideoGame::class, groups: ['game:write']))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Video game updated',
                content: new OA\JsonContent(ref: new Model(type: VideoGame::class, groups: ['game:read']))
            )
        ]
    )]
    public function edit(Request $request, UrlGeneratorInterface $urlGenerator, VideoGame $game): JsonResponse
    {
        $editedGame = $this->serializer->deserialize(
            $request->getContent(),
            VideoGame::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $game]
        );
        if ($editedGame->getReleaseDate() !== null) {
            $releaseDate = $editedGame->getReleaseDate();
            if (is_string($releaseDate)) {
                $editedGame->setReleaseDate(new \DateTime($releaseDate));
            }
        }
        $this->entityManager->persist($editedGame);
        $this->entityManager->flush();
        return $this->json(['status' => 'success'], Response::HTTP_OK);
    }

    #[Route('/api/video-game/delete/{id}', name: 'game_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        path: '/api/video-game/delete/{id}',
        summary: 'Delete a video game',
        security: [['bearerAuth' => []]],
        tags: ['Video Games'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Video game deleted',
                content: new OA\JsonContent(type: 'string', example: 'Success')
            )
        ]
    )]
    public function delete(VideoGame $game): JsonResponse
    {
        $this->entityManager->remove($game);
        $this->entityManager->flush();
        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
    }
}
