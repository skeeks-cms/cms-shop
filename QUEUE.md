# Native scheduled shop maintenance

Six native handlers cover old unsubmitted orders/carts, price-change history,
product types, store-derived prices, automatic prices and generated ratings.
One installation normally has one site. Historical site parameters remain for
backward compatibility; no new multi-site workflow is introduced.

Existing route keys remain schedule identities; jobType routes them to native
handlers with empty payload. No console subprocess is used. Existing IDs,
activation, dates and intervals survive; manual execution uses the CMS admin
permission and does not change the next scheduled execution.

Run the maintenance worker after installing the source files, restart consumers,
and run cms/migrate. This change adds no schema migration. Install handler and
service classes before the registry configuration. Release after production
review; do not run a broad Composer update for a direct-file deployment.

All types use maintenance, skip and one attempt without automatic retry.
Catalog updates share resource key shop:catalog, but each type has its own
dedup key: distinct operations wait for the resource instead of suppressing one
another. CLI/admin invocations outside cms-job do not acquire its resource lock.

Services are shared by the CLI and handlers. Deletion is bounded by captured
maximum IDs and 1000-row batches; old unsubmitted orders retain the 5000-row
per-run cap and are not required to have empty item lists. Submitted orders
are rechecked at DELETE time. Price history defaults to 30 days, orders to 3.
Product/rating/price SQL is range-batched and respects tablePrefix. Counters
represent affected rows across phases, not distinct product counts.
Automatic-price formulas, fixed store prices and existing rating values retain
their existing semantics.

Store-price writes use an explicit Yii transaction, rolled back on cancellation
or failure, without changing the session autocommit setting. Cancellation and
heartbeat checkpoints run between batches. For native runs on MariaDB, each SQL
write is capped at 60 seconds with SET STATEMENT; the session value is unchanged.
MySQL has no equivalent general UPDATE timeout here: cancellation waits for the
current bounded statement to return. MariaDB 10.5 is the verified database.

Verification: tests/native-maintenance-smoke.php uses a disposable database on
the explicitly named cms-native-shop-db container; it never boots site config.
It checks both empty and sx_ table prefixes, preserved formulas and fixed
prices, rollback, native reporter counters, cancellation and resource keys.
The cms-agent package-command-bridge-smoke fixture verifies schedule identity.
