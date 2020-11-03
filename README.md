# Automatic Javascript, CSS and HTML combiner and minifier for Yii2

This extension provides ability to process and combine local assets, 
thus relieving the developer from having to strictly control what assets used in project and how they organized.

**This also makes the page load faster by reducing the page size and reducing the loading of additional files.**

#### Javascript

Extension combines all included .js files in a page, minifies it and include result to the page as a single .js file.
This generated file is cached.

All inline javascript is minified and stays in the same place on the page.
You can set option to cache this generated parts to prevent multiple generation of the same code parts. 

#### CSS

Like javascript processing, all .css files are minified and included in the page as a single .css file.
This generated file is cached.

All inline CSS is combined and minified.
Like javascript processing, you can set option to cache this generated code parts.

#### HTML

Extension also provides ability to minify whole HTML on page.
There are two generation strategies used in component:

* [mrclay](https://github.com/mrclay/minify)
* [tylerhall](https://github.com/tylerhall/html-compressor)

# Install

Add `"nabu/yii2-compressr": ">=0.0.4"` to `composer.json` or run
```
composer require --prefer-dist nabu/yii2-compressr ">=0.0.4"
```

# Usage

```
[
    'bootstrap' => ['compressr'],
    'components' => [
    ...
        'compressr' =>
        [
            'class' => 'nabu\yii2\compressr\Compressr',

            'enabled' => true,              // enables component. possible use: 'enabled' => YII_ENV_PROD

            'compressJs' => true,           // compress and minify whole javascript on page
            'jsCutComments' => true,        // cut comments in javascript code
            'jsCacheInlineParts' => true,   // cache inline code parts. need the cache to be configured and enabled

            'compressCss' => true,          // compress and minify whole css on page
            'cssCacheInlineParts' => true,  // cache inline code parts. need the cache to be configured and enabled

            'compressHtml' => true,         // compress html
            // possible options: HtmlCompressStrategy::MRCLAY or HtmlCompressStrategy::TYLER
            'htmlCompressStrategy' => \nabu\yii2\compressr\html\HtmlCompressStrategy::MRCLAY,
        ],
    ]
]
```

# Profile

First time heavy page called:

![Profile](https://feoone.github.io/compressr-profile-first.png "Profiling stats first call")

Subsequent times with caching:

![Profile](https://feoone.github.io/compressr-profile-cache.png "Profiling stats first call")
