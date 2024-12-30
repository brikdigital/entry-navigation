<?php

namespace brikdigital\entrynavigation\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class EntryNavigationAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = "@brikdigital/entrynavigation/web";

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/index.css',
        ];

        $this->js = [
            'js/movePanel.js',
            'js/index.js',
        ];

        parent::init();
    }
}