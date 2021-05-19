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
    $ux_set = 0;
    if (isset($_REQUEST["ux"])) {
        $ux_set = 1;
        $ux = explode(",", $_REQUEST["ux"]);
    }

    /* iframe ? */
    $ix = (isset($_REQUEST["ix"])) ?  $_REQUEST["ix"]:  0;

    if ($DBG) d_print("<br/>rx: $rx, mx: $mx, kx: $kx, ix: $ix");
    for ($jx = 0; $jx < sizeof($ux); ++$jx) {
        if ($DBG) d_print("<br/>jx: $jx, ux[$jx]: $ux[$jx] ");
    }
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
        
        if (!$ux_set) { if ($DBG) d_print("<br/> ux is not set"); }
        if ($ux_set) {
            if ($DBG) d_print("<br/> ux is:" . implode(",",$ux));
        }

        /* generate a new kural number if its reload nor urai request */
        if (!$ux_set || $rx ) {
            if ($DBG) d_print("<br/>In random: rx: $rx");
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
        if (!$ux[0]) {
            /* displaying kural, set urai to '3' to display meaning     */
            $ux[0]  = 3;
            $ux_set = 1;
            d_print("<br/> only meaning flag set, ux[0]: $ux[0]");
            d_print("<br/> size of ux: " . sizeof($ux));
            
        }
    }

    $k_path = sprintf("%s/k1/k1-%04d.png", $dir_path, $lk);
    if ($ux_set)  {
        for ($jx = 0; $jx < sizeof($ux); ++$jx) {
            $u_path[$jx] = sprintf("%s/p%d/p%d-%04d.png", 
                                        $dir_path, $ux[$jx], $ux[$jx], $lk);
        }
    }

    if ($ix) { /* display in iframe? */
        if ($mx) {  /* we've to display both kural and meaning  */
            print "<br/>$k_path";
            for ($jx = 0; $jx < sizeof($ux); ++$jx) {
                print "<br/>$u_path[$jx]";
            }
        } else {
            print "<br/>$k_path";
        }
    } else {
        if ($mx) {  /* we've to display both kural and meaning  */
            $kimg = imagecreatefrompng($k_path);
            list($kw1, $kh1, $kt1, $kat1) = getimagesize($k_path);
            $dst_ht = $kh1;
            for ($jx = 0; $jx < sizeof($ux); ++$jx) {
                $uimg[$jx] = imagecreatefrompng($u_path[$jx]);
                list($kw2[$jx], $kh2[$jx], $kt2[$jx], $kat2[$jx]) = 
                                                    getimagesize($u_path[$jx]);
                $dst_ht += $kh2[$jx];
            }
            $dst = imagecreatetruecolor($kw1, $dst_ht);
            imagecopy($dst, $kimg, 0, 0, 0, 0, $kw1, $kh1);
            imagedestroy($kimg);
            $dst_ht = $kh1;
            for ($jx = 0; $jx < sizeof($ux); ++$jx) {
                imagecopy($dst,$uimg[$jx],0,$dst_ht,0,0,$kw2[$jx], $kh2[$jx]);
                $dst_ht += $kh2[$jx];
                imagedestroy($uimg[$jx]);
            }
        } else {    /* we need to display one image, either kural or urai   */
            if ($ux_set && $ux) {
                $img_path = $u_path[0];
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
