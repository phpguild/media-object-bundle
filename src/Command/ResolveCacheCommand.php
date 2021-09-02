<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Command;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PhpGuild\MediaObjectBundle\Upload\FileUploader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ResolveCacheCommand.
 */
class ResolveCacheCommand extends Command
{
    protected static $defaultName = 'phpguild:media:resolve:cache';

    /** @var FileUploader $fileUploader */
    private $fileUploader;

    /** @var FilterManager $filterManager */
    private $filterManager;

    /** @var DataManager $dataManager */
    private $dataManager;

    /** @var CacheManager $cacheManager */
    private $cacheManager;

    /**
     * ResolveCacheCommand constructor.
     *
     * @param FileUploader       $fileUploader
     * @param FilterManager      $filterManager
     * @param DataManager        $dataManager
     * @param CacheManager       $cacheManager
     */
    public function __construct(
        FileUploader $fileUploader,
        FilterManager $filterManager,
        DataManager $dataManager,
        CacheManager $cacheManager,
    ) {
        parent::__construct();

        $this->fileUploader = $fileUploader;
        $this->filterManager = $filterManager;
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
    }

    /**
     * execute
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystemCollection = $this->fileUploader->getMediaCollection();
        $filters = array_keys($this->filterManager->getFilterConfiguration()->all());

        $count = 0;
        foreach ($filesystemCollection as $file) {
            $fileName = basename($file);
            $image = sprintf('%s/%s', $this->fileUploader->getChunkedFileName($fileName), $fileName);
            foreach ($filters as $filter) {
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
        }

        $output->writeln(
            sprintf('Resolve cache for %s filter(s) of %s image(s)', $count, \count($filesystemCollection))
        );

        return Command::SUCCESS;
    }
}
