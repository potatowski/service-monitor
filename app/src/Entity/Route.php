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
    CONST TYPE_TOKEN_BEARER = 'Bearer';
    CONST TYPE_TOKEN_BASIC = 'Basic';

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

    /**
     * @ORM\Column(type="boolean")
     */
    private $removed = 0;

    /**
     * @ORM\ManyToOne(targetEntity=RequestMethod::class, inversedBy="routes")
     */
    private $requestMethod;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasToken = false;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $typeToken;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $token;

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
        return 'https://' . $url;
    }

    public function setUrl(string $url): self
    {
        $url = str_contains($url, 'http') ? $url : 'http://' . $url;
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

    public function isRemoved(): ?bool
    {
        return $this->removed;
    }

    public function setRemoved(bool $removed): self
    {
        $this->removed = $removed;

        return $this;
    }

    public function getLastRegistry(): ?Registry
    {
        return $this->registries->last() ? $this->registries->last() : null;
    }

    public function getRequestMethod(): ?RequestMethod
    {
        return $this->requestMethod;
    }

    public function setRequestMethod(?RequestMethod $requestMethod): self
    {
        $this->requestMethod = $requestMethod;

        return $this;
    }

    /**
     * @Groups({"route"})
     */
    public function getMethod(): ?string
    {
        return $this->getRequestMethod() ? $this->getRequestMethod()->getMethod() : 'GET';
    }

    /**
     * @Groups({"route"})
     */
    public function getHasToken(): ?bool
    {
        return $this->hasToken;
    }

    public function setHasToken(bool $hasToken): self
    {
        $this->hasToken = $hasToken;

        return $this;
    }

    /**
     * @Groups({"route"})
     */
    public function getTypeToken(): ?string
    {
        return $this->typeToken;
    }

    public function setTypeToken(?string $typeToken): self
    {
        $this->typeToken = $typeToken;

        return $this;
    }

    /**
     * @Groups({"route"})
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getAuthorization(): ?string
    {
        if ($this->getHasToken()) {
            return $this->getTypeToken() . ' ' . $this->getToken();
        }
     
        return null;
    }
}
