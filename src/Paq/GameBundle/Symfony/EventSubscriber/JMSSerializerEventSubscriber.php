<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Symfony\EventSubscriber;


use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use Paq\GameBundle\Model\ImagePresentedInterface;
use Sonata\MediaBundle\Model\Media;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class JMSSerializerEventSubscriber implements EventSubscriberInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize'),
        );
    }

    public function onPreSerialize(\JMS\Serializer\EventDispatcher\PreSerializeEvent $event)
    {
        $object = $event->getObject();

        // in order to get Image Url we need to inject proper Media Provider
        if ($object instanceof ImagePresentedInterface && $object->hasImage()) {
            /* @var ImagePresentedInterface $object */
            $imageProvider = $this->container->get($object->getImage()->getProviderName());
            $object->setImageProvider($imageProvider);
        }
    }
}