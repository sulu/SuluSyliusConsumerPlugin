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

namespace spec\Sulu\SyliusProducerPlugin\Producer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sulu\Bundle\SyliusConsumerBundle\Message\RemoveProductVariantMessage;
use Sulu\Bundle\SyliusConsumerBundle\Message\SynchronizeProductVariantMessage;
use Sulu\SyliusProducerPlugin\Producer\ProductVariantMessageProducer;
use Sulu\SyliusProducerPlugin\Producer\Serializer\ProductVariantSerializerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductVariantMessageProducerSpec extends ObjectBehavior
{
    public function let(
        ProductVariantSerializerInterface $serializer,
        MessageBusInterface $messageBus
    ): void {
        $this->beConstructedWith($serializer, $messageBus);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductVariantMessageProducer::class);
    }

    public function it_should_dispatch_synchronize_message(
        ProductVariantSerializerInterface $serializer,
        MessageBusInterface $messageBus,
        ProductVariantInterface $productVariant,
        ProductInterface $product
    ): void {
        $productVariant->getCode()->willReturn('product-1-variant-0');
        $productVariant->getProduct()->willReturn($product);
        $product->getCode()->willReturn('product-1');
        $serializer->serialize($productVariant)
            ->shouldBeCalled()->willReturn(['code' => 'product-1']);

        $messageBus->dispatch(
            Argument::that(
                function (SynchronizeProductVariantMessage $message) {
                    return 'product-1-variant-0' === $message->getCode()
                        && 'product-1' === $message->getProductCode()
                        && ['code' => 'product-1'] === $message->getPayload();
                }
            )
        )->shouldBeCalled()->will(
            function ($arguments) {
                return new Envelope($arguments[0]);
            }
        );

        $this->synchronize($productVariant);
    }

    public function it_should_dispatch_remove_message(
        MessageBusInterface $messageBus,
        ProductVariantInterface $productVariant
    ): void {
        $productVariant->getCode()->willReturn('product-1-variant-0');

        $messageBus->dispatch(
            Argument::that(
                function (RemoveProductVariantMessage $message) {
                    return 'product-1-variant-0' === $message->getCode();
                }
            )
        )->shouldBeCalled()->will(
            function ($arguments) {
                return new Envelope($arguments[0]);
            }
        );

        $this->remove($productVariant);
    }
}
