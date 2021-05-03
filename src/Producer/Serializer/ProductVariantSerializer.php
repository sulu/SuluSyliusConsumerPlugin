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

namespace Sulu\SyliusProducerPlugin\Producer\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

class ProductVariantSerializer implements ProductVariantSerializerInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize(ProductVariantInterface $productVariant): array
    {
        $serializationContext = new SerializationContext();
        $serializationContext->setGroups(['Default', 'Detailed', 'CustomData']);

        return json_decode(
            $this->serializer->serialize($productVariant, 'json', $serializationContext),
            true
        );
    }
}
