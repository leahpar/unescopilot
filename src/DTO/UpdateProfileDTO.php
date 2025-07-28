<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateProfileDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    public ?string $pseudo = null;
    
    // L'email n'est volontairement pas inclus dans ce DTO
    // pour empêcher sa modification
}