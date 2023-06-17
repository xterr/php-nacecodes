<?php

namespace Xterr\NaceCodes\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xterr\NaceCodes\NaceCode;
use Xterr\NaceCodes\NaceCodesFactory;
use Xterr\NaceCodes\NaceVersion;

class NaceCodesTest extends TestCase
{
    public function testIterator(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        $naceCodes = $naceCodesFactory->getCodes();

        foreach ($naceCodes as $naceCode) {
            static::assertInstanceOf(
                NaceCode::class,
                $naceCode
            );
        }

        static::assertIsArray($naceCodes->toArray());
        static::assertGreaterThan(0, count($naceCodes));
    }

    public function testGetByCodeAndVersion(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        $naceCode = $naceCodesFactory->getCodes()->getByCodeAndVersion('6202', NaceVersion::VERSION_2);

        static::assertInstanceOf(NaceCode::class, $naceCode);

        static::assertEquals('J', $naceCode->getSection());
        static::assertEquals('62', $naceCode->getDivision());
        static::assertEquals('620', $naceCode->getGroup());
        static::assertEquals('6202', $naceCode->getCode());
        static::assertEquals('62.02', $naceCode->getRawCode());
        static::assertEquals(NaceVersion::VERSION_2, $naceCode->getVersion());
        static::assertEquals('Computer consultancy activities', $naceCode->getName());
    }

    public function testGetByNormalizedCodeAndVersion(): void
    {
        $isoCodes = new NaceCodesFactory();
        $naceCode = $isoCodes->getCodes()->getByRawCodeAndVersion('62.02', NaceVersion::VERSION_2);

        static::assertInstanceOf(NaceCode::class, $naceCode);

        static::assertEquals('J', $naceCode->getSection());
        static::assertEquals('62', $naceCode->getDivision());
        static::assertEquals('620', $naceCode->getGroup());
        static::assertEquals('6202', $naceCode->getCode());
        static::assertEquals('62.02', $naceCode->getRawCode());
        static::assertEquals(NaceVersion::VERSION_2, $naceCode->getVersion());
        static::assertEquals('Computer consultancy activities', $naceCode->getName());
    }

    public function testGetAllByVersion(): void
    {
        $naceCodesFactory = new NaceCodesFactory();

        static::assertCount(
            615,
            $naceCodesFactory->getCodes()->getAllByVersion(NaceVersion::VERSION_2)
        );

        static::assertCount(
            0,
            $naceCodesFactory->getCodes()->getAllByVersion(1)
        );
    }

    public function testCount(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        static::assertEquals(615, $naceCodesFactory->getCodes()->count());
    }
}
