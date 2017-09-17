<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Model;


use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Provider\MediaProviderInterface;

interface ImagePresentedInterface
{

    /**
     * @param Media $image
     * @return mixed
     */
    public function setImage(Media $image);

    /**
     * @return bool
     */
    public function hasImage();

    /**
     * @return Media
     */
    public function getImage();

    /**
     * @param MediaProviderInterface $mediaProviderInterface
     * @return $this
     */
    public function setImageProvider(MediaProviderInterface $mediaProviderInterface);
}