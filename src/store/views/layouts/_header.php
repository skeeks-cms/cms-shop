
<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/**
 * @var $theme \skeeks\cms\themes\unify\admin\UnifyThemeAdmin;
 */
$theme = $this->theme;
?>

<!-- Header -->
<header id="js-header" class="u-header u-header--sticky-top">
    <div class="<?= $theme->headerClasses; ?>">
        <nav class="navbar no-gutters g-pa-0">
            <div class="col-auto d-flex flex-nowrap u-header-logo-toggler g-py-12">
                <!-- Logo -->
                <a href="<?= $theme->logoHref; ?>" class="navbar-brand d-flex align-self-center g-hidden-xs-down py-0">
                    <? if ($theme->logoSrc) : ?>
                        <img class="default-logo" src="<?= $theme->logoSrc; ?>" alt="<?= $theme->logoTitle; ?>">
                    <? endif; ?>
                    <?= $theme->logoTitle; ?>
                </a>
                <!-- End Logo -->
                <!-- Sidebar Toggler -->
                <a class="js-side-nav u-header__nav-toggler d-flex align-self-center ml-auto" href="#!" data-hssm-class="u-side-nav--mini u-sidebar-navigation-v1--mini" data-hssm-body-class="u-side-nav-mini"
                   data-hssm-is-close-all-except-this="true" data-hssm-target="#sideNav">
                    <i class="hs-admin-align-left"></i>
                </a>
                <!-- End Sidebar Toggler -->
            </div>

            <div class="col-auto d-flex g-py-12 g-ml-20 sx-breadcrumbs-wrapper">
                <?= $this->render("@app/views/layouts/_breadcrumbs"); ?>
            </div>


            <!-- Messages/Notifications/Top Search Bar/Top User -->
            <div class="col-auto d-flex g-py-12 g-pl-40--lg ml-auto">
                <!-- Top Messages -->
                <div class="sx-now-hide g-pos-rel g-hidden-sm-down g-mr-5">
                    <a id="messagesInvoker" class="d-block text-uppercase u-header-icon-v1 g-pos-rel g-width-40 g-height-40 rounded-circle g-font-size-20" href="#!" aria-controls="messagesMenu" aria-haspopup="true"
                       aria-expanded="false" data-dropdown-event="click" data-dropdown-target="#messagesMenu"
                       data-dropdown-type="css-animation" data-dropdown-duration="300" data-dropdown-animation-in="fadeIn" data-dropdown-animation-out="fadeOut">
                        <span class="u-badge-v1 g-top-7 g-right-7 g-width-18 g-height-18 g-bg-primary g-font-size-10 g-color-white rounded-circle p-0">7</span>
                        <i class="hs-admin-comment-alt g-absolute-centered"></i>
                    </a>

                    <!-- Top Messages List -->
                    <div id="messagesMenu" class="g-absolute-centered--x g-width-340 g-max-width-400 g-mt-17 rounded" aria-labelledby="messagesInvoker">
                        <div class="media u-header-dropdown-bordered-v1 g-pa-20">
                            <h4 class="d-flex align-self-center text-uppercase g-font-size-default g-letter-spacing-0_5 g-mr-20 g-mb-0">3 new messages</h4>
                            <div class="media-body align-self-center text-right">
                                <a class="g-color-secondary" href="#!">View All</a>
                            </div>
                        </div>

                        <ul class="p-0 mb-0">
                            <!-- Top Messages List Item -->
                            <li class="media g-pos-rel u-header-dropdown-item-v1 g-pa-20">
                                <div class="d-flex g-mr-15">
                                    <!--<img class="g-width-40 g-height-40 rounded-circle" src="../../assets/img-temp/100x100/img5.jpg" alt="Image Description">-->
                                </div>

                                <div class="media-body">
                                    <h5 class="g-font-size-16 g-font-weight-400 g-mb-5"><a href="#!">Verna Swanson</a></h5>
                                    <p class="g-mb-10">Not so many years businesses used to grunt at using</p>

                                    <em class="d-flex align-self-center align-items-center g-font-style-normal g-color-lightblue-v2">
                                        <i class="hs-admin-time icon-clock g-mr-5"></i>
                                        <small>5 Min ago</small>
                                    </em>
                                </div>
                                <a class="u-link-v2" href="#!">Read</a>
                            </li>
                            <!-- End Top Messages List Item -->

                            <!-- Top Messages List Item -->
                            <li class="media g-pos-rel u-header-dropdown-item-v1 g-pa-20">
                                <div class="d-flex g-mr-15">
                                    <!--<img class="g-width-40 g-height-40 rounded-circle" src="../../assets/img-temp/100x100/img6.jpg" alt="Image Description">-->
                                </div>

                                <div class="media-body">
                                    <h5 class="g-font-size-16 g-font-weight-400 g-mb-5"><a href="#!">Eddie Hayes</a></h5>
                                    <p class="g-mb-10">But today and influence of is growing right along illustration</p>

                                    <em class="d-flex align-self-center align-items-center g-font-style-normal g-color-lightblue-v2">
                                        <i class="hs-admin-time icon-clock g-mr-5"></i>
                                        <small>22 Min ago</small>
                                    </em>
                                </div>
                                <a class="u-link-v2" href="#!">Read</a>
                            </li>
                            <!-- End Top Messages List Item -->

                            <!-- Top Messages List Item -->
                            <li class="media g-pos-rel u-header-dropdown-item-v1 g-pa-20">
                                <div class="d-flex g-mr-15">
                                    <!--<img class="g-width-40 g-height-40 rounded-circle" src="../../assets/img-temp/100x100/img7.jpg" alt="Image Description">-->
                                </div>

                                <div class="media-body">
                                    <h5 class="g-font-size-16 g-font-weight-400 g-mb-5"><a href="#!">Herbert Castro</a></h5>
                                    <p class="g-mb-10">But today, the use and influence of illustrations is growing right along</p>

                                    <em class="d-flex align-self-center align-items-center g-font-style-normal g-color-lightblue-v2">
                                        <i class="hs-admin-time icon-clock g-mr-5"></i>
                                        <small>15 Min ago</small>
                                    </em>
                                </div>
                                <a class="u-link-v2" href="#!">Read</a>
                            </li>
                            <!-- End Top Messages List Item -->
                        </ul>
                    </div>
                    <!-- End Top Messages List -->
                </div>
                <!-- End Top Messages -->

                <!-- Top Notifications -->
                <div class="sx-now-hide g-pos-rel g-hidden-sm-down">
                    <a id="notificationsInvoker" class="d-block text-uppercase u-header-icon-v1 g-pos-rel g-width-40 g-height-40 rounded-circle g-font-size-20" href="#!" aria-controls="notificationsMenu"
                       aria-haspopup="true" aria-expanded="false" data-dropdown-event="click"
                       data-dropdown-target="#notificationsMenu" data-dropdown-type="css-animation" data-dropdown-duration="300" data-dropdown-animation-in="fadeIn" data-dropdown-animation-out="fadeOut">
                        <i class="hs-admin-bell g-absolute-centered"></i>
                    </a>

                    <!-- Top Notifications List -->
                    <div id="notificationsMenu" class="js-custom-scroll g-absolute-centered--x g-width-340 g-max-width-400 g-height-400 g-mt-17 rounded" aria-labelledby="notificationsInvoker">
                        <div class="media text-uppercase u-header-dropdown-bordered-v1 g-pa-20">
                            <h4 class="d-flex align-self-center g-font-size-default g-letter-spacing-0_5 g-mr-20 g-mb-0">Notifications</h4>
                        </div>

                        <ul class="p-0 mb-0">
                            <!-- Top Notifications List Item -->
                            <li class="media u-header-dropdown-item-v1 g-parent g-px-20 g-py-15">
                                <div class="d-flex align-self-center u-header-dropdown-icon-v1 g-pos-rel g-width-55 g-height-55 g-font-size-22 rounded-circle g-mr-15">
                                    <i class="hs-admin-bookmark-alt g-absolute-centered"></i>
                                </div>

                                <div class="media-body align-self-center">
                                    <p class="mb-0">A Pocket PC is a handheld computer features</p>
                                </div>

                                <a class="d-flex g-color-lightblue-v2 g-font-size-12 opacity-0 g-opacity-1--parent-hover g-transition--ease-in g-transition-0_2" href="#!">
                                    <i class="hs-admin-close"></i>
                                </a>
                            </li>
                            <!-- End Top Notifications List Item -->

                            <!-- Top Notifications List Item -->
                            <li class="media u-header-dropdown-item-v1 g-parent g-px-20 g-py-15">
                                <div class="d-flex align-self-center u-header-dropdown-icon-v1 g-pos-rel g-width-55 g-height-55 g-font-size-22 rounded-circle g-mr-15">
                                    <i class="hs-admin-blackboard g-absolute-centered"></i>
                                </div>

                                <div class="media-body align-self-center">
                                    <p class="mb-0">The first is a non technical method which requires</p>
                                </div>

                                <a class="d-flex g-color-lightblue-v2 g-font-size-12 opacity-0 g-opacity-1--parent-hover g-transition--ease-in g-transition-0_2" href="#!">
                                    <i class="hs-admin-close"></i>
                                </a>
                            </li>
                            <!-- End Top Notifications List Item -->

                            <!-- Top Notifications List Item -->
                            <li class="media u-header-dropdown-item-v1 g-parent g-px-20 g-py-15">
                                <div class="d-flex align-self-center u-header-dropdown-icon-v1 g-pos-rel g-width-55 g-height-55 g-font-size-22 rounded-circle g-mr-15">
                                    <i class="hs-admin-calendar g-absolute-centered"></i>
                                </div>

                                <div class="media-body align-self-center">
                                    <p class="mb-0">Stu Unger is of the biggest superstarsis</p>
                                </div>

                                <a class="d-flex g-color-lightblue-v2 g-font-size-12 opacity-0 g-opacity-1--parent-hover g-transition--ease-in g-transition-0_2" href="#!">
                                    <i class="hs-admin-close"></i>
                                </a>
                            </li>
                            <!-- End Top Notifications List Item -->

                            <!-- Top Notifications List Item -->
                            <li class="media u-header-dropdown-item-v1 g-parent g-px-20 g-py-15">
                                <div class="d-flex align-self-center u-header-dropdown-icon-v1 g-pos-rel g-width-55 g-height-55 g-font-size-22 rounded-circle g-mr-15">
                                    <i class="hs-admin-pie-chart g-absolute-centered"></i>
                                </div>

                                <div class="media-body align-self-center">
                                    <p class="mb-0">Sony laptops are among the most well known laptops</p>
                                </div>

                                <a class="d-flex g-color-lightblue-v2 g-font-size-12 opacity-0 g-opacity-1--parent-hover g-transition--ease-in g-transition-0_2" href="#!">
                                    <i class="hs-admin-close"></i>
                                </a>
                            </li>
                            <!-- End Top Notifications List Item -->
                            <!-- Top Notifications List Item -->
                            <li class="media u-header-dropdown-item-v1 g-parent g-px-20 g-py-15">
                                <div class="d-flex align-self-center u-header-dropdown-icon-v1 g-pos-rel g-width-55 g-height-55 g-font-size-22 rounded-circle g-mr-15">
                                    <i class="hs-admin-bookmark-alt g-absolute-centered"></i>
                                </div>

                                <div class="media-body align-self-center">
                                    <p class="mb-0">A Pocket PC is a handheld computer features</p>
                                </div>

                                <a class="d-flex g-color-lightblue-v2 g-font-size-12 opacity-0 g-opacity-1--parent-hover g-transition--ease-in g-transition-0_2" href="#!">
                                    <i class="hs-admin-close"></i>
                                </a>
                            </li>
                            <!-- End Top Notifications List Item -->

                            <!-- Top Notifications List Item -->
                            <li class="media u-header-dropdown-item-v1 g-parent g-px-20 g-py-15">
                                <div class="d-flex align-self-center u-header-dropdown-icon-v1 g-pos-rel g-width-55 g-height-55 g-font-size-22 rounded-circle g-mr-15">
                                    <i class="hs-admin-blackboard g-absolute-centered"></i>
                                </div>

                                <div class="media-body align-self-center">
                                    <p class="mb-0">The first is a non technical method which requires</p>
                                </div>

                                <a class="d-flex g-color-lightblue-v2 g-font-size-12 opacity-0 g-opacity-1--parent-hover g-transition--ease-in g-transition-0_2" href="#!">
                                    <i class="hs-admin-close"></i>
                                </a>
                            </li>
                            <!-- End Top Notifications List Item -->
                        </ul>
                    </div>
                    <!-- End Top Notifications List -->
                </div>
                <!-- End Top Notifications -->

                <!-- Top Search Bar (Mobi) -->
                <a id="searchInvoker" class="sx-now-hide g-hidden-sm-up text-uppercase u-header-icon-v1 g-pos-rel g-width-40 g-height-40 rounded-circle g-font-size-20" href="#!" aria-controls="searchMenu"
                   aria-haspopup="true" aria-expanded="false" data-is-mobile-only="true" data-dropdown-event="click"
                   data-dropdown-target="#searchMenu" data-dropdown-type="css-animation" data-dropdown-duration="300" data-dropdown-animation-in="fadeIn" data-dropdown-animation-out="fadeOut">
                    <i class="hs-admin-search g-absolute-centered"></i>
                </a>
                <!-- End Top Search Bar (Mobi) -->

                <!-- Top User -->
                <div class="col-auto d-flex g-pt-5 g-pt-0--sm g-pl-10 g-pl-20--sm">
                    <div class="g-pos-rel g-px-10--lg sx-header-user-profile">
                        <a id="profileMenuInvoker" class="d-block" href="#!" aria-controls="profileMenu" aria-haspopup="true" aria-expanded="false" data-dropdown-event="click" data-dropdown-target="#profileMenu"
                           data-dropdown-type="css-animation" data-dropdown-duration="300"
                           data-dropdown-animation-in="fadeIn" data-dropdown-animation-out="fadeOut">
                <span class="g-pos-rel">
        <span class="u-badge-v2--xs u-badge--top-right g-hidden-sm-up g-bg-secondary g-mr-5"></span>
                <img class="g-width-30 g-width-40--md g-height-30 g-height-40--md rounded-circle g-mr-10--sm sx-avatar"
                     src="<?= \Yii::$app->user->identity->avatarSrc ? \Yii::$app->user->identity->avatarSrc : \skeeks\cms\helpers\Image::getCapSrc(); ?>" alt="Image description">
                </span>
                            <span class="g-pos-rel g-top-2">
        <span class="g-hidden-sm-down"><?= \Yii::$app->user->identity->shortDisplayName; ?></span>
                <i class="hs-admin-angle-down g-pos-rel g-top-2 g-ml-10"></i>
                </span>
                        </a>

                        <!-- Top User Menu -->
                        <ul id="profileMenu" class="g-pos-abs g-left-0 g-width-100x--lg g-nowrap g-font-size-14 g-py-20 g-mt-17 rounded" aria-labelledby="profileMenuInvoker">

                            <li class="g-mb-10">
                                <a class="media g-py-5 g-px-20" href="<?= \yii\helpers\Url::to(['/cms/upa-personal/update']); ?>">
                                                <span class="d-flex align-self-center g-mr-12">
                                      <i class="hs-admin-user"></i>
                                    </span>
                                    <span class="media-body align-self-center">Мой профиль</span>
                                </a>
                            </li>

                            <li class="g-mb-10">
                                <a class="media g-py-5 g-px-20" href="<?= \yii\helpers\Url::to(['/cms/upa-personal/change-password']); ?>">
                                        <span class="d-flex align-self-center g-mr-12">
                                      <i class="fas fa-key"></i>
                                    </span>
                                    <span class="media-body align-self-center">Смена пароля</span>
                                </a>
                            </li>

                            <li class="mb-0">
                                <a class="media g-py-5 g-px-20" href="<?= \skeeks\cms\helpers\UrlHelper::construct('cms/auth/logout')->setCurrentRef(); ?>" data-method="post">
                    <span class="d-flex align-self-center g-mr-12">
          <i class="hs-admin-shift-right"></i>
        </span>
                                    <span class="media-body align-self-center">Выход</span>
                                </a>
                            </li>
                        </ul>
                        <!-- End Top User Menu -->
                    </div>
                </div>
                <!-- End Top User -->
            </div>
            <!-- End Messages/Notifications/Top Search Bar/Top User -->
            <!-- Top Activity Toggler -->
            <a id="activityInvoker" class="sx-now-hide text-uppercase u-header-icon-v1 g-pos-rel g-width-40 g-height-40 rounded-circle g-font-size-20" href="#!" aria-controls="activityMenu" aria-haspopup="true"
               aria-expanded="false" data-dropdown-event="click" data-dropdown-target="#activityMenu"
               data-dropdown-type="css-animation" data-dropdown-animation-in="fadeInRight" data-dropdown-animation-out="fadeOutRight" data-dropdown-duration="300">
                <i class="hs-admin-align-right g-absolute-centered"></i>
            </a>
            <!-- End Top Activity Toggler -->
        </nav>

    </div>
</header>
<!-- End Header -->