<?php

namespace nabu\yii2\compressr\base;

use Yii;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class BasePartsCompressor
 * @package nabu\yii2\compressr\base
 */
class BasePartsCompressor extends BaseObject
{
    /**
     * @param array $parts
     * @param string $key
     * @param callable $compressor
     * @return array|null
     * @throws InvalidConfigException
     */
    public function getFromCacheOrCompressAndCache(array $parts, string $key, callable $compressor) : array
    {
        $hashes = array_keys($parts);
        $hash = md5(implode('', $hashes));

        $cache = Yii::$app->getCache();
        if (is_null($cache)) {
            throw new InvalidConfigException('Caching must be configured in Yii2 application.');
        }

        $key = "$key-$hash";

        $js = $cache->get($key);
        if ($js === false) {
            $parts = call_user_func($compressor, $parts);
            $cache->set($key, $parts);
        } else {
            $parts = $js;
        }

        return $parts;
    }
}
