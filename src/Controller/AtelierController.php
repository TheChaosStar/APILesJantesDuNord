<?php

namespace App\Controller;

use App\Entity\Atelier;
use App\Repository\AtelierRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AtelierController extends AbstractController
{
    #[Route('/api/ateliers', name: 'ateliers', methods: ['GET'])]
    public function getAteliers(
        AtelierRepository $atelierRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $atelier = $atelierRepository->findAll();
        $jsonAtelier = $serializer->serialize($atelier, "json", ["groups" => "getAteliers"]);

        return new JsonResponse($jsonAtelier, Response::HTTP_OK, [], true);
    }

    #[Route('/api/atelier/{id}', name: 'atelier', methods: ['GET'])]
    public function getEvent(
        Atelier $atelier,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsonAtelier = $serializer->serialize($atelier, "json", ["groups" => "getAteliers"]);
        return new JsonResponse($jsonAtelier, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/atelier/{id}', name: 'delete_atelier', methods: ['DELETE'])]
    public function deleteEvent(
        Atelier $atelier, 
        EntityManagerInterface $em
    ) : JsonResponse {

        $em->remove($atelier);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

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

        $jsonAtelier = $serializer->serialize($atelier, 'json', ['groups' => 'getAteliers']);

        $location = $urlGenerator->generate('atelier', ['id' => $atelier->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAtelier, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/atelier', name: 'update_atelier', methods: ['PUT'])]
    public function updateAtelier(
        Request $request,
        SerializerInterface $serializer,
        Atelier $currentAtelier,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ) : JsonResponse {

        $updatedAtelier = $serializer->deserialize($request->getContent(), Atelier::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAtelier]);

        $errors = $validator->validate($updatedAtelier);
        if ($errors->count() > 0)
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        

        $em->persist($updatedAtelier);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


}
