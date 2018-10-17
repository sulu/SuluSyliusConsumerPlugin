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
use Sylius\Component\Core\Model\ProductInterface;

class ProductMessageProducer extends BaseMessageProducer implements ProductMessageProducerInterface
{
    public function synchronize(ProductInterface $product): void
    {
        $message = new SynchronizeProductMessage($product->getCode(), $this->serialize($product));

        $this->getMessageBus()->dispatch($message);
    }

    public function remove(ProductInterface $product): void
    {
        $message = new RemoveProductMessage($product->getCode());

        $this->getMessageBus()->dispatch($message);
    }
}
