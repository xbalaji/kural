<?
if (!extension_loaded("gd")) {
    if (!dl("gd.so")) {
        exit;
    }
}

$DBG = 0;
$dir_path  = "/home/httpd/kural/png";

function d_print ($string)
{
    global $DBG;
    if ($DBG) print $string;
}


define("MAX_KURAL", "1330");

    /* reload   */
    $rx = (isset($_REQUEST["rx"])) ?  $_REQUEST["rx"]:  0;

    /* meaning  */
    $mx = (isset($_REQUEST["mx"])) ?  $_REQUEST["mx"]:  0;

    /* kural display */ 
    if (isset($_REQUEST["kx"])) {
        $kx = $_REQUEST["kx"];
    }

    /* meaning display */
    $mx = (isset($kx) ?  $kx : $mx);    /* set meaning flag */

    /* urai number */
    if (isset($_REQUEST["ux"])) {
        $ux = $_REQUEST["ux"];
    }

    /* iframe ? */
    $ix = (isset($_REQUEST["ix"])) ?  $_REQUEST["ix"]:  0;

    if ($DBG) d_print("<br/>rx: $rx, mx: $mx, kx: $kx, ux: $ux, ix: $ix");
    if ($DBG) $ix = 1;
    if ($DBG) d_print("<br/>setting ix: $ix\n");

    /*
       Dont read _REQUEST["lk"], since it might return cookie value, look
       for kural number in GET or POST, ift is not set, read the cookie
       and generate a new one
    */
    if (isset($_REQUEST["no"])) {
        $lk = $_REQUEST["no"];
    }
    if (!isset($lk)) {
        if (isset($_COOKIE["lk"])) {
            $lk = $_COOKIE["lk"];
        }

        srand(time());
        if (!isset($lk)) {
            $lk = (rand() % MAX_KURAL) + 1;
        }
        
        if (!isset($ux)) { if ($DBG) d_print("<br/> ux is not set: $ux"); }
        if (isset($ux))  { if ($DBG) d_print("<br/> ux is set: $ux"); }

        /* generate a new kural number if its reload nor urai request */
        if (!isset($ux) || $rx ) {
            if ($DBG) d_print("<br/>In random: rx: $rx, ux: $ux");
            do {
                $random = (rand() % MAX_KURAL) + 1;
            }
            while ($random == $lk);
            $lk = $random;
            setcookie("lk", $random);
        }
    } else { /* set the kural if its already sent in GET or POST */
        setcookie("lk", $lk);
    }


    /* check if meaning flag is set */
    if ($mx) {
        if (!$ux) {
            /* displaying kural, set urai to '3' to display meaning     */
            $ux  = 3;
        }
    }

    $k_path = sprintf("%s/k1/k1-%04d.png", $dir_path, $lk);
    if (isset($ux))  {
        $u_path = sprintf("%s/p%d/p%d-%04d.png", $dir_path, $ux, $ux, $lk);
    }

    if ($ix) { /* display in iframe? */
        if ($mx) {  /* we've to display both kural and meaning  */
            print "<br/>$k_path";
            print "<br/>$u_path";
        } else {
            print "<br/>$k_path";
        }
    } else {
        if ($mx) {  /* we've to display both kural and meaning  */
            $kimg = imagecreatefrompng($k_path);
            $uimg = imagecreatefrompng($u_path);
            list($kw1, $kh1, $kt1, $kat1) = getimagesize($k_path);
            list($kw2, $kh2, $kt2, $kat2) = getimagesize($u_path);
            $dst = imagecreatetruecolor($kw1, ($kh1 + $kh2));
            imagecopy($dst, $kimg, 0, 0, 0, 0, $kw1, $kh1);
            imagecopy($dst, $uimg, 0, $kh1, 0, 0, $kw2, $kh2);
            imagedestroy($kimg);
            imagedestroy($uimg);
        } else {    /* we need to display one image, either kural or urai   */
            if (isset($ux) && $ux) {
                $img_path = $u_path;
            } else {
                $img_path = $k_path;
            }
            $dst = imagecreatefrompng($img_path);
        }
        header("cache-control: no-store, no-cache, must-revalidate");
        header("cache-control: post-check=0, pre-check=0", false);
        header("pragma: no-cache" ); 
        header("Content-type: image/png");
        imagepng($dst);
        imagedestroy($dst);
    }
?>
