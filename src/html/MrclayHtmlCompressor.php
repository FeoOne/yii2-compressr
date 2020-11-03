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
     * @param string|null $content
     * @return string
     */
    public static function compress(?string $content) : ?string
    {
        return is_null($content) ? $content : Minify_HTML::minify($content);
    }
}
