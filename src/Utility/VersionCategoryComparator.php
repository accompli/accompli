<?php

namespace Accompli\Utility;

use Accompli\Deployment\Release;
use InvalidArgumentException;

/**
 * VersionCategoryComparator.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class VersionCategoryComparator
{
    /**
     * The major version category.
     *
     * @var string
     */
    const MAJOR = 'major';

    /**
     * The minor version category.
     *
     * @var string
     */
    const MINOR = 'minor';

    /**
     * The patch version category.
     *
     * @var string
     */
    const PATCH = 'patch';

    /**
     * Match only when the difference between two versions is a major version.
     *
     * @var string
     */
    const MATCH_MAJOR_DIFFERENCE = 'major_difference';

    /**
     * Match when the difference between two versions is a major or minor version.
     *
     * @var string
     */
    const MATCH_MINOR_DIFFERENCE = 'minor_difference';

    /**
     * Match always.
     *
     * @var string
     */
    const MATCH_ALWAYS = 'always';

    /**
     * Returns the highest version category difference between two Release instances.
     *
     * @param Release      $release1
     * @param Release|null $release2
     *
     * @return string|null
     */
    public static function getDifference(Release $release1, Release $release2 = null)
    {
        if ($release2 === null) {
            return self::MAJOR;
        }

        $version1 = explode('.', $release1->getVersion());
        $version2 = explode('.', $release2->getVersion());
        if (isset($version1[0]) === false || isset($version2[0]) === false || $version1[0] != $version2[0]) {
            return self::MAJOR;
        } elseif (isset($version1[1]) === false || isset($version2[1]) === false || $version1[1] != $version2[1]) {
            return self::MINOR;
        } elseif (isset($version1[2]) === false || isset($version2[2]) === false || $version1[2] != $version2[2]) {
            return self::PATCH;
        }
    }

    /**
     * Returns true when the version category difference matches the supplied match strategy.
     *
     * @param string  $strategy
     * @param Release $release1
     * @param Release $release2
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public static function matchesStrategy($strategy, Release $release1, Release $release2 = null)
    {
        if (in_array($strategy, array(self::MATCH_MAJOR_DIFFERENCE, self::MATCH_MINOR_DIFFERENCE, self::MATCH_ALWAYS)) === false) {
            throw new InvalidArgumentException(sprintf('The match strategy "%s" is invalid.', $strategy));
        }

        if ($strategy === self::MATCH_ALWAYS) {
            return true;
        }

        $match = false;
        $versionCategoryDifference = self::getDifference($release1, $release2);
        if ($strategy === self::MATCH_MAJOR_DIFFERENCE && $versionCategoryDifference === self::MAJOR) {
            $match = true;
        } elseif ($strategy === self::MATCH_MINOR_DIFFERENCE && in_array($versionCategoryDifference, array(self::MAJOR, self::MINOR))) {
            $match = true;
        }

        return $match;
    }
}
