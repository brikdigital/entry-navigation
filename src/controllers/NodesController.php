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
        $parent = $this->request->post('parent');
        if (!isset($nodeId) || !isset($title) || !isset($parent)) {
            $this->response->statusCode = 422; // unprocessable content
            return $this->asJson([
                "success" => false,
                "message" => "Missing values"
            ]);
        }


        $node = Navigation::$plugin->getNodes()->getNodeById($nodeId);

        if ($parent === $node->id) {
            $this->response->statusCode = 500;
            return $this->asJson([
                "success" => false,
                "message" => "Parent cannot be the node itself"
            ]);
        }

        $node->title = $title;
        $node->parentId = $parent;

        if (!Craft::$app->elements->saveElement($node)) {
            $this->response->statusCode = 500;
            return $this->asJson([
                "success" => false,
                "message" => "Couldn't save node"
            ]);
        } else {
            Craft::$app->session->setSuccess("Saved node");
            return $this->asJson([
                "success" => true
            ]);
        }
    }

    public function actionDeleteNode()
    {
        $nodeId = $this->request->post('nodeId');
        $siteId = $this->request->post('siteId');

        $node = Navigation::$plugin->getNodes()->getNodeById($nodeId, $siteId);
        if (!Craft::$app->elements->deleteElement($node)) {
            return $this->asJson([
                "success" => false,
                "message" => "Couldn't delete node"
            ]);
        } else {
            return $this->asJson([
                "success" => true
            ]);
        }
    }
}