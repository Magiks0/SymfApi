<?php

namespace App\Entity;

use App\Repository\EditorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EditorRepository::class)]
class Editor
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(['editor:read', 'editor:write', 'game:read'])]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['editor:read', 'editor:write', 'game:read'])]
    #[Assert\NotBlank(message: 'Please enter a name')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['editor:read', 'editor:write', 'game:read'])]
    #[Assert\NotBlank(message: 'Please enter a country')]
    private ?string $country = null;

    /**
     * @var Collection<int, VideoGame>
     */
    #[ORM\OneToMany(targetEntity: VideoGame::class, mappedBy: 'editor', cascade: ['remove'])]
    private ?Collection $videogame;

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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

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
            $videogame->setEditor($this);
        }

        return $this;
    }

    public function removeVideogame(VideoGame $videogame): static
    {
        if ($this->videogame->removeElement($videogame)) {
            // set the owning side to null (unless already changed)
            if ($videogame->getEditor() === $this) {
                $videogame->setEditor(null);
            }
        }

        return $this;
    }
}
