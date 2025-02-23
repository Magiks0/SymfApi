<?php

namespace App\Controller;

use App\Entity\Editor;
use App\Repository\EditorRepository;
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

class EditorController extends AbstractController
{
    public function __construct(
        private readonly EditorRepository $editorRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly TagAwareCacheInterface $cachePool
    ) {}

    #[Route('/api/editors', name: 'app_editor', methods: ['GET'])]
    #[OA\Get(
        path: '/api/editors',
        summary: 'Retrieve a list of editors',
        tags: ['Editors'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Editor::class, groups: ['editor:read']))
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $cacheIdentifier = sprintf("getAllEditors-%d-%d", $page, $limit);

        $editors = $this->cachePool->get($cacheIdentifier, function (ItemInterface $item) use ($page, $limit) {
            $item->tag("EditorCache");

            return $this->editorRepository->findAllWithPagination($page, $limit);
        });

        return $this->json($editors, Response::HTTP_OK, [], ['groups' => 'editor:read']);
    }

    #[Route('/api/editor/new', name: 'editor_new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/editor/new',
        summary: 'Create a new editor',
        requestBody: new OA\RequestBody(
            description: 'Editor data',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: Editor::class, groups: ['editor:write']))
        ),
        tags: ['Editors'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Editor created',
                content: new OA\JsonContent(ref: new Model(type: Editor::class, groups: ['editor:read']))
            )
        ]
    )]

    public function new(Request $request, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $editor = $this->serializer->deserialize($request->getContent(), Editor::class, 'json');
        $this->entityManager->persist($editor);
        $this->entityManager->flush();
        $location = $urlGenerator->generate('editor_new', ['id' => $editor->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        return $this->json($editor, Response::HTTP_CREATED, ['Location' => $location], ['groups' => 'editor:write']);
    }

    #[Route('/api/editor/edit/{id}', name: 'editor_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        path: '/api/editor/edit/{id}',
        summary: 'Edit an editor',
        tags: ['Editors'],
        requestBody: new OA\RequestBody(
            description: 'Updated editor data',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: Editor::class, groups: ['editor:write']))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Editor updated',
                content: new OA\JsonContent(ref: new Model(type: Editor::class, groups: ['editor:read']))
            )
        ]
    )]
    public function edit(Request $request, UrlGeneratorInterface $urlGenerator, Editor $editor): JsonResponse
    {
        $editedEditor = $this->serializer->deserialize(
            $request->getContent(),
            Editor::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $editor]
        );
        $this->entityManager->persist($editedEditor);
        $this->entityManager->flush();
        return $this->json(['status' => 'success'], Response::HTTP_OK);
    }

    #[Route('/api/editor/delete/{id}', name: 'editor_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        path: '/api/editor/delete/{id}',
        summary: 'Delete an editor',
        tags: ['Editors'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Editor deleted',
                content: new OA\JsonContent(type: 'string', example: 'Success')
            )
        ]
    )]
    public function delete(Editor $editor): JsonResponse
    {
        $this->entityManager->remove($editor);
        $this->entityManager->flush();
        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
    }
}
