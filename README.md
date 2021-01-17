# Symfony Media Object Bundle

## Installation

Install with composer

    composer req phpguild/media-object-bundle

## Configuration

Create file config/packages/phpguild_media_object.yaml

    phpguild_media_object:
        public_path: '%kernel.project_dir%/public'
        media_original_directory: media/original

## Usage

Custom usage
    
    use Doctrine\ORM\Mapping as ORM;
    use PhpGuild\MediaObjectBundle\Model\MediaObjectInterface;
    use PhpGuild\MediaObjectBundle\Annotation as MediaObject;

    class Photo implements MediaObjectInterface
    {
        /**
         * @ORM\Column(type="string")
         * @MediaObject\Uploadable(urlProperty="url")
         */
        protected $file;

        protected $url;

With predefined trait

    use PhpGuild\MediaObjectBundle\Model\File\FileInterface;
    use PhpGuild\MediaObjectBundle\Model\File\FileTrait;

    class Photo implements FileInterface
    {
        use FileTrait;
