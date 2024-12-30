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
            if (!in_array($element::class, ElementSidebarHelper::ELIGIBLE_ELEMENT_TYPES)) return;
            if ($element->getIsDraft()) return;

            // Ready up our HTML as a DOMElement
            $ourHTML = ElementSidebarHelper::getSidebarHtml($element);
            $ourNode = new \DOMDocument();
            $ourNode->loadHTML("<html>$ourHTML</html>");
            $ourNode = $ourNode->documentElement->firstChild;

            $revisionNotesField = null;
            $statusFieldDisabled = false;
            $revisionNotesDisabled = false;

            // Ready up Craft's HTML for querying
            $document = new \DOMDocument();
            $document->loadHTML($event->html);

            // Check if the site status field is there
            $slugField = $document->getElementById("slug-field");
            $slugFieldParent = $slugField->parentElement;
            $statusFieldset = $slugFieldParent->nextElementSibling->nextElementSibling;
            if ($statusFieldset->nodeName !== "fieldset")  $statusFieldDisabled = true;

            // If it is, also check if the revision notes are there
            if (!$statusFieldDisabled) {
                $revisionNotesField = $statusFieldset->nextElementSibling;
                if (!str_starts_with($revisionNotesField->id, "textarea")) $revisionNotesDisabled = true;
            }

            $modifiedWithDOM = false;
            // Revision notes are present, insert our HTML after it
            if (!$revisionNotesDisabled && isset($revisionNotesField)) {
                $revisionNotesField->insertAdjacentElement("afterend", $ourNode);
                $modifiedWithDOM = true;
            } else if (!$statusFieldDisabled && isset($statusFieldset)) {
                // Revision notes aren't present, but the status field is
                // so insert our HTML after that instead
                $statusFieldset->insertAdjacentElement("afterend", $ourNode);
                $modifiedWithDOM = true;
            } else {
                // All hope is lost, prepend our element to the start
                $event->html = ElementSidebarHelper::getSidebarHtml($element) . $event->html;
            }

            if ($modifiedWithDOM) {
                // export our modified HTML
                $html = $document->saveHTML($document->documentElement->firstChild);
                $event->html = $this->_stripBodyTag($html);
            }
        });
    }

    private function _stripBodyTag(string $html): string
    {
        $html = str_replace("<body>", "", $html);
        return str_replace("</body>", "", $html);
    }
}