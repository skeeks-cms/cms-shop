<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
$this->registerCSS(<<<CSS

h5 {
    text-transform: uppercase;
    font-size: 14px;
    font-weight: bold;
}

.sx-fast-edit-value {
    padding: 5px;
}

.sx-fast-edit-form-wrapper {
    display: none;
}

.sx-fast-edit {
    cursor: pointer;
    min-width: 40px;
    border-bottom: 1px dotted;
}
.js-slide img {
     max-height: 300px;
     margin: auto;
}
.sx-stick-navigation .js-slide {
    padding: 5px;
}
.sx-stick-navigation .slick-slide {
    opacity: .6;
}
.sx-stick-navigation .slick-slide:hover {
    opacity: 1;
}
.sx-stick-navigation .js-slide {
    cursor: pointer;
    border: none;
    margin: 0 0px;
    position: relative;
}

.sx-stick-navigation {
    margin-top: 10px;
    margin-bottom: 10px;
}

.sx-stick-navigation .slick-current:before {
    border: 1px solid #d2d2d2;
    content: '';
    position: absolute;
    z-index: 2;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    /* border: 1px solid rgba(21,146,165,0); */
    -moz-transition: all .3s ease;
    -o-transition: all .3s ease;
    -webkit-transition: all .3s ease;
    transition: all .3s ease;
}






.sx-table td:first-child, .sx-table th:first-child {
    text-align: left;
}
.sx-table td, .sx-table th {
    border: 0;
    text-align: center;
    padding: 7px 10px;
    font-size: 13px;
    border-bottom: 1px solid #dee2e68f;
    background: white;
}


.sx-table th {
    background: #f9f9f9;
}

.sx-table td {
    vertical-align: baseline;
}

.sx-table-wrapper {
    border-radius: 5px;
    border-left: 1px solid #dee2e68f;
    border-right: 1px solid #dee2e68f;
    border-top: 1px solid #dee2e68f;
}
.sx-table-wrapper table {
    margin-bottom: 0;
}


.sx-info-block {
    background: #f9f9f9;
    margin-top: 10px;
    padding: 10px;
}
.sx-title {
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.sx-quantity {
    max-width: 95px;
}

.popover {
    max-width: none;
}

CSS
);