<?php

namespace nabu\yii2\compressr\compress;

use JShrink\Minifier;

use Yii;

use yii\base\BaseObject;

/**
 * Class JsPartsCompressor
 * @package nabu\yii2\compressr\compress
 */
class JsPartsCompressor extends BaseObject
{
    /**
     * @var bool
     */
    public $flaggedComments;
    /**
     * @var
     */
    public $enableCache;

    /**
     * @param array $parts
     * @return array
     */
    public function compress(array $parts) : array
    {
        return $this->enableCache
            ? $this->compressAndCache($parts)
            : $this->bakeAllParts($parts);
    }

    /**
     * @param array $parts
     * @return array
     */
    private function compressAndCache(array $parts) : array
    {
        $keys = array_keys($parts);
        $hash = md5(implode('', $keys));

        $cache = Yii::$app->getCache();
        $key = "compressr-js-{$hash}";

        $js = $cache->get($key);
        if ($js === false) {
            $parts = $this->bakeAllParts($parts);
            $cache->set($key, $parts);
        } else {
            $parts = $js;
        }

        return $parts;
    }

    /**
     * @param array $parts
     * @return array
     */
    private function bakeAllParts(array $parts) : array
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
