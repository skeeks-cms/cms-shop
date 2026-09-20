# GPD offers synchronization

Текущая схема и правила: [GPD v2](gpd.md). Разделы с датами — история внедрения.

Enable `gpdReceiver.offersEnabled` explicitly. Schedule `shop.gpd.offers.sync` with an empty payload on the native `gpd-offers` queue; provision a cms-job worker for this lane. Receipt and application share a job, but use separate durable receipt and applied revisions in `shop_gpd_offer_connection` and `shop_gpd_offer_state`. Cursor ownership remains separate from catalog/references.

The writer and reference/catalog jobs share the `shop:gpd:apply` resource lock with distinct dedup keys. Network work is outside application transactions. Missing products or warehouse bindings remain pending, without acknowledging the revision. Prices/stock are independent of the product description synchronization flag.

A complete product bundle replaces only positions on this site's GPD-bound warehouses. Missing positions are deactivated with zero quantity; unrelated local warehouses are preserved. A revoked product bundle has the same bounded deactivation policy. Supplier-code changes reuse the existing product/store position; conflicting bindings fail rather than produce duplicates.

OfferPrices recalculates the affected product in the same transaction, preserving fixed prices. Eligible positions and maintenance SQL both require active positions and active warehouses. No eligible supplier position produces zero calculated prices. Automatic dependent price types are processed in dependency order; invalid cycles roll back application. The current protocol uses RUB.

Deploy additive migrations, configure the queue worker and enable the component flag. Pause legacy price/stock commands before the first writer run to avoid concurrent writers, verify all bundles and calculated values, and keep the old commands disabled after success. This is an opt-in pilot, not automatic fleet activation.

Tests: gpd-offer-receiver.php (18), gpd-offer-prices.php (8), gpd-catalog-transport.php (21 including offers HTTP formatting). gpd-offer-models.php needs an explicitly authorized Yii test bootstrap and rolls back its real-model fixtures. Never run integration scripts against an arbitrary site.

## Склады перед остатками (18.09.2026)

`shop.gpd.offers.sync` начинает каждый новый запуск с `GET /v2/stores`.
`StoreSync` проверяет весь ответ, затем обрабатывает до 20 складов за chunk:
создаёт отсутствующие и обновляет название, адрес, координаты и изображение.
Изображения скачиваются, как в v1. Соответствие — `cms_site_id + sx_id`.
Локальная активность, наценки, приоритет и расписание не перезаписываются;
отсутствующие в ответе склады не удаляются. Повторный ответ не вызывает запись.
После этого выполняются receive/resolve/apply; накопленные пакеты не требуют
сброса курсора. Неустранённые ожидания дают предупреждение в результате job.
Пилот elitkras: run 5085 создал Artkera (GPD 1618, local 11), применил 1251 пакет,
remaining=0, errors=0. Следующие установки требуют обновить worker gpd-offers.

### Перепривязка складских позиций на стороне GPD

Позиция определяется парой «привязанный склад GPD + код поставщика».
Если её перепривязали к другому товару на маркете, клиент сохраняет ID
складской строки и переносит её на новый товар своего сайта. Цены
пересчитываются у обоих товаров. Если у нового товара уже была строка
этого склада с другим кодом, она отвязывается и отключается без удаления.
Перенос между сайтами запрещён. Изменения и подтверждение версии пакета
выполняются в одной транзакции CatalogApplier; при ошибке откатываются вместе.