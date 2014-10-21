<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <title>Simple OPML Parser Demo</title>
    </head>

    <body>

        <form action="" method="post" enctype="multipart/form-data">
            <label for="file">Filename:</label>
            <input type="file" name="opmlFile" accept=".opml"><br>
            <input type="submit" name="submit" value="Submit">
        </form>

        <br/>

        <?php
        if (isset($_POST['submit'])) {

            require_once('../SimpleOpmlParser.php');

            $sop = new SimpleOpmlParser();
            $sop->setOpmlUploadName("opmlFile"); //must be in html input name
            $sop->setUploadDir("upload"); //optional, default: upload, need full rights for the dir
            $sop->setMaxOpmlSize(512); //optional, in kilobytes KB, default: 512
            $sop->setWrongOpmlMessage("OPML file does not meet the standard."); //optional
            $sop->setUploadFailMessage("Failed to upload OPML file."); //optional
            $opml = $sop->init();
            
            if ($opml == true) {
                if (isset($opml->head->title)) {
                    echo "OPML file title: " . $opml->head->title . "<br/><br/>";
                }

                echo "Upload directory: " . $sop->uploadDir . "<br/>";
                echo "Max upload file size: " . $sop->maxOpmlSize . " KB <br/>";
                echo "HTML input name: " . $sop->opmlUploadName . "<br/><br/>";

                if (count($opml->body->outline->outline) == 0) { //no categories
                    foreach ($opml->body->outline as $outline) {
                        echo "Outline type: " . $outline->attributes()->type . "<br/>";
                        echo "Outline text: " . $outline->attributes()->text . "<br/>";
                        echo "Outline xmlUrl: " . $outline->attributes()->xmlUrl . "<br/><br/>";
                    }
                } else { //with categories
                    foreach ($opml->body->outline as $outline) {
                        echo "Category text: " . $outline->attributes()->text . "<br/>";
                        foreach ($opml->body->outline->outline as $outline2) {
                            echo "Outline type: " . $outline2->attributes()->type . "<br/>";
                            echo "Outline text: " . $outline2->attributes()->text . "<br/>";
                            echo "Outline xmlUrl: " . $outline2->attributes()->xmlUrl . "<br/>";
                        }
                        echo "<br/>";
                    }
                }

                //$sop->deleteOpml(); //delete file from the upload directory
            }
        }
        ?>

    </body>

</html>