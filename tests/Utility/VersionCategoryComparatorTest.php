<?php

namespace Accompli\Test;

use Accompli\Deployment\Release;
use Accompli\Utility\VersionCategoryComparator;
use PHPUnit_Framework_TestCase;

/**
 * VersionCategoryComparatorTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class VersionCategoryComparatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if VersionCategoryComparator::getDifference returns the expected result when comparing two Release instances.
     *
     * @dataProvider provideTestGetDifference
     *
     * @param Release      $release1
     * @param Release|null $release2
     * @param string|null  $expectedResult
     */
    public function testGetDifference(Release $release1, Release $release2 = null, $expectedResult = null)
    {
        $this->assertSame($expectedResult, VersionCategoryComparator::getDifference($release1, $release2));
    }

    /**
     * Tests if VersionCategoryComparator::matchesStrategy returns the expected result for the match strategy when comparing two Release instances.
     *
     * @depends      testGetDifference
     * @dataProvider provideTestMatchesStrategy
     *
     * @param string       $strategy
     * @param Release      $release1
     * @param Release|null $release2
     * @param bool         $expectedResult
     */
    public function testMatchesStrategy($strategy, Release $release1, Release $release2 = null, $expectedResult = false)
    {
        $this->assertSame($expectedResult, VersionCategoryComparator::matchesStrategy($strategy, $release1, $release2));
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage The match strategy "invalid" is invalid.
     */
    public function testMatchesStrategyThrowsInvalidArgumentException()
    {
        VersionCategoryComparator::matchesStrategy('invalid', new Release('0.1.0'));
    }

    /**
     * Returns an array with test cases for @see testGetDifference.
     *
     * @return array
     */
    public function provideTestGetDifference()
    {
        return array(
            array(new Release('0.0.1'), null, 'major'),
            array(new Release('0.1.0'), null, 'major'),
            array(new Release('1.0.0'), null, 'major'),
            array(new Release('master'), new Release('0.0.1'), 'major'),
            array(new Release('0.0.1'), new Release('0.0.1'), null),
            array(new Release('0.0.2'), new Release('0.0.1'), 'patch'),
            array(new Release('0.1.0'), new Release('0.0.1'), 'minor'),
            array(new Release('1.0.0'), new Release('0.1.0'), 'major'),
            array(new Release('1.0.0'), new Release('0.0.1'), 'major'),
            array(new Release('1.1.0'), new Release('0.0.1'), 'major'),
            array(new Release('2.0.0'), new Release('2.0-dev'), 'minor'),
            array(new Release('2.0.0'), new Release('2.0.0-rc1'), 'patch'),
        );
    }

    /**
     * Returns an array with test cases for @see testMatchesStrategy.
     *
     * @return array
     */
    public function provideTestMatchesStrategy()
    {
        return array(
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('0.0.1'), null, true),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('0.1.0'), null, true),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('1.0.0'), null, true),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('master'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('0.0.1'), new Release('0.0.1'), false),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('0.0.2'), new Release('0.0.1'), false),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('0.1.0'), new Release('0.0.1'), false),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('1.0.0'), new Release('0.1.0'), true),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('1.0.0'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('1.1.0'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('2.0.0'), new Release('2.0-dev'), false),
            array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, new Release('2.0.0'), new Release('2.0.0-rc1'), false),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('0.0.1'), null, true),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('0.1.0'), null, true),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('1.0.0'), null, true),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('master'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('0.0.1'), new Release('0.0.1'), false),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('0.0.2'), new Release('0.0.1'), false),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('0.1.0'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('1.0.0'), new Release('0.1.0'), true),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('1.0.0'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('1.1.0'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('2.0.0'), new Release('2.0-dev'), true),
            array(VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, new Release('2.0.0'), new Release('2.0.0-rc1'), false),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('0.0.1'), null, true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('0.1.0'), null, true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('1.0.0'), null, true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('master'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('0.0.1'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('0.0.2'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('0.1.0'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('1.0.0'), new Release('0.1.0'), true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('1.0.0'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('1.1.0'), new Release('0.0.1'), true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('2.0.0'), new Release('2.0-dev'), true),
            array(VersionCategoryComparator::MATCH_ALWAYS, new Release('2.0.0'), new Release('2.0.0-rc1'), true),
        );
    }
}
