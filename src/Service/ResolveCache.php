<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Service;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

/**
 * Class ResolveCache.
 */
final class ResolveCache
{
    /** @var FilterManager $filterManager */
    private $filterManager;

    /** @var DataManager $dataManager */
    private $dataManager;

    /** @var CacheManager $cacheManager */
    private $cacheManager;

    /**
     * ResolveCache constructor.
     *
     * @param FilterManager $filterManager
     * @param DataManager   $dataManager
     * @param CacheManager  $cacheManager
     */
    public function __construct(
        FilterManager $filterManager,
        DataManager $dataManager,
        CacheManager $cacheManager
    ) {
        $this->filterManager = $filterManager;
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
    }

    /**
     * resolve
     *
     * @param string $image
     *
     * @return int
     */
    public function resolve(string $image): int
    {
        $count = 0;

        foreach (array_keys($this->filterManager->getFilterConfiguration()->all()) as $filter) {
            if ($this->cacheManager->isStored($image, $filter)) {
                continue;
            }

            $this->cacheManager->store(
                $this->filterManager->applyFilter($this->dataManager->find($filter, $image), $filter),
                $image,
                $filter
            );

            $this->cacheManager->resolve($image, $filter);

            $count++;
        }

        return $count;
    }
}
