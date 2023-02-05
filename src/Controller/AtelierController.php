<?php

namespace App\Controller;

use App\Entity\Atelier;
use App\Repository\AtelierRepository;
use App\Service\VersioningService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class AtelierController extends AbstractController
{
    /**
     *  @OA\Response(
     *      response=200,
     *      description="Retourne la liste des ateliers",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Atelier::class, groups={"getAteliers"}))
     *      )
     *  )
     * 
     *  @OA\Tag(name="Atelier")
     * 
     * @param AtelierRepository $atelierRepository
     * @param SerializerInterface $serializer
     * @param VersioningService $versioningService
     * @return JsonResponse
     */
    #[Route('/api/ateliers', name: 'ateliers', methods: ['GET'])]
    public function getAteliers(
        AtelierRepository $atelierRepository,
        SerializerInterface $serializer,
        VersioningService $versioningService
    ): JsonResponse {
        
        $atelier = $atelierRepository->findAll();
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setGroups(["getAteliers"]);
        $context->setVersion($version);
        $jsonAtelier = $serializer->serialize($atelier, "json", $context);
        
        return new JsonResponse($jsonAtelier, Response::HTTP_OK, [], true);
    }

    /**
     * @OA\Response(
     *      response=200,
     *      description="Retourne un atelier avec l'id",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Atelier::class, groups={"getAteliers"}))
     *      )
     *  )
     * 
     *  @OA\Tag(name="Atelier")
     * 
     * @param Atelier $atelier, 
     * @param SerializerInterface $serializer, 
     * @param VersioningService $versioningService
     * @return JsonResponse
     */
    #[Route('/api/atelier/{id}', name: 'atelier', methods: ['GET'])]
    public function getEvent(
        Atelier $atelier,
        SerializerInterface $serializer,
        VersioningService $versioningService
    ): JsonResponse {
        $version = $versioningService->getVersion(); 
        $context = SerializationContext::create()->setGroups(["getAteliers"]);
        $context->setVersion($version);
        $jsonAtelier = $serializer->serialize($atelier, "json", $context);
        return new JsonResponse($jsonAtelier, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * @OA\Response(
     *      response=200,
     *      description="supprime un ateliers avec l'id",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Atelier::class, groups={"getAteliers"}))
     *      )
     *  )
     * 
     *  @OA\Tag(name="Atelier")
     *  @param Atelier $atelier, 
     *  @param EntityManagerInterface $em
     *  @return JsonResponse
     */
    #[Route('/api/atelier/{id}', name: 'delete_atelier', methods: ['DELETE'])]
    public function deleteEvent(
        Atelier $atelier, 
        EntityManagerInterface $em
    ) : JsonResponse {

        $em->remove($atelier);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Response(
     *      response=200,
     *      description="créer un atelier",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Atelier::class, groups={"getAteliers"}))
     *      )
     *  )
     * 
     *  @OA\Tag(name="Atelier")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/atelier', name: 'create_atelier', methods: ['POST'])]
    public function createEvent(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator
    ) : JsonResponse {
        
        $atelier = $serializer->deserialize($request->getContent(), Atelier::class, 'json');
        
        $errors = $validator->validate($atelier);
        if ($errors->count() > 0)
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        

        $em->persist($atelier);
        $em->flush();

        $context = SerializationContext::create()->setGroups(["getAteliers"]);
        $jsonAtelier = $serializer->serialize($atelier, 'json', $context);

        $location = $urlGenerator->generate('atelier', ['id' => $atelier->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAtelier, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * @OA\Response(
     *      response=200,
     *      description="met a jour un atelier avec l'id",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Atelier::class, groups={"getAteliers"}))
     *      )
     *  )
     * 
     *  @OA\Tag(name="Atelier")
     * 
     * @param Request $request, 
     * @param SerializerInterface $serializer, 
     * @param EntityManagerInterface $em, 
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/atelier/{id}', name: 'update_atelier', methods: ['PUT'])]
    public function updateAtelier(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ) : JsonResponse {

        $updatedAtelier = $serializer->deserialize($request->getContent(), Atelier::class, 'json');

        $errors = $validator->validate($updatedAtelier);
        if ($errors->count() > 0)
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        

        $em->persist($updatedAtelier);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
