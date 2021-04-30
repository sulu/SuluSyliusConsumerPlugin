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

use Sulu\Bundle\SyliusConsumerBundle\Message\RemoveProductVariantMessage;
use Sulu\Bundle\SyliusConsumerBundle\Message\SynchronizeProductVariantMessage;
use Sulu\SyliusProducerPlugin\Producer\Serializer\ProductVariantSerializerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductVariantMessageProducer implements ProductVariantMessageProducerInterface
{
    /**
     * @var ProductVariantSerializerInterface
     */
    private $serializer;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(ProductVariantSerializerInterface $serializer, MessageBusInterface $messageBus)
    {
        $this->serializer = $serializer;
        $this->messageBus = $messageBus;
    }

    public function synchronize(ProductVariantInterface $productVariant): void
    {
        $payload = $this->serializer->serialize($productVariant);

        $product = $productVariant->getProduct();
        if (!$product) {
            return;
        }

        $code = $product->getCode();
        $variantCode = $productVariant->getCode();
        if (!$code || !$variantCode) {
            throw new \RuntimeException();
        }

        $message = new SynchronizeProductVariantMessage($code, $variantCode, $payload);
        $this->messageBus->dispatch($message);
    }

    public function remove(ProductVariantInterface $productVariant): void
    {
        $variantCode = $productVariant->getCode();
        if (!$variantCode) {
            throw new \RuntimeException();
        }

        $message = new RemoveProductVariantMessage($variantCode);
        $this->messageBus->dispatch($message);
    }
}
