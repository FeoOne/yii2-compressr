<?php

namespace nabu\yii2\compressr\html;

use Minify_HTML;

/**
 * Class MrclayHtmlCompressor
 * @package nabu\yii2\compressr\html
 */
class MrclayHtmlCompressor
{
    /**
     * @param $content
     * @return string
     */
    public static function compress(string $content) : string
    {
        return Minify_HTML::minify($content);
    }
}
