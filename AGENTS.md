# cms-shop

Для разработки приёма GPD v2 читать [docs/gpd.md](docs/gpd.md) и профильный документ по потоку. Серверные JSON, журналы публикации и jobs поставщика принадлежат его проекту. Сохранять legacy-команды и v1-совместимость при изменении новых обработчиков.

Для системных расписаний магазина и GPD читать [docs/system-shop-jobs.md](docs/system-shop-jobs.md).

## Кеш настроек магазина

CmsSite::getShopSite сохраняет контракт ActiveQuery и кеширует запрос конкретного
сайта на 8 часов. Зависимости: общий тег таблицы ShopSite для событий сохранения
и удаления и getCacheTag() сайта для кнопки очистки. Eager loading без конкретного
ID не кешируется; при отдельном queryCache, отличном от app cache, оптимизация
не включается. Прямой SQL обходит события моделей и требует явной инвалидации.
Тест tests/shop-site-cache.php принимает Composer autoload и путь к
cms/tests/site-settings-cache.php; использует изолированную SQLite.

## ID складов поставщиков в фильтре наличия

ShopComponent::getSupplierStoreIds выбирает только id для штатного фильтра
filterByQuantityQuery. Типы ID приводятся через ColumnSchema::phpTypecast,
как при гидратации ActiveRecord. Полные getStores/getSupplierStores не меняются.
Заданный через setSupplierStores набор (включая пустой), уже загруженные модели
и переопределённый проектом getSupplierStores имеют приоритет перед SQL column().
Снимок ID хранится только в объекте компонента, заменяется при смене сайта и
сбрасывается setter; статический и межзапросный кеш не используются.
Тест tests/supplier-store-ids.php с Composer autoload проверяет отсутствие
гидратации, типы, повторные/пустые чтения, смену сайта, setters, override и
результаты фильтра для собственных/поставщицких складов и товарных предложений.
