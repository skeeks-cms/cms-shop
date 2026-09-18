<?php
namespace skeeks\cms\shop\gpd;

interface CatalogWriterInterface
{
    /** Prepare external resources before the receipt-state transaction. */
    public function prepare(array $item): void;
    /** Called inside the same transaction as applied_revision. */
    public function apply(array $item, array $state): array;
}
