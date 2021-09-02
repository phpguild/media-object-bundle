<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Command;

use PhpGuild\MediaObjectBundle\Service\ResolveCache;
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

    /** @var ResolveCache $resolveCache */
    private $resolveCache;

    /**
     * ResolveCacheCommand constructor.
     *
     * @param FileUploader $fileUploader
     * @param ResolveCache $resolveCache
     */
    public function __construct(
        FileUploader $fileUploader,
        ResolveCache $resolveCache
    ) {
        parent::__construct();

        $this->fileUploader = $fileUploader;
        $this->resolveCache = $resolveCache;
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

        $count = 0;
        foreach ($filesystemCollection as $file) {
            $fileName = basename($file);
            $image = sprintf('%s/%s', $this->fileUploader->getChunkedFileName($fileName), $fileName);
            $count += $this->resolveCache->resolve($image);
        }

        $output->writeln(
            sprintf('Resolve cache for %s filter(s) of %s image(s)', $count, \count($filesystemCollection))
        );

        return Command::SUCCESS;
    }
}
