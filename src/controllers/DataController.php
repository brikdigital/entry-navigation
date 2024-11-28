<?php

namespace brikdigital\entrynavigation\controllers;

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
}