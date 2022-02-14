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

namespace Sulu\Bundle\SyliusConsumerBundle\Message;

class SynchronizeTaxonsMessage
{
    /**
     * @var array
     */
    private $taxons;

    public function __construct(array $taxons)
    {
        $this->taxons = $taxons;
    }

    public function getTaxons(): array
    {
        return $this->taxons;
    }
}
