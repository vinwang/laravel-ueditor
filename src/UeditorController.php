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
            /* 抓取远程文件 */
            case 'catchimage':
                $result = $this->uploads($config, $request);
                break;
            /* 列出图片 */
            case 'listimage':
            /* 列出文件 */
            case 'listfile':
                $result = $this->list($config, $request);
                break;
            default:
                $result = array('state'=> '请求地址出错');
                break;
        }
// dd($result);
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                $data = array('state'=> 'callback参数不合法');

                return $data;
                // return response()->json($data);
            }
        } else {
            return $result;
            // return response()->json($result);
        }
    }

    /**
     * 上传处理
     * @return [type] [description]
     */
    protected function uploads($upconfig = array(), Request $request){
        $base64 = "upload";
        $action = $request->input('action');
        switch ($action) {
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
            case 'catchimage':
                $config = [
                    "pathFormat" => $upconfig['catcherPathFormat'],
                    "maxSize" => $upconfig['catcherMaxSize'],
                    "allowFiles" => $upconfig['catcherAllowFiles'],
                    "oriName" => "remote.png"
                ];
                $fieldName = $upconfig['catcherFieldName'];
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

        if($action == 'catchimage'){
            $list = [];
            $source = $request->input($fieldName);
            foreach ($source as $imgUrl) {
                $item = new Uploader($imgUrl, $config, "remote");
                $info = $item->getFileInfo();
                array_push($list, array(
                    "state" => $info["state"],
                    "url" => $info["url"],
                    "size" => $info["size"],
                    "title" => htmlspecialchars($info["title"]),
                    "original" => htmlspecialchars($info["original"]),
                    "source" => htmlspecialchars($imgUrl)
                ));
            }
            /* 返回抓取数据 */
            return json_encode(array(
                'state'=> count($list) ? 'SUCCESS':'ERROR',
                'list'=> $list
            ));
        }
        else{
            $up = new Uploader($fieldName, $config, $base64);

            return $up->getFileInfo();
        }
    }

    protected function list($upconfig = array(), Request $request){
        $action = $request->input('action');
        switch ($_GET['action']) {
            /* 列出文件 */
            case 'listfile':
                $allowFiles = $upconfig['fileManagerAllowFiles'];
                $listSize = $upconfig['fileManagerListSize'];
                $path = $upconfig['fileManagerListPath'];
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = $upconfig['imageManagerAllowFiles'];
                $listSize = $upconfig['imageManagerListSize'];
                $path = $upconfig['imageManagerListPath'];
        }

        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size = $request->filled('size') ? htmlspecialchars($request->input('size')) : $listSize;
        $start = $request->filled('start') ? htmlspecialchars($request->input('start')) : 0;
        $end = $start + $size;

        /* 获取文件列表 */
        $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;

        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            return json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }

        /* 返回数据 */
        $result = json_encode(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ));

        return $result;
    }

    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    private function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if(substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                        $files[] = array(
                            'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime'=> filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }

}
