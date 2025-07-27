<?php

namespace App\Tests\Service;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Service\SiteService;
use PHPUnit\Framework\TestCase;

class SiteServiceTest extends TestCase
{
    private SiteRepository $siteRepository;
    private SiteService $siteService;

    protected function setUp(): void
    {
        $this->siteRepository = $this->createMock(SiteRepository::class);
        $this->siteService = new SiteService($this->siteRepository);
    }

    public function testGetAllSites(): void
    {
        $site1 = new Site();
        $site1->id = 1;
        $site1->name = 'Test Site 1';

        $site2 = new Site();
        $site2->id = 2;
        $site2->name = 'Test Site 2';

        $expectedSites = [$site1, $site2];

        $this->siteRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedSites);

        $result = $this->siteService->getAllSites();

        $this->assertSame($expectedSites, $result);
        $this->assertCount(2, $result);
    }

    public function testGetSiteById(): void
    {
        $siteId = 123;
        $site = new Site();
        $site->id = $siteId;
        $site->name = 'Test Site';

        $this->siteRepository
            ->expects($this->once())
            ->method('find')
            ->with($siteId)
            ->willReturn($site);

        $result = $this->siteService->getSiteById($siteId);

        $this->assertSame($site, $result);
        $this->assertEquals($siteId, $result->id);
    }

    public function testGetSiteByIdReturnsNullWhenNotFound(): void
    {
        $siteId = 999;

        $this->siteRepository
            ->expects($this->once())
            ->method('find')
            ->with($siteId)
            ->willReturn(null);

        $result = $this->siteService->getSiteById($siteId);

        $this->assertNull($result);
    }
}