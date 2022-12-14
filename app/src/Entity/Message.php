<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
{
    CONST MESSAGE_SUCESS = 'sucess';
    CONST MESSAGE_LIMITED = 'limit';
    CONST MESSAGE_FAILED = 'failed';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $identifier;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $subject;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * @ORM\OneToMany(targetEntity=Registry::class, mappedBy="message", orphanRemoval=true)
     */
    private $registries;

    public function __construct()
    {
        $this->registries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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
            $registry->setMessage($this);
        }

        return $this;
    }

    public function removeRegistry(Registry $registry): self
    {
        if ($this->registries->removeElement($registry)) {
            // set the owning side to null (unless already changed)
            if ($registry->getMessage() === $this) {
                $registry->setMessage(null);
            }
        }

        return $this;
    }
}
