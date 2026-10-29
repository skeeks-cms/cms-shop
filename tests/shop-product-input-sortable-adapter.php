<?php

function shopInputSortableExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$barcodes = file_get_contents(dirname(__DIR__).'/src/widgets/admin/views/product-barcodes.php');
$measures = file_get_contents(dirname(__DIR__).'/src/widgets/admin/views/product-measure-matches.php');
$composer = json_decode(file_get_contents(dirname(__DIR__).'/composer.json'), true);

// BackendSortableAdapterAsset first shipped in skeeks/cms-backend 2.1.1.11: every
// alternative of the constraint must be a caret range of major 2 starting at that
// version or later (a stricter minimum is fine), or the development branch.
$backendConstraint = $composer['require']['skeeks/cms-backend'] ?? null;
shopInputSortableExpect(is_string($backendConstraint), 'cms-shop does not declare its backend adapter dependency.');
foreach (preg_split('/\s*\|\|?\s*/', trim($backendConstraint)) as $alternative) {
    shopInputSortableExpect(
        $alternative === 'dev-master@dev'
        || (preg_match('/^\^(2(?:\.\d+){0,3})$/', $alternative, $m) && version_compare($m[1], '2.1.1.11', '>=')),
        'cms-shop backend dependency allows versions without the Sortable adapter: '.$backendConstraint
    );
}

foreach ([$barcodes, $measures] as $view) {
    shopInputSortableExpect(
        strpos($view, 'BackendSortableAdapterAsset::register($this);') !== false,
        'Shop product input does not register the backend Sortable adapter.'
    );
    shopInputSortableExpect(
        strpos($view, 'sx.backend.sortable.create(jElementsWrapper') !== false
        && strpos($view, 'onUpdate: function()') !== false
        && strpos($view, 'self.trigger("innerUpdate");') !== false,
        'Shop product input does not synchronize its value after sorting.'
    );
    shopInputSortableExpect(
        strpos($view, '.sortable(') === false
        && strpos($view, '\\yii\\jui\\Sortable::widget()') === false,
        'Shop product input still uses jQuery UI directly.'
    );
}

shopInputSortableExpect(
    strpos($barcodes, 'itemSelector: "> .sx-barcode-row"') !== false,
    'Barcode input does not restrict sorting to barcode rows.'
);
shopInputSortableExpect(
    strpos($measures, 'itemSelector: "> .sx-measure-row"') !== false,
    'Measure match input does not restrict sorting to measure rows.'
);

echo "CMS shop product input sortable adapter contract: OK\n";
