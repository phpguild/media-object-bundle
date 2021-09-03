<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Service;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class ResolveCache.
 */
final class ResolveCache
{
    public const POST_UPLOAD_FILTER = '_post_upload';

    /** @var FilterManager $filterManager */
    private $filterManager;

    /** @var DataManager $dataManager */
    private $dataManager;

    /** @var CacheManager $cacheManager */
    private $cacheManager;

    /** @var string|null $cachePrefix */
    private $cachePrefix;

    /** @var string|null $webRoot */
    private $webRoot;

    /**
     * ResolveCache constructor.
     *
     * @param FilterManager         $filterManager
     * @param DataManager           $dataManager
     * @param CacheManager          $cacheManager
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        FilterManager $filterManager,
        DataManager $dataManager,
        CacheManager $cacheManager,
        ParameterBagInterface $parameterBag
    ) {
        $this->filterManager = $filterManager;
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;

        $configuration = $parameterBag->get('phpguild_media_object');
        $this->cachePrefix = $configuration['cache_prefix'] ?? null;
        $this->webRoot = $configuration['web_root'] ?? null;
    }

    /**
     * resolve
     *
     * @param string $image
     * @param array  $filters
     *
     * @return array
     */
    public function resolve(string $image, array $filters = []): array
    {
        $items = [];

        $filterConfiguration = $this->filterManager->getFilterConfiguration();

        if (!\count($filters)) {
            $filters = array_keys($filterConfiguration->all());
        }

        foreach ($filters as $filter) {
            if (
                !$filterConfiguration->get($filter)
                || $this->cacheManager->isStored($image, $filter)
            ) {
                continue;
            }

            $this->cacheManager->store(
                $this->filterManager->applyFilter($this->dataManager->find($filter, $image), $filter),
                $image,
                $filter
            );

            $url = $this->cacheManager->resolve($image, $filter);
            $items[$filter] = sprintf(
                '%s/%s',
                $this->webRoot,
                substr($url, strrpos($url, $this->cachePrefix))
            );
        }

        return $items;
    }
}
