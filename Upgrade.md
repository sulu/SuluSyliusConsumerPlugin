# Upgrade

## 0.x

### Rework synchronization of taxon entities

The `SynchronizeTaxonMessage` as well as the `TaxonMessageProducer` has
been refactored to support the synchronization of multiple independent taxon
entities. Previously only the root node could be synchronized, which resulted
in always synchronizing the complete tree structure.

`Sulu\Bundle\SyliusConsumerBundle\Message\SynchronizeTaxonMessage` renamed to `Sulu\Bundle\SyliusConsumerBundle\Message\SynchronizeTaxonsMessage`

Futhermore the logic to automatically find the parent taxon of the tree in the `TaxonMessageProducer` has been removed.
The producer now accepts an array of `TaxonInterfaces`. The service using the `TaxonMessagePrducer` now has to decide, which taxons
are synchronized.

Before
```php
// producer automatically searches the rootTaxon and synchronizes the complete tree
$taxonMessageProducer->synchronize($taxon);
```

After
```php
// producer synchronizes only listed taxons, it is expected that required parent taxons
// either were already synchronized or are also listed in the array
$taxonMessageProducer->synchronize([$taxon1, $taxon2, $taxon3]);
```
