<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
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

class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly TagAwareCacheInterface $cachePool
    ) {}

    #[Route('/api/categories', name: 'category_index', methods: ['GET'])]
    #[OA\Get(
        summary: 'Retrieve a list of categories',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Category::class, groups: ['category:read']))
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $cacheIdentifier = sprintf("getAllCategories-%d-%d", $page, $limit);

        $categories = $this->cachePool->get($cacheIdentifier, function (ItemInterface $item) use ($page, $limit) {
            $item->tag("categoryCache");

            return $this->categoryRepository->findAllWithPagination($page, $limit);
        });

        return $this->json(json_decode($categories), Response::HTTP_OK);
    }

    #[Route('/api/category/new', name: 'category_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        summary: 'Create a new category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category:write']))
        ),
        responses: [
            new OA\Response(response: 201, description: 'Category created successfully')
        ]
    )]
    public function new(Request $request, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $location = $urlGenerator->generate('category_index', ['id' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);

        return $this->json($category, Response::HTTP_CREATED, ['Location' => $location], ['groups' => 'category:write']);
    }

    #[Route('/api/category/edit/{id}', name: 'category_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        summary: 'Edit an existing category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category:write']))
        ),
        responses: [
            new OA\Response(response: 200, description: 'Category updated successfully')
        ]
    )]
    public function edit(Request $request, Category $category): JsonResponse
    {
        $editedCategory = $this->serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $category]
        );

        $this->entityManager->persist($editedCategory);
        $this->entityManager->flush();

        return $this->json(['status' => 'success'], Response::HTTP_OK);
    }

    #[Route('/api/category/delete/{id}', name: 'category_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        summary: 'Delete a category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 200, description: 'Category deleted successfully')
        ]
    )]
    public function delete(Category $category): JsonResponse
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
    }
}
