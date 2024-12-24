<?php

namespace brikdigital\entrynavigation\helpers;

use Craft;
use craft\base\Element;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use craft\models\Site;
use craft\web\View;
use verbb\navigation\elements\Node;
use verbb\navigation\models\Nav;
use verbb\navigation\Navigation;

class ElementSidebarHelper
{
    private static array $breadcrumbs = [];

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
//        $navs = Navigation::$plugin->getNavs()->getAllNavs();
        $navs = Navigation::$plugin->getNavs()->getEditableNavsForSite($element->site);
        $optionsByNav = [];
        $crumbs = self::getElementBreadcrumbs($element->canonicalId, $element->site);

        foreach ($navs as $nav) {
            $nodes = Navigation::$plugin->getNodes()->getNodesForNav($nav->id,$element->siteId);
            $optionsByNav[$nav->id] = Navigation::$plugin->getNodes()->getParentOptions($nodes, $nav);
        }

        return Craft::$app->view->renderTemplate('entry-navigation/_element-sidebar', [
            'navs' => $navs,
            'crumbs' => $crumbs,
            'optionsByNav' => $optionsByNav,
            'fetchNavItemsUrl' => UrlHelper::actionUrl('entry-navigation/data/fetch-nav-items')
        ]);
    }

    public static function getElementBreadcrumbs(int $id, Site $site): array
    {
        // NOTE(lexisother): Apparently `Craft::$app->sites->currentSite` is unreliable as all hell

        /** @var Nav[] $navs */
        $navs = Navigation::$plugin->getNavs()->getEditableNavsForSite($site);
        /** @var Node[] $nodes */
        $nodes = [];

        // replace $navs with array_slice($navs, 1, 1) to get the second menu for debugging
        // otherwise any dd(...) invocation will only show data for the first iteration
        foreach ($navs as $nav) {
            $navNodes = Navigation::$plugin->getNodes()->getNodesForNav($nav->id, $site->id);
            $navNodes = array_filter($navNodes, function ($node) use ($id) {
                return $node->element != null && $node->element->canonicalId === $id;
            });

            $nodes[$nav->id] = $navNodes;
        }

        foreach ($nodes as $navId => $nodeList) {
            foreach ($nodeList as $i => $node) {
                $nav = Navigation::$plugin->getNavs()->getNavById($node->navId);
                self::pushNodesToCrumbs($node, $nav, $i);
            }
        }
        

        // :lostdiver: 😦
        return array_map(function ($occs) {
            return array_map(function ($crumbList) {
                return array_reverse($crumbList);
            }, $occs);
        }, self::$breadcrumbs);
    }

    /**
     * Recursively push nodes to our breadcrumb list, until we've reached the top.
     *
     * @param Node|null $node
     * @param Nav $nav
     * @param int $i
     * @return void
     */
    private static function pushNodesToCrumbs(?Node $node, Nav $nav, int $i): void
    {
        // If for some reason there's no node, let's bail.
        if (!$node) return;

        $parent = $node->getParent();
        $parentLabel = "";
        if ($parent) {
            for ($j = 1; $j < $parent->level; $j++) {
                $parentLabel .= '    ';
            }
            $parentLabel .= $parent->title;
        }

        // Add our current node to the breadcrumbs...
        self::$breadcrumbs[$nav->name][$i][] = [
            "id" => $node->id,
            "title" => $node->title,
            "parent" => $parentLabel
        ];
        // ...and if we've reached the root node, call it a day.
        if ($node->level === 1) return;

        // If we've got more nodes to go, however, let's do it all over.
        $nextNode = $node->getParent();
        self::pushNodesToCrumbs($nextNode, $nav, $i);
    }
}