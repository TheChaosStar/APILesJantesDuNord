<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 *  @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "event",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getEvents")
 *  )
 * 
 *  @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "delete_event",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getEvents", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 *  )
 * 
 *  @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "update_event",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getEvents", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 *  )
 * 
 */
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEvents", "getAteliers"])]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(["getEvents", "getAteliers"])]
    #[Assert\NotBlank(message: 'Le titre de l\'évènement est obligatoire')]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: 'Le titre doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le titre ne peut pas faire plus de {{ limit }} caractères'
    )]
    private ?string $title = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(["getEvents", "getAteliers"])]
    private ?string $description = null;
    
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["getEvents", "getAteliers"])]
    #[Assert\NotBlank(message: 'La date de debut de l\'évènement est obligatoire')]
    private ?\DateTimeInterface $dateStart = null;
    
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["getEvents", "getAteliers"])]
    #[Assert\NotBlank(message: 'La date de fin de l\'évènement est obligatoire')]
    private ?\DateTimeInterface $dateEnd = null;
    
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(["getEvents", "getAteliers"])]
    #[Assert\NotBlank(message: 'L\'heurs de debut de l\'évènement est obligatoire')]
    private ?\DateTimeInterface $timeStart = null;
    
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(["getEvents", "getAteliers"])]
    #[Assert\NotBlank(message: 'L\'heurs de fin de l\'évènement est obligatoire')]
    private ?\DateTimeInterface $timeEnd = null;
    
    #[ORM\Column]
    #[Groups(["getEvents", "getAteliers"])]
    #[Assert\NotBlank(message: 'Le niveau de priorité de l\'évènement est obligatoire')]
    private ?int $priority = null;
    
    #[ORM\Column]
    #[Groups(["getEvents", "getAteliers"])]
    private ?bool $compulsory_presence = false;
    
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[Groups(["getEvents"])]
    private ?Atelier $atelier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getTimeStart(): ?\DateTimeInterface
    {
        return $this->timeStart;
    }

    public function setTimeStart(\DateTimeInterface $timeStart): self
    {
        $this->timeStart = $timeStart;

        return $this;
    }

    public function getTimeEnd(): ?\DateTimeInterface
    {
        return $this->timeEnd;
    }

    public function setTimeEnd(\DateTimeInterface $timeEnd): self
    {
        $this->timeEnd = $timeEnd;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function isCompulsoryPresence(): ?bool
    {
        return $this->compulsory_presence;
    }

    public function setCompulsoryPresence(bool $compulsory_presence): self
    {
        $this->compulsory_presence = $compulsory_presence;

        return $this;
    }

    public function getAtelier(): ?Atelier
    {
        return $this->atelier;
    }

    public function setAtelier(?Atelier $atelier): self
    {
        $this->atelier = $atelier;

        return $this;
    }
}
