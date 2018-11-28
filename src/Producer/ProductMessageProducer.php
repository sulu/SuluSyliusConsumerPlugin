<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\SyliusProducerPlugin\Producer;

use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\RemoveProductMessage;
use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\SynchronizeProductMessage;
use Sulu\SyliusProducerPlugin\Producer\Serializer\ProductSerializerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductMessageProducer implements ProductMessageProducerInterface
{
    /**
     * @var ProductSerializerInterface
     */
    private $productSerializer;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        ProductSerializerInterface $productSerializer,
        MessageBusInterface $messageBus
    ) {
        $this->productSerializer = $productSerializer;
        $this->messageBus = $messageBus;
    }

    public function synchronize(ProductInterface $product): void
    {
        $payload = $this->productSerializer->serialize($product);
        $this->messageBus->dispatch(new SynchronizeProductMessage($product->getCode(), $payload));
    }

    public function remove(ProductInterface $product): void
    {
        $message = new RemoveProductMessage($product->getCode());

        $this->messageBus->dispatch($message);
    }
}
