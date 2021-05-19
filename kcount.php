<?php 
    $update = $_GET["u"];
    $fp = fopen("./tnuoc.txt", "r");
    $count = fread($fp, 1024);
    fclose($fp);
    if (isset($update)) {
        $count++;
        $fp = fopen("./tnuoc.txt", "w");
        fwrite($fp, $count);
        fclose($fp);
    }
    print $count;
?> 
