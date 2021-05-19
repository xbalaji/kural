<?php 
    $update = $_GET["u"];
    $fp = fopen("/home/vhttpd/noip/kural/tnuoc.txt", "r");
    $count = fread($fp, 1024);
    fclose($fp);
    if (isset($update)) {
        $count++;
        $fp = fopen("/home/vhttpd/noip/kural/tnuoc.txt", "w");
        fwrite($fp, $count);
        fclose($fp);
    }
    print $count;
?> 
