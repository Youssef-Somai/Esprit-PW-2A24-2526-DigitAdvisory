<?php

class Conversation
{
    private int $id_conversation;
    private int $id_user1;
    private int $id_user2;
    private int $deleted_by1;
    private int $deleted_by2;
    private string $created_at;
    private string $updated_at;

    public function __construct(
        int    $id_conversation,
        int    $id_user1,
        int    $id_user2,
        int    $deleted_by1 = 0,
        int    $deleted_by2 = 0,
        string $created_at  = '',
        string $updated_at  = ''
    ) {
        $this->id_conversation = $id_conversation;
        $this->id_user1        = $id_user1;
        $this->id_user2        = $id_user2;
        $this->deleted_by1     = $deleted_by1;
        $this->deleted_by2     = $deleted_by2;
        $this->created_at      = $created_at;
        $this->updated_at      = $updated_at;
    }

    public function getIdConversation(): int    { return $this->id_conversation; }
    public function getIdUser1(): int           { return $this->id_user1; }
    public function getIdUser2(): int           { return $this->id_user2; }
    public function getDeletedBy1(): int        { return $this->deleted_by1; }
    public function getDeletedBy2(): int        { return $this->deleted_by2; }
    public function getCreatedAt(): string      { return $this->created_at; }
    public function getUpdatedAt(): string      { return $this->updated_at; }
}
