<?php

namespace nabu\yii2\compressr\compress;

use yii\base\BaseObject;

/**
 * Class JsCompressor
 * @package nabu\yii2\compressr\resource
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
    public $enableCache;
    /**
     * @var bool
     */
    public $flaggedComments;

    /**
     * @param array $parts
     * @return array
     */
    public function compressParts(array $parts) : array
    {
        return (new JsPartsCompressor([
            'flaggedComments' => $this->flaggedComments,
            'enableCache' => $this->enableCache,
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
