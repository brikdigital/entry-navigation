<?php

namespace brikdigital\entrynavigation\controllers;

use brikdigital\entrynavigation\helpers\ElementSidebarHelper;
use Craft;
use craft\web\Controller;
use verbb\navigation\Navigation;

class DataController extends Controller
{
    public function actionFetchNavItems()
    {
        $this->requirePostRequest();

        $navId = $this->request->post('navId');
        $nav = Navigation::$plugin->getNavs()->getNavById($navId);
        $nodes = Navigation::$plugin->getNodes()->getNodesForNav($navId);

        return $this->asJson([
            'nodes' => $nodes,
            'options' => Navigation::$plugin->getNodes()->getParentOptions($nodes, $nav),
        ]);
    }

    public function actionFetchElementBreadcrumbs()
    {
        $this->requirePostRequest();

        $elementId = $this->request->post('elementId');
        $breadcrumbs = ElementSidebarHelper::getElementBreadcrumbs($elementId);

        return $this->asJson([
            'breadcrumbs' => $breadcrumbs
        ]);
    }
}