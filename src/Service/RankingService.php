<?php

namespace App\Service;

use App\Repository\UserRepository;

class RankingService
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function getRanking(): array
    {
        return $this->userRepository->getUsersRanking();
    }
}
