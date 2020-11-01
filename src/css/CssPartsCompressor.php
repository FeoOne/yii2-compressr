<?php

namespace nabu\yii2\compressr\css;

use Minify_CSSmin;

use yii\base\InvalidConfigException;

use nabu\yii2\compressr\base\BasePartsCompressor;

/**
 * Class CssPartsCompressor
 * @package nabu\yii2\compressr\css
 */
class CssPartsCompressor extends BasePartsCompressor
{
    /**
     * @var bool
     */
    public $cacheInlineParts;

    /**
     * @param array $parts
     * @return array
     * @throws InvalidConfigException
     */
    public function compress(array $parts) : array
    {
        return $this->cacheInlineParts
            ? $this->getFromCacheOrCompressAndCache($parts, 'compressr-css', [$this, 'compressParts'])
            : $this->compressParts($parts);
    }

    /**
     * @param array $parts
     * @return array
     */
    private function compressParts(array $parts) : array
    {
        $css = [];

        foreach ($parts as $hash => $value) {
            $css[] = preg_replace_callback('/<style\b.*?>(.*)<\/style>/si', function ($match) {
                return trim($match[1]);
            }, $value);
        }

        $css = implode("\n", $css);
        $css = Minify_CSSmin::minify($css, [
            'compress'         => true,
            'removeCharsets'   => true,
            'preserveComments' => false,
        ]);

        return [md5($css) => "<style>$css</style>"];
    }
}
