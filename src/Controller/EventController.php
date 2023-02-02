<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\AtelierRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class EventController extends AbstractController
{
    #[Route('/api/events', name: 'events', methods: ['GET'])]
    public function getEvents(
        EventRepository $eventRepository,
        SerializerInterface $serializer,
        Request $request,
        TagAwareCacheInterface $cache
    ): JsonResponse {

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllEvents-" . $page . "-" . $limit;

        $jsonEventList = $cache->get($idCache, function (ItemInterface $item) use ($eventRepository, $page, $limit, $serializer) {
            $item->tag("eventCache");
            $eventList = $eventRepository->findAllWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(["getEvents"]);
            return $serializer->serialize($eventList, "json", $context);
        });

        return new JsonResponse($jsonEventList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/event/{id}', name: 'event', methods: ['GET'])]
    public function getEvent(
        Event $event,
        SerializerInterface $serializer
    ): JsonResponse {
        $context = SerializationContext::create()->setGroups(["getEvents"]);
        $jsonEvent = $serializer->serialize($event, "json", $context);
        return new JsonResponse($jsonEvent, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/event/{id}', name: 'delete_event', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour creer un evenement')]
    public function deleteEvent(
        Event $event,
        EntityManagerInterface $em,
        TagAwareCacheInterface $cachePool
    ): JsonResponse {
        $cachePool->invalidateTags(["eventCache"]);
        $em->remove($event);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/event', name: 'create_event', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour creer un evenement')]
    public function createEvent(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        AtelierRepository $atelierRepository,
        ValidatorInterface $validator
    ) : JsonResponse {
        
        $event = $serializer->deserialize($request->getContent(), Event::class, 'json');
        
        
        $errors = $validator->validate($event);
        if ($errors->count() > 0)
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
           
        $content = $request->toArray();
        $idAtelier = $content["idAtelier"] ?? -1;
        
        $event->setAtelier($atelierRepository->find($idAtelier));
    
        $em->persist($event);
        $em->flush();

        $context = SerializationContext::create()->setGroups(["getEvents"]);
        $jsonEvent = $serializer->serialize($event, 'json', $context);

        $location = $urlGenerator->generate('event', ['id' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonEvent, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    
    #[Route('/api/event', name: 'update_event', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour creer un evenement')]
    public function updateEvent(
        Request $request,
        SerializerInterface $serializer,
        Event $currentEvent,
        EntityManagerInterface $em,
        AtelierRepository $atelierRepository,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache
    ) : JsonResponse {

        $updatedEvent = $serializer->deserialize($request->getContent(), Event::class, 'json');
        
        $errors = $validator->validate($updatedEvent);
        if ($errors->count() > 0)
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        
        $content = $request->toArray();
        $idAtelier = $content['idAtelier'] ?? -1;

        $updatedEvent->setAtelier($atelierRepository->find($idAtelier));

        $em->persist($updatedEvent);
        $em->flush();

        $cache->invalidateTags(["getEvents"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

}
