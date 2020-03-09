<?php
namespace dingdingwebhook;

use Yii;
use Requests;
use yii\base\Component;

/**
 * Class webhook
 *
 *         'webHook' => [
 *              'class' => 'dingdingwebhook\webhook',
 *               'webHookToken' => '4822f242fa710c7e43568bd8f1548fa1953e8b047a23422d5521c919d63c1284',
 *               'webHookUrl' => 'https://oapi.dingtalk.com/robot/send',
 *               'webHookMsgType'=>'text',
 *               'webHookToTel'=>['18611732502','18513116475']
 *           ],
 * eg. Yii::$app->webHook->send($content);
 * @package dingdingwebhook
 */
class webhook extends Component
{
    private $_header = ['Content-Type' => 'application/json', 'Charset' => 'utf-8'];

    public function init()
    {
        parent::init();
    }

    /**
     * @param string $content
     *
     * @return bool
     */
    public function send($content)
    {
        if (empty($content)) {
            return false;
        }

        if (empty($this->_toUrl)) {
            return false;
        }

        $sendContent = [
            'msgtype' => $this->webHookMsgType,
            $this->webHookMsgType => $this->setContent($content),
            'at' => [
                'atMobiles' => empty($this->webHookToTel) ? [] : $this->webHookToTel,
                'isAtAll' => false,
            ]
        ];
        $response = false;
        if (is_string($this->webHookUrl)) {
            $response = \Requests::post($this->webHookUrl, $this->_header, json_encode($sendContent));
        } else {
            foreach ($this->webHookUrl as $url) {
                $response = \Requests::post($url, $this->_header, json_encode($sendContent));
            }
        }
        return $response;
    }

    /**
     * @param $content
     *
     * @return array
     */
    public function setContent($content)
    {
        switch ($this->webHookMsgType) {
            case 'text':
                if (is_string($content)) {
                    $msgContent = ['content' => $content];
                }
                break;
            case 'link':
                $msgContent = [
                    'title' => isset($content['title']) ? $content['title'] : '默认title',
                    'text' => isset($content['text']) ? $content['text'] : '未设置',
                    'messageUrl' => isset($content['messageUrl']) ? $content['messageUrl'] : 'http://home.xiyunerp.com',
                    'picUrl' => ''
                ];
                break;
            case 'markdown':
                $msgContent = [
                    'title' => isset($content['title']) ? $content['title'] : '默认title',
                    'text' => isset($content['text']) ? $content['text'] : '未设置',
                ];
                break;
            default:
                if (is_string($content)) {
                    $msgContent = ['content' => $content];
                }
        }
        return $msgContent;
    }
}