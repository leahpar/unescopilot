<?php

namespace App\Tests\Entity;

use App\Entity\Visit;
use App\Entity\User;
use App\Entity\Site;
use App\Enum\VisitType;
use PHPUnit\Framework\TestCase;

class VisitTest extends TestCase
{
    public function testVisitCreation(): void
    {
        $visit = new Visit();
        
        $this->assertEquals(VisitType::WISHLIST, $visit->type);
        $this->assertNull($visit->visitedAt);
    }

    public function testVisitWithUserAndSite(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->pseudo = 'testuser';

        $site = new Site();
        $site->id = 1;
        $site->name = 'Test Site';

        $visit = new Visit();
        $visit->user = $user;
        $visit->site = $site;
        $visit->type = VisitType::VISITED;
        $visit->visitedAt = 2024;

        $this->assertSame($user, $visit->user);
        $this->assertSame($site, $visit->site);
        $this->assertEquals(VisitType::VISITED, $visit->type);
        $this->assertEquals(2024, $visit->visitedAt);
    }

    public function testVisitTypes(): void
    {
        $visit = new Visit();
        
        // Default is wishlist
        $this->assertEquals(VisitType::WISHLIST, $visit->type);
        
        // Can be changed to visited
        $visit->type = VisitType::VISITED;
        $this->assertEquals(VisitType::VISITED, $visit->type);
    }

    public function testVisitedAtAsYear(): void
    {
        $visit = new Visit();
        
        $this->assertNull($visit->visitedAt);
        
        $visit->visitedAt = 2023;
        $this->assertEquals(2023, $visit->visitedAt);
    }
}