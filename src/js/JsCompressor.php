<?php

namespace nabu\yii2\compressr\js;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class JsCompressor
 * @package nabu\yii2\compressr\js
 */
class JsCompressor extends BaseObject
{
    /**
     * @var string
     */
    public $dirName = 'js';
    /**
     * @var bool
     */
    public $cacheInlineParts;
    /**
     * @var bool
     */
    public $flaggedComments;

    /**
     * @param array $parts
     * @return array
     * @throws InvalidConfigException
     */
    public function compressParts(array $parts) : array
    {
        return (new JsPartsCompressor([
            'flaggedComments' => $this->flaggedComments,
            'cacheInlineParts' => $this->cacheInlineParts,
        ]))->compress($parts);
    }

    /**
     * @param array $files
     * @return array
     */
    public function compressFiles(array $files) : array
    {
        return (new JsFilesCompressor([
            'dirName' => $this->dirName,
            'flaggedComments' => $this->flaggedComments,
        ]))->compress($files);
    }
}
