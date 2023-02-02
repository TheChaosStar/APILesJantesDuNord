<?php

namespace App\Entity;

use App\Repository\AtelierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 *  @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "atelier",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAteliers")
 *  )
 * 
 *  @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "delete_atelier",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAteliers", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 *  )
 * 
 *  @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "update_atelier",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAteliers", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 *  )
 * 
 */
#[ORM\Entity(repositoryClass: AtelierRepository::class)]
class Atelier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAteliers", "getEvents"])]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(["getAteliers", "getEvents"])]
    #[Assert\NotBlank(message: 'L\'adresse de l\'atelier est obligatoire')]
    private ?string $location = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(["getAteliers", "getEvents"])]
    #[Assert\NotBlank(message: 'Les coordonnées de l\'atelier est obligatoire')]
    private ?string $coordinate = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(["getAteliers", "getEvents"])]
    #[Assert\NotBlank(message: 'Le nom de l\'atelier est obligatoire')]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'atelier', targetEntity: Event::class)]
    #[Groups(["getAteliers"])]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getCoordinate(): ?string
    {
        return $this->coordinate;
    }

    public function setCoordinate(string $coordinate): self
    {
        $this->coordinate = $coordinate;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setAtelier($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getAtelier() === $this) {
                $event->setAtelier(null);
            }
        }

        return $this;
    }
}
