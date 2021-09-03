# Symfony Media Object Bundle

## Features
- Api Platform support
- Base64 file normalization
- URL file normalization

## Installation

Install with composer

    composer req phpguild/media-object-bundle

## Configuration

Create file `config/packages/phpguild_media_object.yaml`

    phpguild_media_object:
        default_filter: 'original'
        media_prefix: 'media'
        original_prefix: 'media/original'
        cache_prefix: 'media/cache'
        resolve_prefix: 'media/cache/resolve'
        resolve_filter_prefix: 'media/cache/resolve/{filter}'
        web_root: '%kernel.project_dir%/public'
        data_root: '%kernel.project_dir%/public/media/original'

## Command support

https://symfony.com/doc/current/routing.html#router-generate-urls-commands

Edit file `.env`

    ###> symfony/request ###
    REQUEST_CONTEXT_SCHEME=https
    REQUEST_CONTEXT_HOST=localhosturl
    REQUEST_CONTEXT_PORT=
    REQUEST_CONTEXT_PATH=
    ###< symfony/request ###

Edit file `config/packages/routing.yaml`

    parameters:
        router.request_context.scheme: '%env(REQUEST_CONTEXT_SCHEME)%'
        router.request_context.host: '%env(REQUEST_CONTEXT_HOST)%'
        router.request_context.port: '%env(REQUEST_CONTEXT_PORT)%'
        router.request_context.base_url: '%env(REQUEST_CONTEXT_PATH)%'
        asset.request_context.base_path: '%router.request_context.base_url%'
        asset.request_context.secure: true

Edit file `config/packages/liip_imagine.yaml`

    liip_imagine:
        driver: gd
        default_image: '/media/default.png'
        
        loaders:
            default:
                filesystem:
                    data_root: '%kernel.project_dir%/public/media/original'
    
        resolvers:
            default:
                web_path:
                    web_root: '%kernel.project_dir%/public'
                    cache_prefix: 'media/cache'
        
        filter_sets:

            _post_upload:
                quality: 95
                filters:
                    auto_rotate: ~
                    thumbnail: { size: [2048], mode: outbound }

## Usage

Custom usage
    
    use Doctrine\ORM\Mapping as ORM;
    use PhpGuild\MediaObjectBundle\Model\MediaObjectInterface;
    use PhpGuild\MediaObjectBundle\Annotation as MediaObject;

    class Photo implements MediaObjectInterface
    {
        /**
         * @ORM\Column(type="string")
         * @MediaObject\Uploadable(urlProperty="fileUrl")
         */
        protected $file;

        protected $fileUrl;

With predefined trait

    use PhpGuild\MediaObjectBundle\Model\File\FileInterface;
    use PhpGuild\MediaObjectBundle\Model\File\FileTrait;

    class Photo implements FileInterface
    {
        use FileTrait;

## API Platform Bridge

Edit file `config/services.yaml`

    imports:
        - { resource: '@PhpGuildMediaObjectBundle/Resources/config/bridge/api-platform.yaml' }

Edit file `config/routes.yaml`

    api_phpguild_media_object_upload:
        path: /api/upload
        controller: PhpGuild\MediaObjectBundle\Bridge\ApiPlatform\Action
        methods: POST
        defaults:
            _api_item_operation_name: post_upload
