<?php

namespace Xterr\NaceCodes\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xterr\NaceCodes\NaceCodesFactory;
use Xterr\NaceCodes\NaceDivision;
use Xterr\NaceCodes\NaceVersion;

class NaceDivisionsTest extends TestCase
{
    public function testIterator(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        $naceDivisions = $naceCodesFactory->getDivisions();

        foreach ($naceDivisions as $naceDivision) {
            static::assertInstanceOf(
                NaceDivision::class,
                $naceDivision
            );
        }

        static::assertIsArray($naceDivisions->toArray());
        static::assertGreaterThan(0, count($naceDivisions));
    }

    public function testGetByCodeAndVersion(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        $naceDivision = $naceCodesFactory->getDivisions()->getByCodeAndVersion('01', NaceVersion::VERSION_2);

        static::assertInstanceOf(NaceDivision::class, $naceDivision);

        static::assertEquals('01', $naceDivision->getCode());
        static::assertEquals(NaceVersion::VERSION_2, $naceDivision->getVersion());
        static::assertEquals(
            'Crop and animal production, hunting and related service activities',
            $naceDivision->getName()
        );
    }

    public function testGetAllByVersion(): void
    {
        $naceCodesFactory = new NaceCodesFactory();

        static::assertCount(
            88,
            $naceCodesFactory->getDivisions()->getAllByVersion(NaceVersion::VERSION_2)
        );

        static::assertCount(
            0,
            $naceCodesFactory->getDivisions()->getAllByVersion(1)
        );
    }

    public function testCount(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        static::assertEquals(88, $naceCodesFactory->getDivisions()->count());
    }
}
