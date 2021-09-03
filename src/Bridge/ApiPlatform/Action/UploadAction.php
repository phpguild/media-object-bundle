<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Bridge\ApiPlatform\Action;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PhpGuild\ApiBundle\Http\RequestHandler;
use PhpGuild\MediaObjectBundle\Service\ResolveCache;
use PhpGuild\MediaObjectBundle\Upload\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Class UploadAction.
 */
class UploadAction extends AbstractController
{
    /** @var RequestHandler $requestHandler */
    private $requestHandler;

    /** @var FileUploader $fileUploader */
    private $fileUploader;

    /** @var CacheManager $cacheManager */
    private $cacheManager;

    /** @var ResolveCache $resolveCache */
    private $resolveCache;

    /** @var ?string $defaultFilter */
    private $defaultFilter;

    /**
     * UploadAction constructor.
     *
     * @param RequestHandler        $requestHandler
     * @param FileUploader          $fileUploader
     * @param CacheManager          $cacheManager
     * @param ResolveCache          $resolveCache
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        RequestHandler $requestHandler,
        FileUploader $fileUploader,
        CacheManager $cacheManager,
        ResolveCache $resolveCache,
        ParameterBagInterface $parameterBag
    ) {
        $this->requestHandler = $requestHandler;
        $this->fileUploader = $fileUploader;
        $this->cacheManager = $cacheManager;
        $this->resolveCache = $resolveCache;

        $configuration = $parameterBag->get('phpguild_media_object');
        $this->defaultFilter = $configuration['default_filter'] ?? null;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws ExceptionInterface|\JsonException
     */
    public function __invoke(Request $request): Response
    {
        $data = $request->getContent();
        $file = $this->fileUploader->prepare($data);
        $fileName = $this->fileUploader->copy($file);

        $fileSrc = $this->fileUploader->getAbsoluteFile($fileName);
        $fileChunked = sprintf('%s/%s', $this->fileUploader->getChunkedFileName($fileName), $fileName);

        $cacheFiles = $this->resolveCache->resolve($fileChunked, [ ResolveCache::POST_UPLOAD_FILTER ]);
        $cacheFile = $cacheFiles[ResolveCache::POST_UPLOAD_FILTER] ?? null;
        if ($cacheFile) {
            rename($cacheFile, $fileSrc);
        }

        return $this->requestHandler->getResponse([
            'filename' => $fileName,
            'url' => $this->cacheManager->getBrowserPath($fileChunked, $this->defaultFilter),
        ], Response::HTTP_CREATED);
    }
}
