<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\SyliusProducerPlugin\EventSubscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use Sylius\Component\Addressing\Model\AddressInterface;

class AddressSerializeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'format' => 'json',
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        $object = $event->getObject();
        if (!$event->getContext()->hasAttribute('groups') || !$object instanceof AddressInterface) {
            return;
        }

        /** @var array $groups */
        $groups = $event->getContext()->getAttribute('groups');
        if (!\in_array('Default', $groups) && !\in_array('Detailed', $groups)) {
            return;
        }

        if (null !== $object->getCompany()) {
            /** @var JsonSerializationVisitor $visitor */
            $visitor = $event->getVisitor();

            $visitor->setData('company', $object->getCompany());
        }
    }
}
