<?php


namespace App\Modules\AuditLogging\Entity;

use App\Modules\AuditLogging\Repository\ActivityLogRepository;
use App\Entity\User;
use App\Entity\Traits\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ActivityLogRepository::class)]
#[ORM\Table(name: "activity_log")]   // ✅ Tell Doctrine to use the existing table


class ActivityLog
{
    use UserAwareTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $message = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}




