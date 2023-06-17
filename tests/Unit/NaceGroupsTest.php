<?php

namespace Xterr\NaceCodes\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xterr\NaceCodes\NaceCodesFactory;
use Xterr\NaceCodes\NaceGroup;
use Xterr\NaceCodes\NaceVersion;

class NaceGroupsTest extends TestCase
{
    public function testIterator(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        $naceGroups = $naceCodesFactory->getGroups();

        foreach ($naceGroups as $naceGroup) {
            static::assertInstanceOf(
                NaceGroup::class,
                $naceGroup
            );
        }

        static::assertIsArray($naceGroups->toArray());
        static::assertGreaterThan(0, count($naceGroups));
    }

    public function testGetByCodeAndVersion(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        $naceGroup = $naceCodesFactory->getGroups()->getByCodeAndVersion('011', NaceVersion::VERSION_2);

        static::assertInstanceOf(NaceGroup::class, $naceGroup);

        static::assertEquals('011', $naceGroup->getCode());
        static::assertEquals('01.1', $naceGroup->getRawCode());
        static::assertEquals(NaceVersion::VERSION_2, $naceGroup->getVersion());
        static::assertEquals(
            'Growing of non-perennial crops',
            $naceGroup->getName()
        );
    }

    public function testGetAllByVersion(): void
    {
        $naceCodesFactory = new NaceCodesFactory();

        static::assertCount(
            272,
            $naceCodesFactory->getGroups()->getAllByVersion(NaceVersion::VERSION_2)
        );

        static::assertCount(
            0,
            $naceCodesFactory->getGroups()->getAllByVersion(1)
        );
    }

    public function testCount(): void
    {
        $naceCodesFactory = new NaceCodesFactory();
        static::assertEquals(272, $naceCodesFactory->getGroups()->count());
    }
}
