<?php

namespace nabu\yii2\compressr\css;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class CssCompressor
 * @package nabu\yii2\compressr\css
 */
class CssCompressor extends BaseObject
{
    /**
     * @var string
     */
    public $dirName = 'css';

    /**
     * @var bool
     */
    public $cacheInlineParts;

    /**
     * @param array $parts
     * @return array
     * @throws InvalidConfigException
     */
    public function compressParts(array $parts) : array
    {
        return (new CssPartsCompressor([
            'cacheInlineParts' => $this->cacheInlineParts,
        ]))->compress($parts);
    }

    /**
     * @param array $files
     * @return array
     */
    public function compressFiles(array $files) : array
    {
        return (new CssFilesCompressor([
            'dirName' => $this->dirName,
        ]))->compress($files);
    }
}
