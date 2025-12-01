<?php

declare(strict_types=1);

namespace Src\Task\Domain\Entities;

use DateTimeImmutable;
use Src\Task\Domain\ValueObjects\TaskPriority;
use Src\Task\Domain\ValueObjects\TaskStatus;

class Task
{
    private ?int $id;
    private string $title;
    private ?string $description;
    private TaskStatus $status;
    private ?DateTimeImmutable $dueDate;
    private TaskPriority $priority;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        string $title,
        ?string $description,
        TaskStatus $status,
        ?DateTimeImmutable $dueDate,
        TaskPriority $priority,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->status = $status;
        $this->dueDate = $dueDate;
        $this->priority = $priority;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        string $title,
        ?string $description,
        string $status,
        ?DateTimeImmutable $dueDate,
        int $priority
    ): self {
        return new self(
            null,
            $title,
            $description,
            new TaskStatus($status),
            $dueDate,
            new TaskPriority($priority),
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );
    }

    public function update(
        string $title,
        ?string $description,
        string $status,
        ?DateTimeImmutable $dueDate,
        int $priority
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->status = new TaskStatus($status);
        $this->dueDate = $dueDate;
        $this->priority = new TaskPriority($priority);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeStatus(TaskStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changePriority(TaskPriority $priority): void
    {
        $this->priority = $priority;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function status(): TaskStatus
    {
        return $this->status;
    }

    public function dueDate(): ?DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function priority(): TaskPriority
    {
        return $this->priority;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value(),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'priority' => $this->priority->value(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}


