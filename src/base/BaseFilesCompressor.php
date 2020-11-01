<?php

namespace nabu\yii2\compressr\base;

use Yii;

use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;

/**
 * Class BaseFilesCompressor
 * @package nabu\yii2\compressr\compress
 */
class BaseFilesCompressor extends BaseObject
{
    /**
     * @param array $files
     * @param array $internalFiles
     * @param array $externalFiles
     */
    protected function processFiles(array $files, array &$internalFiles, array &$externalFiles) : void
    {
        $internalFiles = [];
        $externalFiles = [];

        $keys = array_keys($files);

        foreach ($keys as $path) {
            if (Url::isRelative($path)) {
                $internalFiles[] = $path;
            } else {
                $externalFiles[] = $path;
            }
        }

        $internalFiles = ArrayHelper::getColumn($internalFiles, function (string $path) {
            return explode('?', $path)[0];
        });
    }

    /**
     * @param array $internalFiles
     * @param string $extension
     * @param string $dirName
     * @param string $fileName
     * @param string $assetsDirPath
     * @param string $assetsFilePath
     */
    protected function makeAssetsPaths(array $internalFiles,
                                       string $extension,
                                       string $dirName,
                                       string &$fileName,
                                       string &$assetsDirPath,
                                       string &$assetsFilePath) : void
    {
        $hash = substr(md5(implode('', $internalFiles)), 0, 8);
        $fileName = "{$hash}.{$extension}";
        $assetsDirPath = Yii::$app->assetManager->basePath . DIRECTORY_SEPARATOR . $dirName;
        $assetsFilePath = $assetsDirPath . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $dirName
     * @param string $fileName
     * @param string $assetsFilePath
     * @param array $externalFiles
     * @param callable $htmlMaker
     * @return array
     */
    protected function makeResultFiles(string $dirName,
                                       string $fileName,
                                       string $assetsFilePath,
                                       array $externalFiles,
                                       callable $htmlMaker) : array
    {
        $files = [];

        $basePath = Yii::$app->assetManager->baseUrl
            . DIRECTORY_SEPARATOR
            . $dirName
            . DIRECTORY_SEPARATOR
            . $fileName;
        $basePath .= '?v=' . (Yii::$app->assetManager->appendTimestamp ? time() : filemtime($assetsFilePath));
        $files[$basePath] = call_user_func($htmlMaker, $basePath);

        foreach ($externalFiles as $path) {
            $files[$path] = call_user_func($htmlMaker, $path);
        }

        return $files;
    }

    protected function createAssetsDirectoryIfNeeded(string $assetsDirPath) : bool
    {
        if (!is_dir($assetsDirPath)) {
            try {
                if (!FileHelper::createDirectory($assetsDirPath, 0775)) {
                    Yii::error("Can't create path '{$assetsDirPath}'", self::class);
                    return false;
                }
            }
            catch (Exception $e) {
                Yii::error("Can't create path '{$assetsDirPath}'", self::class);
                Yii::error($e->getMessage(), self::class);
                return false;
            }
        }

        return true;
    }
}
