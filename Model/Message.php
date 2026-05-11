<?php

class Message
{
    private int     $id_message;
    private int     $id_conversation;
    private int     $id_sender;
    private string  $type;
    private ?string $content;
    private ?string $file_path;
    private ?string $file_name;
    private ?int    $file_size;
    private int     $is_read;
    private int     $is_edited;
    private int     $is_deleted;
    private string  $created_at;
    private string  $updated_at;

    public function __construct(
        int     $id_message,
        int     $id_conversation,
        int     $id_sender,
        string  $type       = 'text',
        ?string $content    = null,
        ?string $file_path  = null,
        ?string $file_name  = null,
        ?int    $file_size  = null,
        int     $is_read    = 0,
        int     $is_edited  = 0,
        int     $is_deleted = 0,
        string  $created_at = '',
        string  $updated_at = ''
    ) {
        $this->id_message      = $id_message;
        $this->id_conversation = $id_conversation;
        $this->id_sender       = $id_sender;
        $this->type            = $type;
        $this->content         = $content;
        $this->file_path       = $file_path;
        $this->file_name       = $file_name;
        $this->file_size       = $file_size;
        $this->is_read         = $is_read;
        $this->is_edited       = $is_edited;
        $this->is_deleted      = $is_deleted;
        $this->created_at      = $created_at;
        $this->updated_at      = $updated_at;
    }

    public function getIdMessage(): int        { return $this->id_message; }
    public function getIdConversation(): int   { return $this->id_conversation; }
    public function getIdSender(): int         { return $this->id_sender; }
    public function getType(): string          { return $this->type; }
    public function getContent(): ?string      { return $this->content; }
    public function getFilePath(): ?string     { return $this->file_path; }
    public function getFileName(): ?string     { return $this->file_name; }
    public function getFileSize(): ?int        { return $this->file_size; }
    public function getIsRead(): int           { return $this->is_read; }
    public function getIsEdited(): int         { return $this->is_edited; }
    public function getIsDeleted(): int        { return $this->is_deleted; }
    public function getCreatedAt(): string     { return $this->created_at; }
    public function getUpdatedAt(): string     { return $this->updated_at; }
}
