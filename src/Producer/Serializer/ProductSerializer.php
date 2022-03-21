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
use Sulu\SyliusProducerPlugin\Model\CustomDataInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Attribute\Model\AttributeTranslationInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductSerializer implements ProductSerializerInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize(ProductInterface $product): array
    {
        $mainTaxon = $product->getMainTaxon();

        return [
            'id' => $product->getId(),
            'code' => $product->getCode(),
            'enabled' => $product->isEnabled(),
            'mainTaxonId' => $mainTaxon ? $mainTaxon->getId() : null,
            'mainTaxonCode' => $mainTaxon ? $mainTaxon->getCode() : null,
            'productTaxons' => $this->getProductTaxons($product),
            'translations' => $this->getTranslations($product),
            'attributeValues' => $this->getAttributeValues($product),
            'images' => $this->getImages($product),
            'customData' => $this->getCustomData($product),
            'variants' => $this->getVariants($product),
            'isSimple' => $product->isSimple(),
        ];
    }

    protected function getAttributeValues(ProductInterface $product): array
    {
        $attributeValues = [];
        foreach ($product->getAttributes() as $attributeValue) {
            /** @var AttributeInterface $attribute */
            $attribute = $attributeValue->getAttribute();
            $key = \sprintf('%1$s|%2$s', $attributeValue->getCode(), $attributeValue->getLocaleCode() ?? '_null_');
            $attributeValues[$key] = [
                'code' => $attributeValue->getCode(),
                'localeCode' => $attributeValue->getLocaleCode(),
                'value' => $attributeValue->getValue(),
                'attribute' => [
                    'id' => $attribute->getId(),
                    'code' => $attribute->getCode(),
                    'type' => $attribute->getType(),
                    'translatable' => $attribute->isTranslatable(),
                    'translations' => $this->getAttributeTranslations($attribute),
                    'configuration' => $attribute->getConfiguration(),
                    'customData' => $this->getCustomData($attribute),
                ],
                'customData' => $this->getCustomData($attributeValue),
            ];
        }

        return \array_values($attributeValues);
    }

    protected function getProductTaxons(ProductInterface $product): array
    {
        $productTaxons = [];
        foreach ($product->getProductTaxons() as $productTaxon) {
            $taxon = $productTaxon->getTaxon();
            if (!$taxon) {
                continue;
            }

            $productTaxons[] = [
                'id' => $productTaxon->getId(),
                'taxonId' => $taxon->getId(),
                'taxonCode' => $taxon->getCode(),
                'position' => $taxon->getPosition(),
                'customData' => $this->getCustomData($productTaxon),
            ];
        }

        return $productTaxons;
    }

    protected function getTranslations(ProductInterface $product): array
    {
        $translations = [];
        /** @var ProductTranslationInterface $translation */
        foreach ($product->getTranslations() as $translation) {
            $translations[] = [
                'locale' => $translation->getLocale(),
                'name' => $translation->getName(),
                'slug' => $translation->getSlug(),
                'description' => $translation->getDescription(),
                'shortDescription' => $translation->getShortDescription(),
                'metaKeywords' => $translation->getMetaKeywords(),
                'metaDescription' => $translation->getMetaDescription(),
                'customData' => $this->getCustomData($translation),
            ];
        }

        return $translations;
    }

    private function getAttributeTranslations(AttributeInterface $attribute): array
    {
        $translations = [];
        /** @var AttributeTranslationInterface $translation */
        foreach ($attribute->getTranslations() as $translation) {
            $translations[] = [
                'locale' => $translation->getLocale(),
                'name' => $translation->getName(),
                'customData' => $this->getCustomData($translation),
            ];
        }

        return $translations;
    }

    protected function getImages(ProductInterface $product): array
    {
        $images = [];
        foreach ($product->getImages() as $image) {
            $filename = null;
            $file = $image->getFile();
            if ($file && $file instanceof UploadedFile) {
                $filename = $file->getClientOriginalName();
            }

            $images[] = [
                'id' => $image->getId(),
                'type' => $image->getType(),
                'path' => $image->getPath(),
                'filename' => $filename,
                'customData' => $this->getCustomData($image),
            ];
        }

        return $images;
    }

    protected function getVariants(ProductInterface $product): array
    {
        if (!$product->hasVariants()) {
            return [];
        }

        $serializationContext = new SerializationContext();
        $serializationContext->setGroups(['Default', 'Detailed', 'CustomData']);

        /** @var array $content */
        $content = \json_decode(
            $this->serializer->serialize($product->getVariants()->getValues(), 'json', $serializationContext),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );

        return $content;
    }

    private function getCustomData(object $object): array
    {
        if (!$object instanceof CustomDataInterface) {
            return [];
        }

        return $object->getCustomData();
    }
}
