<?php

namespace App\Entity;

use App\Repository\RouteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ORM\Entity(repositoryClass=RouteRepository::class)
 */
class Route
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"route"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"route"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"route"})
     */
    private $url;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"route"})
     */
    private $createAt;

    /**
     * @ORM\OneToMany(targetEntity=Registry::class, mappedBy="route", orphanRemoval=true)
     */
    private $registries;

    public function onPreUpdate()
    {
        $this->setCreateAt(new \DateTime());
    }

    public function __construct()
    {
        $this->registries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUrl(): ?string
    {
        $url = $this->url;
        if (str_contains($url, 'http://') || str_contains($url, 'https://')) {
            return $url;
        }
        return 'http://' . $url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * @return Collection<int, Registry>
     */
    public function getRegistries(): Collection
    {
        return $this->registries;
    }

    public function addRegistry(Registry $registry): self
    {
        if (!$this->registries->contains($registry)) {
            $this->registries[] = $registry;
            $registry->setRoute($this);
        }

        return $this;
    }

    public function removeRegistry(Registry $registry): self
    {
        if ($this->registries->removeElement($registry)) {
            // set the owning side to null (unless already changed)
            if ($registry->getRoute() === $this) {
                $registry->setRoute(null);
            }
        }

        return $this;
    }
}
