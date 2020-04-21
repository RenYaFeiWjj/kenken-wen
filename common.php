<?php

class Api{


    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function http_post($url, $param, $post_file = false)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curlFile = true;
        } else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        if (is_string($param)) {
            $strPOST = $param;
        } elseif ($post_file) {
            if ($is_curlFile) {
                foreach ($param as $key => $val) {
                    if (substr($val, 0, 1) == '@') {
                        $param[$key] = new \CURLFile(realpath(substr($val, 1)));
                    }
                }
            }
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_HEADER, false);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 任亚飞
     * 获取数据存入数据库
     */
    public function aa()
    {
        $data = [
            "em" => "c4adbd4359e447dd",
            "type" => "3",
            "fktype" => "-1",
            "fkid" => "0",
        ];
        $con = $this->http_post("http://yogreek.coincsd.com/Train/SelCSExaminate",$data);
        $data = \Qiniu\json_decode($con , true);
        if($data){
            $a = [];
            foreach($data['qinfo'] as $k=>$v){
                $title = $v['question'];
                $value = \GuzzleHttp\json_encode($v['alist']);
                $res = db('admin_ol')->where(['title' => $title])->find();
                if(!$res){
                    $a[] = db('admin_ol')->insert([
                        "title" => $title,
                        "value" => $value,
                    ]);

                }
            }
            print_r($a);exit;
        }
    }


    /**
     * @param $title
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 任亚飞
     * 查询接口
     */
    public function bb($title)
    {
        header('Access-Control-Allow-Origin: *');
        $data = db('admin_ol')->where('title', 'like', "%".$title."%")->select();
        foreach ($data as &$v){
            $v['value'] = \Qiniu\json_decode($v['value'] , true);
        }
        if($data){
            return \GuzzleHttp\json_encode(['code' => 1 , 'data' => $data]);
        }else{
            return \GuzzleHttp\json_encode(['code' => 0 , 'data' => []]);
        }
    }
}
