<?php

namespace Xterr\NaceCodes\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xterr\NaceCodes\NaceCodesFactory;
use Xterr\NaceCodes\NaceSection;
use Xterr\NaceCodes\NaceVersion;

class NaceSectionsTest extends TestCase
{
    public function testIterator(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        $naceSections = $naceCodesFactory->getSections();

        foreach ($naceSections as $naceSection) {
            static::assertInstanceOf(
                NaceSection::class,
                $naceSection
            );
        }

        static::assertIsArray($naceSections->toArray());
        static::assertGreaterThan(0, count($naceSections));
    }

    public function testGetByCodeAndVersion(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        $naceSection = $naceCodesFactory->getSections()->getByCodeAndVersion('A', NaceVersion::VERSION_2);

        static::assertInstanceOf(NaceSection::class, $naceSection);

        static::assertEquals('A', $naceSection->getCode());
        static::assertEquals(NaceVersion::VERSION_2, $naceSection->getVersion());
        static::assertEquals('AGRICULTURE, FORESTRY AND FISHING', $naceSection->getName());
    }

    public function testGetAllByVersion(): void
    {
        $naceCodesFactory = new NaceCodesFactory();

        static::assertCount(
            21,
            $naceCodesFactory->getSections()->getAllByVersion(NaceVersion::VERSION_2)
        );

        static::assertCount(
            0,
            $naceCodesFactory->getSections()->getAllByVersion(1)
        );
    }

    public function testCount(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        static::assertEquals(21, $naceCodesFactory->getSections()->count());
    }
}
