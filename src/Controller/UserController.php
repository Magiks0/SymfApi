<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly TagAwareCacheInterface $cachePool
    ) {}

    #[Route('/api/users', name: 'user_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: 'Retrieve a list of users',
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: User::class, groups: ['user:read']))
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $cacheIdentifier = sprintf("getAllUsers-%d-%d", $page, $limit);

        $users = $this->cachePool->get($cacheIdentifier, function (ItemInterface $item) use ($page, $limit) {
            $item->tag("userCache");

            return $this->userRepository->findAllWithPagination($page, $limit);
        });

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }


    #[Route('/api/user/new', name: 'user_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: '/api/user/new',
        summary: 'Create a new user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            description: 'User data',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:write']))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created',
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:read']))
            )
        ]
    )]
    public function new(Request $request, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $location = $urlGenerator->generate('user_new', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        return $this->json($user, Response::HTTP_CREATED, ['Location' => $location], ['groups' => 'user:write']);
    }

    #[Route('/api/user/edit/{id}', name: 'user_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        path: '/api/user/edit/{id}',
        summary: 'Edit an existing user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            description: 'Updated user data',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:write']))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated',
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:read']))
            )
        ]
    )]
    public function edit(Request $request, UrlGeneratorInterface $urlGenerator, User $user): JsonResponse
    {
        $editedUser = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );
        $this->entityManager->persist($editedUser);
        $this->entityManager->flush();
        $location = $urlGenerator->generate(
            'user_edit',
            ['id' => $user->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return $this->json(['status' => 'success'], Response::HTTP_OK, ['Location' => $location]);
    }

    #[Route('/api/user/delete/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        path: '/api/user/delete/{id}',
        summary: 'Delete a user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User deleted',
                content: new OA\JsonContent(type: 'string', example: 'Success')
            )
        ]
    )]
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
    }
}
