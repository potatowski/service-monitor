<?php

namespace App\Entity;

use App\Repository\RegistryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RegistryRepository::class)
 */
class Registry
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Message::class, inversedBy="registries")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity=Route::class, inversedBy="registries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $route;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $repeatedStatus;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $httpStatusCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $copyUrl;

    /**
     * @ORM\Column(type="integer")
     */
    private $timeExecution;

    public function __construct()
    {
        $this->message = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessage(): Collection
    {
        return $this->message;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->message->contains($message)) {
            $this->message[] = $message;
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        $this->message->removeElement($message);

        return $this;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function setRoute(?Route $route): self
    {
        $this->route = $route;

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

    public function getRepeatedStatus(): ?int
    {
        return $this->repeatedStatus;
    }

    public function setRepeatedStatus(int $repeatedStatus): self
    {
        $this->repeatedStatus = $repeatedStatus;

        return $this;
    }

    public function getHttpStatusCode(): ?string
    {
        return $this->httpStatusCode;
    }

    public function setHttpStatusCode(string $httpStatusCode): self
    {
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }

    public function getCopyUrl(): ?string
    {
        return $this->copyUrl;
    }

    public function setCopyUrl(string $copyUrl): self
    {
        $this->copyUrl = $copyUrl;

        return $this;
    }

    public function getTimeExecution(): ?int
    {
        return $this->timeExecution;
    }

    public function setTimeExecution(int $timeExecution): self
    {
        $this->timeExecution = $timeExecution;

        return $this;
    }
}
