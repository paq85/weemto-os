<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Model;


use Sonata\MediaBundle\Provider\MediaProviderInterface;

/**
 * NOTE: create a property called "defaultImageFormat" if you want to override format being used by default instead of "fallbackImageFormat"
 */
trait ImagePresentedTrait
{
    /**
     * @var MediaProviderInterface
     */
    protected $imageProvider;

    /**
     * NOTE: format must reflect one of the formats specified in config.yml
     *
     * @var string
     */
    protected $fallbackImageFormat = 'default';

    /**
     * @param MediaProviderInterface $mediaProvider
     * @return $this
     */
    public function setImageProvider(MediaProviderInterface $imageProvider)
    {
        $this->imageProvider = $imageProvider;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImageUrl()
    {
        $image = $this->getImage();

        if (!$image || !$image->getEnabled()) {
            return null;
        }

        if ($this->imageProvider) {
            $imageFormat = isset($this->defaultImageFormat) ? $this->defaultImageFormat : $this->fallbackImageFormat;
            $url = $this->imageProvider->generatePublicUrl($image, $imageFormat); // eg. /uploads/media/tags/0001/01/thumb_1_tags_small.png

            return $url;
        } else {
            return $this->getImage()->getName();
        }
    }
}