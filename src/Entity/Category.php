<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read', 'category:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['category:read', 'category:write', 'game:read'])]
    #[Assert\NotBlank(message: 'Please enter a category name')]
    private ?string $name = null;

    /**
     * @var Collection<int, VideoGame>
     */
    #[ORM\ManyToMany(targetEntity: VideoGame::class, inversedBy: 'categories')]
    private Collection $videogame;

    public function __construct()
    {
        $this->videogame = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, VideoGame>
     */
    public function getVideogame(): Collection
    {
        return $this->videogame;
    }

    public function addVideogame(VideoGame $videogame): static
    {
        if (!$this->videogame->contains($videogame)) {
            $this->videogame->add($videogame);
        }

        return $this;
    }

    public function removeVideogame(VideoGame $videogame): static
    {
        $this->videogame->removeElement($videogame);

        return $this;
    }
}
