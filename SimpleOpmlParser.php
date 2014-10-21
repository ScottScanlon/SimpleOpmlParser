<?php
libxml_use_internal_errors(true);

class SimpleOpmlParser {

    private $opmlFile;
    public $uploadDir = "upload";
    public $maxOpmlSize = 512; //KB
    public $opmlUploadName;
    public $wrongOpmlMessage = "OPML file does not meet the standard.";
    public $uploadFailMessage = "Failed to upload OPML file.";

    public function setUploadDir($uploadDir) {
        $this->uploadDir = $uploadDir;
    }

    public function setMaxOpmlSize($maxOpmlSize) {
        $this->maxOpmlSize = $maxOpmlSize;
    }

    public function setOpmlUploadName($opmlUploadName) {
        $this->opmlUploadName = $opmlUploadName;
    }

    public function setWrongOpmlMessage($wrongOpmlMessage) {
        $this->wrongOpmlMessage = $wrongOpmlMessage;
    }

    public function setUploadFailMessage($uploadFailMessage) {
        $this->uploadFailMessage = $uploadFailMessage;
    }

    public function init() {
        $uploadOpml = $this->uploadOpml();
        $checkOpml = $this->checkOpml();

        if ($uploadOpml == 1) {
            if ($checkOpml == 1) {
                return $this->loadOpml();
            } else {
                echo $this->wrongOpmlMessage;
                $this->deleteOpml();
            }
        } else {
            echo $this->uploadFailMessage;
        }
    }

    public function deleteOpml() {
        if (is_file($this->opmlFile)) {
            unlink($this->opmlFile);
        }
    }

    private function uploadOpml() {
        $allowedExts = array("opml");
        $temp = explode(".", $_FILES[$this->opmlUploadName]["name"]);
        $extension = end($temp);
        $opmlName = "opml_" . date('U') . date('B') . "." . $extension;
        $uploadDir = $this->uploadDir . DIRECTORY_SEPARATOR;
        if (//($_FILES[$this->opmlUploadName]["type"] == "text/x-opml") 
                ($_FILES[$this->opmlUploadName]["size"] / 1024 < $this->maxOpmlSize) && in_array($extension, $allowedExts)) {
            if ($_FILES[$this->opmlUploadName]["error"] > 0) {
                return 0;
            } else {
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir);
                    chmod($uploadDir, 0777);
                }
                move_uploaded_file($_FILES[$this->opmlUploadName]["tmp_name"], $uploadDir . $opmlName);
            }
        } else {
            return 0;
        }
        $this->opmlFile = $uploadDir . $opmlName;
        return 1;
    }

    private function loadOpml() {
        if (!isset($this->opmlFile)) {
            return 0;
        }

        $opmlContent = file_get_contents($this->opmlFile);
        $opmlContent = preg_replace('#&(?=[a-z_0-9]+=)#', '&amp;', $opmlContent);

        $opml = simplexml_load_string($opmlContent);

        if ($opml == false) {
            return 0;
        } else {
            return $opml;
        }
    }

    private function checkOpml() {
        if (!isset($this->opmlFile)) {
            return 0;
        }

        $opml = $this->loadOpml();
        if ($opml == false) {
            return 0;
        }
        $text = 0;
        $text2 = 0;
        $type2 = 0;
        $xmlUrl2 = 0;

        if ($opml->getName() == 'opml') {
            $count = 0;
            foreach ($opml->children() as $children) {
                if ($children->getName() == 'head') {
                    $count++;
                }
                if ($children->getName() == 'body') {
                    $count++;
                }
            }
            if ($count != 2) {
                return 0;
            }

            if ($opml["version"] != null) {
                if ("1.0" == $opml["version"] || "1.1" == $opml["version"] || "2.0" == $opml["version"]) {
                    foreach ($opml->body->children() as $outline) {
                        if ($outline->getName() == 'outline') {
                            foreach ($outline->attributes() as $a) {
                                if ($a->getName() == 'text') {
                                    $text = 1;
                                }
                            }

                            if ($text == 0) {
                                return 0;
                            } else {

                                if (count($outline->children()) == 0) { //first level outline

                                    foreach ($outline->attributes() as $a) {
                                        if ($a->getName() == 'type') {
                                            $type2 = 1;
                                            if ($a != 'rss') {
                                                return 0;
                                            }
                                        }
                                        if ($a->getName() == 'xmlUrl') {
                                            $xmlUrl2 = 1;
                                        }
                                    }
                                    if ($type2 == 0 || $xmlUrl2 == 0) {
                                        return 0;
                                    }
                                } else { //second level outline
                                    foreach ($outline->children() as $outline2) {
                                        if ($outline2->getName() == 'outline') {
                                            foreach ($outline2->attributes() as $a2) {
                                                if ($a2->getName() == 'text') {
                                                    $text2 = 1;
                                                }
                                                if ($a2->getName() == 'type') {
                                                    $type2 = 1;
                                                    if ($a2 != 'rss') {
                                                        return 0;
                                                    }
                                                }
                                                if ($a2->getName() == 'xmlUrl') {
                                                    $xmlUrl2 = 1;
                                                }
                                            }

                                            if ($text2 == 0 || $type2 == 0 || $xmlUrl2 == 0) {
                                                return 0;
                                            }
                                        } else {
                                            return 0;
                                        }
                                        $text2 = 0;
                                        $type2 = 0;
                                        $xmlUrl2 = 0;
                                    }
                                }
                            }
                        } else {
                            return 0;
                        }
                        $text = 0;
                        $type2 = 0;
                        $xmlUrl2 = 0;
                    }
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }

        return 1;
    }

}
?>