<?php

namespace brikdigital\entrynavigation\helpers;

use Craft;
use craft\base\Element;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use craft\web\View;
use verbb\navigation\Navigation;

class ElementSidebarHelper
{
    public static function getSidebarHtml(Element $element): string
    {
        if ($element->uri === null) {
            return '';
        }

        // Obviously the cleanest way to share data from here to the asset bundle JS.
        Craft::$app->view->registerJs(<<< JS
            window.__ENTRY_ID__ = $element->id
            window.__ENTRY_URL__ = "$element->url"
        JS, View::POS_HEAD);

        $html = Html::beginTag('fieldset', ['class' => 'navigation-element-sidebar']) .
            Html::tag('legend', 'Entry Navigation', ['class' => 'h6']) .
            Html::tag('div', self::renderSidebarInnerHtml($element), ['class' => 'meta entry-nav-padded']) .
            Html::endTag('fieldset');

        return $html;
    }

    private static function renderSidebarInnerHtml(Element $element): string
    {
        $navs = Navigation::$plugin->getNavs()->getAllNavs();

        return Craft::$app->view->renderTemplate('entry-navigation/_element-sidebar', [
            'navs' => $navs,
            'fetchNavItemsUrl' => UrlHelper::actionUrl('entry-navigation/data/fetch-nav-items')
        ]);
    }
}