<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright 2010 SkeekS
 */

use yii\db\Migration;

/**
 * Количество в позиции корзины и заказа хранится с 4 знаками, как остатки складов
 * (shop_store_product) и позиции счетов (shop_bill_item).
 *
 * С decimal(18,2) дробная упаковка округлялась при сохранении: 2 упаковки по 3,592 м²
 * (7,184 м²) сохранялись как 7,18 м², и сумма корзины расходилась с карточкой товара.
 */
class m261124_120000__alter_table__shop_order_item_quantity extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%shop_order_item}}', 'quantity', $this->decimal(18, 4)->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $rounded = (int)(new \yii\db\Query())
            ->from('{{%shop_order_item}}')
            ->where(new \yii\db\Expression('quantity <> ROUND(quantity, 2)'))
            ->count('*', $this->db);
        if ($rounded) {
            throw new \yii\base\Exception('Откат невозможен: у '.$rounded.' позиций количество с тремя и более знаками после запятой, оно округлилось бы. Схема не изменена.');
        }

        $this->alterColumn('{{%shop_order_item}}', 'quantity', $this->decimal(18, 2)->notNull()->defaultValue(0));
    }
}
