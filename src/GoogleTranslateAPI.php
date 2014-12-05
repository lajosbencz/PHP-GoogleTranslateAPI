<?php


namespace LajosBencz\GoogleTranslateAPI;


class GoogleTranslateException extends \Exception { }

class GoogleTranslateAPI {

    const API_URL = "https://www.googleapis.com/language/translate/v2/";

    protected $_key;
    protected $_source;
    protected $_target;

    public function __construct($key, $source="en", $target=null) {
        $this->_key = $key;
        $this->_source = $source;
        $this->_target = $target;
    }

    protected function _rest(array $parameters, $path="") {
        $parameters["key"] = $this->_key;
        $url = self::API_URL.$path."?".http_build_query($parameters);
        $curl = curl_init($url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        $r = curl_exec($curl);
        curl_close($curl);
        $j = json_decode($r, true);
        if(isset($j["error"])) throw new GoogleTranslateException($j["error"]["message"],$j["error"]["code"]);
        return $j;
    }

    public function listLanguages($target=null) {
        $languages = array();
        $d = array();
        if($target) $d["target"] = $target;
        $r = $this->_rest($d,"languages");
        foreach($r["data"]["languages"] as $l) $languages[$l["language"]] = isset($l["name"])?$l["name"]:$l["language"];
        return $languages;
    }

    public function translate($text, $target=null, $source=null) {
        $d = array(
            "q" => $text,
        );
        if(!$target) $target = $this->_target;
        if($target) $d["target"] = $target;
        if(!$source) $source = $this->_source;
        if($source) $d["source"] = $source;
        $r = $this->_rest($d);
        if(isset($r["data"]) && isset($r["data"]["translations"]) && count($r["data"]["translations"])>0) return $r["data"]["translations"][0]["translatedText"];
        return "[(XL_FAIL) ".$text."]";
    }

    public function detect($text) {
        $d = array(
            "q" => $text,
        );
        $r = $this->_rest($d,"detect");
        if(isset($r["data"]) && isset($r["data"]["detections"]) && count($r["data"]["detections"][0])>0) return $r["data"]["detections"][0][0]["language"];
        return false;
    }

}
