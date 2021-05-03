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

use Sylius\Component\Core\Model\ProductVariantInterface;

interface ProductVariantSerializerInterface
{
    public function serialize(ProductVariantInterface $productVariant): array;
}
