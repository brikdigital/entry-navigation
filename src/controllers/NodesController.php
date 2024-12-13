<?php

namespace brikdigital\entrynavigation\controllers;

use Craft;
use craft\web\Controller;
use Exception;
use verbb\navigation\Navigation;

class NodesController extends Controller
{
    public function actionEditNode()
    {
        $this->requirePostRequest();

        $nodeId = $this->request->post('nodeId');
        $title = $this->request->post('title');
        if (!$nodeId || !$title) {
            $this->response->statusCode = 422; // unprocessable content
            return $this->asJson([
                "success" => false,
                "message" => "Missing values"
            ]);
        }

        $node = Navigation::$plugin->getNodes()->getNodeById($nodeId);
        $node->title = $title;

        if (!Craft::$app->elements->saveElement($node)) {
            $this->response->statusCode = 500;
            return $this->asJson([
                "success" => false,
                "message" => "Couldn't save node"
            ]);
        } else {
            return $this->asJson([
                "success" => true
            ]);
        }
    }
}