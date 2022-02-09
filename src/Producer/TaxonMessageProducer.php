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

namespace Sulu\SyliusProducerPlugin\Producer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sulu\Bundle\SyliusConsumerBundle\Message\RemoveTaxonMessage;
use Sulu\Bundle\SyliusConsumerBundle\Message\SynchronizeTaxonsMessage;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TaxonMessageProducer implements TaxonMessageProducerInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(SerializerInterface $serializer, MessageBusInterface $messageBus)
    {
        $this->serializer = $serializer;
        $this->messageBus = $messageBus;
    }

    public function synchronize(array $taxons): void
    {
        $message = new SynchronizeTaxonsMessage(
            \array_map(function(TaxonInterface $taxon) {
                return $this->serialize($taxon);
            }, $taxons)
        );

        $this->messageBus->dispatch($message);
    }

    public function remove(int $id): void
    {
        $message = new RemoveTaxonMessage($id);

        $this->messageBus->dispatch($message);
    }

    protected function serialize(object $object): array
    {
        $serializationContext = new SerializationContext();
        $serializationContext->setGroups(['Default', 'Autocomplete', 'CustomData']);

        /** @var array $content */
        $content = \json_decode(
            $this->serializer->serialize($object, 'json', $serializationContext),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );

        return $content;
    }
}
