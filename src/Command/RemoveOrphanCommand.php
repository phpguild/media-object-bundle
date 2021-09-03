<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Command;

use PhpGuild\MediaObjectBundle\Service\ResolveMediaObject;
use PhpGuild\MediaObjectBundle\Upload\FileUploader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveOrphanCommand.
 */
class RemoveOrphanCommand extends Command
{
    protected static $defaultName = 'phpguild:media:remove:orphan';

    /** @var FileUploader $fileUploader */
    private $fileUploader;

    /** @var ResolveMediaObject $resolveMediaObject */
    private $resolveMediaObject;

    /**
     * CleanCommand constructor.
     *
     * @param FileUploader           $fileUploader
     * @param ResolveMediaObject     $resolveMediaObject
     */
    public function __construct(
        FileUploader $fileUploader,
        ResolveMediaObject $resolveMediaObject
    ) {
        parent::__construct();

        $this->fileUploader = $fileUploader;
        $this->resolveMediaObject = $resolveMediaObject;
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
        $mediaCollection = $this->resolveMediaObject->getMediaCollection();
        $filesystemCollection = $this->fileUploader->getMediaCollection(3600 * 24);

        $files = array_diff($filesystemCollection, $mediaCollection);
        $total = \count($files);

        $count = 0;
        foreach ($files as $file) {
            $count += (int) unlink($file);
        }

        $output->writeln(sprintf('Delete %s file(s) of %s', $count, $total));

        return Command::SUCCESS;
    }
}
