<?php

namespace Wanglu\Ueditor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Wanglu\Ueditor\Uploader;

class UeditorController extends Controller
{
    /**
     * 编辑器配置
     */
    public function index(Request $request){
        $config = config('ueditor.upload');
        $action = $request->get('action');

        switch ($action) {
            case 'config':
                $result =  $config;
                break;

            /* 上传图片 */
            case 'uploadimage':
            /* 上传涂鸦 */
            case 'uploadscrawl':
            /* 上传视频 */
            case 'uploadvideo':
            /* 上传文件 */
            case 'uploadfile':
                $result = $this->uploads($config);
                break;
            /* 列出图片 */
            case 'listimage':
                $result = include("action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $result = include("action_list.php");
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = include("action_crawler.php");
                break;

            default:
                $result = array('state'=> '请求地址出错');
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                $data = array('state'=> 'callback参数不合法');
                return response()->json($data);
            }
        } else {
            return response()->json($result);
        }
    }

    /**
     * 上传处理
     * @return [type] [description]
     */
    protected function uploads($upconfig = array()){
        $base64 = "upload";
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $upconfig['imagePathFormat'],
                    "maxSize" => $upconfig['imageMaxSize'],
                    "allowFiles" => $upconfig['imageAllowFiles']
                );
                $fieldName = $upconfig['imageFieldName'];
                break;
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $upconfig['scrawlPathFormat'],
                    "maxSize" => $upconfig['scrawlMaxSize'],
                    "allowFiles" => $upconfig['scrawlAllowFiles'],
                    "oriName" => "scrawl.png"
                );
                $fieldName = $upconfig['scrawlFieldName'];
                $base64 = "base64";
                break;
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $upconfig['videoPathFormat'],
                    "maxSize" => $upconfig['videoMaxSize'],
                    "allowFiles" => $upconfig['videoAllowFiles']
                );
                $fieldName = $upconfig['videoFieldName'];
                break;
            case 'uploadfile':
            default:
                $config = array(
                    "pathFormat" => $upconfig['filePathFormat'],
                    "maxSize" => $upconfig['fileMaxSize'],
                    "allowFiles" => $upconfig['fileAllowFiles']
                );
                $fieldName = $upconfig['fileFieldName'];
                break;
        }

        $up = new Uploader($fieldName, $config, $base64);

        return $up->getFileInfo();
    }

}
