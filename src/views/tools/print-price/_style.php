<?php
/**
 * @var $this yii\web\View
 * @var $isPrintSpec bool
 */
?>
<style type="text/css">
    * {
        font-family: "arial"
    }

    .label {
        width: 30mm;
        height: 20mm;
        position: relative;
        page-break-inside: avoid;
        overflow: hidden;


        float: left;

    }

    .border1 {
        border: 1px solid gray;
    }

    .block {
        /*position: absolute;*/
        overflow: hidden;
        border-color: grey;
        border-style: solid;
    }

    #settings {
        margin-right: 5px;
        margin-bottom: 5px;
        width: 500px;

    }

    .perpage {
        float: none;
    }


    @media print {
        /*.perpage {
            page-break-after: always;
            float: none !important;
        }*/

        #settings, hr {
            display: none;
        }

        body {
            margin: 0;
            padding: 0;
        }
    }

    <?php if($isPrintSpec) : ?>
    #settings, hr {
        display: none;
    }
    
    .perpage {
        page-break-after: always;
        float: none !important;
    }

    <?php endif; ?>
</style>