<?php

namespace brikdigital\entrynavigation;

use brikdigital\entrynavigation\helpers\ElementSidebarHelper;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\events\DefineHtmlEvent;
use yii\base\Event;

class EntryNavigation extends Plugin
{
    public static EntryNavigation $plugin;
    public string $schemaVersion = "5.0.0";

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        if (Craft::$app->request->isCpRequest) {
            $this->_registerSidebarPanels();
        }
    }

    private function _registerSidebarPanels(): void
    {
        Event::on(Element::class, Element::EVENT_DEFINE_SIDEBAR_HTML, function(DefineHtmlEvent $event) {
            /** @var Element $element */
            $element = $event->sender;
            $event->html .= ElementSidebarHelper::getSidebarHtml($element);
        });
    }
}