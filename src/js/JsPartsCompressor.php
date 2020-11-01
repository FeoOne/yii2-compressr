<?php

namespace nabu\yii2\compressr\js;

use JShrink\Minifier;

use Yii;

use yii\base\InvalidConfigException;

use nabu\yii2\compressr\base\BasePartsCompressor;


/**
 * Class JsPartsCompressor
 * @package nabu\yii2\compressr\js
 */
class JsPartsCompressor extends BasePartsCompressor
{
    /**
     * @var bool
     */
    public $flaggedComments;
    /**
     * @var
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
            ? $this->getFromCacheOrCompressAndCache($parts, 'compressr-js', [$this, 'compressParts'])
            : $this->compressParts($parts);
    }

    /**
     * @param array $parts
     * @return array
     */
    private function compressParts(array $parts) : array
    {
        $result = [];

        foreach ($parts as $key => $value) {
            try {
                $result[$key] = Minifier::minify($value, ['flaggedComments' => $this->flaggedComments]);
            }
            catch (\Exception $e) {
                $result[$key] = $value;

                Yii::error("Can't minify javascript: {$e->getMessage()}", self::class);
                Yii::error($e->getMessage(), self::class);
            }
        }

        return $result;
    }
}
