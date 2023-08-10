# SuluSyliusProducerPlugin

<a href="https://github.com/sulu/SuluSyliusProducerPlugin/actions" target="_blank">
    <img src="https://img.shields.io/github/actions/workflow/status/sulu/SuluSyliusProducerPlugin/test-application.yaml" alt="Test workflow status">
</a>

Producer for synchronization products with sulu.

## Installation

```bash
composer require sulu/sylius-producer-plugin
```

### Register the plugin

```bash
// config/bundles.php

    Sulu\SyliusProducerPlugin\SuluSyliusProducerPlugin::class => ['all' => true],
```

### Add configuration

```bash
// config/packages/sulu_sylius_producer.yaml

imports:
    - { resource: "@SuluSyliusProducerPlugin/Resources/config/app/config.yaml" }

framework:
    messenger:
        transports:
            sulu_sylius_transport: 'redis://localhost:6379/sulu_sylius_products'
```
