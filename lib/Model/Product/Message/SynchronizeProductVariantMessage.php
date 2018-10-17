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

namespace Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message;

class SynchronizeProductVariantMessage
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $payload;

    public function __construct(string $code, array $payload)
    {
        $this->code = $code;
        $this->payload = $payload;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
