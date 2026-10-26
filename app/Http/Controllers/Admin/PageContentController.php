<?php

namespace App\Http\Controllers\Admin;

/**
 * Text and sections of the built-in Home, About and Pricing pages, edited under Content → Pages.
 */
class PageContentController extends SettingController
{
    protected string $scope = 'pages';

    protected string $route = 'admin.page-content';
}
