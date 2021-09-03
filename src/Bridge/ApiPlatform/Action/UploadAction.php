<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Bridge\ApiPlatform\Action;

use PhpGuild\ApiBundle\Http\RequestHandler;
use PhpGuild\MediaObjectBundle\Service\ResolveCache;
use PhpGuild\MediaObjectBundle\Upload\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    /** @var ResolveCache $resolveCache */
    private $resolveCache;

    /**
     * UploadAction constructor.
     *
     * @param RequestHandler $requestHandler
     * @param FileUploader   $fileUploader
     * @param ResolveCache   $resolveCache
     */
    public function __construct(
        RequestHandler $requestHandler,
        FileUploader $fileUploader,
        ResolveCache $resolveCache
    ) {
        $this->requestHandler = $requestHandler;
        $this->fileUploader = $fileUploader;
        $this->resolveCache = $resolveCache;
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

        $cacheFiles = $this->resolveCache->resolve(
            sprintf('%s/%s', $this->fileUploader->getChunkedFileName($fileName), $fileName),
            [ ResolveCache::POST_UPLOAD_FILTER ]
        );

        $cacheFile = $cacheFiles[ResolveCache::POST_UPLOAD_FILTER] ?? null;
        if ($cacheFile) {
            rename($cacheFile, $this->fileUploader->getAbsoluteFile($fileName));
        }

        return $this->requestHandler->getResponse([
            'filename' => $fileName,
        ], Response::HTTP_CREATED);
    }
}
