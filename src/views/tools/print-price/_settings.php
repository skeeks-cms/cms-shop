<?php
/**
* @var $this yii\web\View
* @var $isPrintSpec bool
*/

?>

<div class="label" style="display: none !important"></div>
<div id="settings">

    <form id='settingsForm'>
        <fieldset>
            <legend>Параметры печати</legend>
            <input type="checkbox" <?php echo $isPrintSpec ? "checked": ""; ?> id='perpage' name='perpage'> <label for="perpage">Каждый ценник на отдельную страницу</label><br>
            <input type="checkbox" <?php echo $isPrintSpec ? "" : "checked"; ?> id='border' name='border'> <label for="border">Рамка вокруг ценника</label><br><br>
            <fieldset>
                <legend>Расстояние между ценниками</legend>
                <label for="left">Слева, мм&nbsp;&nbsp;</label> <input type="number" id='left' name="left" value=0><br>
                <label for="top">Сверху, мм&nbsp;</label> <input type="number" id='top' name="top" value=0><br>
                <label for="right">Справа, мм</label> <input type="number" id='right' name="right" value=0><br>
                <label for="bottom">Снизу, мм&nbsp;&nbsp;&nbsp;</label> <input type="number" id='bottom' name="bottom" value=0><br>
            </fieldset>
            <br>
            <button><big><b>Печать</b></big></button>
        </fieldset>
    </form>
</div>
<hr>
