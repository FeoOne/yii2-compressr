<?php

namespace nabu\yii2\compressr;

use Yii;

use yii\web\View;
use yii\web\Response;
use yii\base\Event;
use yii\base\Component;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\helpers\VarDumper;

use nabu\yii2\compressr\compress\JsCompressor;
use nabu\yii2\compressr\compress\CssCompressor;
use nabu\yii2\compressr\html\HtmlFormatStrategy;
use nabu\yii2\compressr\html\TylerHtmlCompressor;
use nabu\yii2\compressr\html\MrclayHtmlCompressor;

/**
 * Class CompressrComponent
 * @package nabu\yii2\compressr
 */
class CompressrComponent extends Component implements BootstrapInterface
{
    /**
     * @var bool
     */
    public $enabled = true;
    /**
     * @var bool
     */
    public $flaggedComments = false;
    /**
     * @var bool
     */
    public $enableCache = true;
    /**
     * @var int
     */
    public $htmlFormatStrategy = HtmlFormatStrategy::MRCLAY;

    /**
     * @var JsCompressor
     */
    private $_jsCompressor;
    /**
     * @var CssCompressor
     */
    private $_cssCompressor;

    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        if (!($app instanceof \yii\web\Application) || !$this->enabled) {
            return;
        }

        $this->_jsCompressor = new JsCompressor([
            'enableCache' => $this->enableCache,
            'flaggedComments' => $this->flaggedComments,
        ]);
        $this->_cssCompressor = new CssCompressor([
            'enableCache' => $this->enableCache,
        ]);

        $app->view->on(View::EVENT_END_PAGE, function (Event $event) use ($app) {
            $this->onEndPage($app, $event);
        });

        $app->response->on(Response::EVENT_BEFORE_SEND, function (Event $event) use ($app) {
            $this->onBeforeSend($app, $event);
        });
    }

    /**
     * @param Application $app
     * @param Event $event
     */
    private function onEndPage(Application $app, Event $event) : void
    {
        /** @var View $view */
        $view = $event->sender;

        if ($view instanceof View
            && $app->getResponse()->format === Response::FORMAT_HTML
            && !$app->getRequest()->isAjax
            && !$app->getRequest()->isPjax) {
            $this->compressResources($view);
        }
    }

    /**
     * @param Application $app
     * @param Event $event
     */
    private function onBeforeSend(Application $app, Event $event) : void
    {
        /** @var Response $response */
        $response = $event->sender;

        if ($app->getResponse()->format === Response::FORMAT_HTML
            && !$app->getRequest()->isAjax
            && !$app->getRequest()->isPjax) {
            $response->data = $this->compressHtml($response->data);
        }
    }

    /**
     * @param View $view
     */
    private function compressResources(View $view) : void
    {
        // js in html
        if (!empty($view->js)) {
            Yii::beginProfile('Compress javascript parts.');
            foreach ($view->js as $pos => $parts) {
                if (empty($parts)) {
                    continue;
                }
                $view->js[$pos] = $this->_jsCompressor->compressParts($parts);
            }
            Yii::endProfile('Compress javascript parts.');
        }

        // js in files
        if (!empty($view->jsFiles)) {
            Yii::beginProfile('Compress javascript files.');
            foreach ($view->jsFiles as $pos => $files) {
                if (empty($files)) {
                    continue;
                }
                $view->jsFiles[$pos] = $this->_jsCompressor->compressFiles($files);
            }
            Yii::endProfile('Compress javascript files.');
        }

        // css files
        if (!empty($view->cssFiles)) {
            Yii::beginProfile('Compress css files.');
            $view->cssFiles = $this->_cssCompressor->compressFiles($view->cssFiles);
            Yii::endProfile('Compress css files.');
        }
    }

    /**
     * @param $content
     * @return string
     */
    private function compressHtml($content) : string
    {
        switch ($this->htmlFormatStrategy) {
            case HtmlFormatStrategy::MRCLAY: {
                Yii::beginProfile('Compress html mrclay.');
                $result = MrclayHtmlCompressor::compress($content);
                Yii::endProfile('Compress html mrclay.');
                break;
            }
            case HtmlFormatStrategy::TYLER: {
                Yii::beginProfile('Compress html tyler.');
                $result = TylerHtmlCompressor::compress($content);
                Yii::endProfile('Compress html tyler.');
                break;
            }
            default: {
                $result = &$content;
            }
        }

        return $result;
    }
}
