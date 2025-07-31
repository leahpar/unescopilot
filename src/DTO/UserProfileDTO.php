<?php

namespace App\DTO;

use App\Entity\User;

class UserProfileDTO
{
    public int $id;
    public string $pseudo;

    public static function fromUser(User $user): self
    {
        $dto = new self();
        $dto->id = $user->getId();
        $dto->pseudo = $user->getPseudo();

        return $dto;
    }
}
