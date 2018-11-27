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

namespace Sulu\SyliusProducerPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\SyliusProducerPlugin\Producer\ProductMessageProducerInterface;
use Sulu\SyliusProducerPlugin\Producer\ProductVariantMessageProducerInterface;
use Sulu\SyliusProducerPlugin\Producer\TaxonMessageProducerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeAllCommand extends Command
{
    const BULK_SIZE = 50;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TaxonMessageProducerInterface
     */
    private $taxonMessageProducer;

    /**
     * @var ProductMessageProducerInterface
     */
    private $productMessageProducer;

    /**
     * @var ProductVariantMessageProducerInterface
     */
    private $productVariantMessageProducer;

    /**
     * @var TaxonRepositoryInterface
     */
    private $taxonRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductVariantRepositoryInterface
     */
    private $productVariantRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TaxonMessageProducerInterface $taxonMessageProducer,
        ProductMessageProducerInterface $productMessageProducer,
        ProductVariantMessageProducerInterface $productVariantMessageProducer,
        TaxonRepositoryInterface $taxonRepository,
        ProductRepositoryInterface $productRepository,
        ProductVariantRepositoryInterface $productVariantRepository
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->taxonMessageProducer = $taxonMessageProducer;
        $this->productMessageProducer = $productMessageProducer;
        $this->productVariantMessageProducer = $productVariantMessageProducer;
        $this->taxonRepository = $taxonRepository;
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
    }

    protected function configure()
    {
        $this->setName('sulu-sylius:sync-all')
            ->setDescription('Sync all data to Sulu');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // disable logger because of memory issues
        $this->entityManager->getConfiguration()->setSQLLogger(null);
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        gc_enable();

        $this->syncTaxonTree($output);
        $this->syncProducts($output);
        $this->syncProductVariants($output);
    }

    private function syncTaxonTree(OutputInterface $output): void
    {
        $output->writeln('<info>Sync taxon tree</info>');

        foreach ($this->taxonRepository->findRootNodes() as $rootTaxon) {
            if (!$rootTaxon instanceof TaxonInterface) {
                continue;
            }

            $this->taxonMessageProducer->synchronize($rootTaxon);
        }
    }

    private function syncProducts(OutputInterface $output): void
    {
        $output->writeln('<info>Sync products</info>');

        $count = $this->entityManager->createQueryBuilder()
            ->select('count(product.id)')
            ->from($this->productRepository->getClassName(), 'product')
            ->getQuery()
            ->getSingleScalarResult();

        $query = $this->entityManager->createQueryBuilder()
            ->select('product')
            ->from($this->productRepository->getClassName(), 'product')
            ->getQuery();
        $iterableResult = $query->iterate();

        $progressBar = new ProgressBar($output, intval($count));
        $progressBar->start();

        $processedItems = 0;
        while (($row = $iterableResult->next()) !== false) {
            $product = $row[0];
            if (!$product instanceof ProductInterface) {
                continue;
            }

            $this->productMessageProducer->synchronize($product, false);

            $this->entityManager->detach($product);
            $processedItems++;
            if ($processedItems % self::BULK_SIZE === 0) {
                $this->entityManager->clear();
                gc_collect_cycles();
            }

            $progressBar->advance();
        }
    }

    private function syncProductVariants(OutputInterface $output): void
    {
        $output->writeln('<info>Sync product variants</info>');

        $count = $this->entityManager->createQueryBuilder()
            ->select('count(productVariant.id)')
            ->from($this->productVariantRepository->getClassName(), 'productVariant')
            ->getQuery()
            ->getSingleScalarResult();

        $query = $this->entityManager->createQueryBuilder()
            ->select('productVariant')
            ->from($this->productVariantRepository->getClassName(), 'productVariant')
            ->getQuery();
        $iterableResult = $query->iterate();

        $progressBar = new ProgressBar($output, intval($count));
        $progressBar->start();

        $processedItems = 0;
        while (($row = $iterableResult->next()) !== false) {
            $productVariant = $row[0];
            if (!$productVariant instanceof ProductVariantInterface) {
                continue;
            }

            $this->productVariantMessageProducer->synchronize($productVariant);

            $this->entityManager->detach($productVariant);
            $processedItems++;
            if ($processedItems % self::BULK_SIZE === 0) {
                $this->entityManager->clear();
                gc_collect_cycles();
            }

            $progressBar->advance();
        }
    }
}
