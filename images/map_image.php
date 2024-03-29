<?php
	include("../includes/bootstrap.inc");
	session_start();
/**
 * Draw a block
 *
 * @param resource $im
 * @param array $blk
 * @param int $im_color
 * @param string $blktxt
 * @param int $blktxt_ftsz
 * @param int border
 */
function draw_block($im, array $blk, $im_color, $blktxt, $blktxt_ftsz, $border , $im_bclr){
	global $im;
	imagefilledrectangle($im, $blk[0], $blk[1], $blk[2], $blk[3], $im_color);
	if (isset($blktxt) && $blktxt !=='') {
		imagestring($im, $blktxt_ftsz, $blk[0]+1, round(($blk[1]+$blk[3])/2-8), $blktxt, $im_black);
	}
	if (isset($border) && $border==1) {
		imagerectangle($im, $blk[0], $blk[1], $blk[2], $blk[3], $im_bclr);
	}
}

/**
 * Draw the map based on the input array
 * 
 * @param array $darr everything to be drawn
 */
function draw_mapmx (array $darr) {
	// The canvas size of the image
	$imw=$darr['image_size'][0];
	$imh=$darr['image_size'][1];
	if ($imw*$imh>450000) ini_set("memory_limit","48M");
	// The blocks to be drawn in the image
	$blks=$darr['image_blks'];
	// The lines to be drawn in the image
	$dlns=$darr['image_dlns'];
	// The text (just text) to be drawn in the image
	$dtxs=$darr['image_dtxs'];
	global $im;
	$im=imagecreatetruecolor($imw, $imh);
	// color allocation
    $im_black=imagecolorallocate($im, 0x00, 0x00, 0x00);
    $im_white=imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
    $im_blue=imagecolorallocate($im, 0x00, 0x00, 0xFF);
    $im_gray=imagecolorallocate($im, 0x66, 0x66, 0x66);
    $im_orange=imagecolorallocate($im, 0xFF, 0x33, 0x00);
    $im_green=imagecolorallocate($im, 0x00, 0xFF, 0x33);
    $im_grayblue=imagecolorallocate($im, 0x99, 0xCC, 0xFF);
    $im_graydeepblue=imagecolorallocate($im, 0x33, 0x66, 0xCC);
    $im_bgblue=imagecolorallocate($im, 0xE9, 0xF1, 0xFF);
    $im_purple=imagecolorallocate($im, 0xFF, 0x33, 0xFF);
    $im_khaki3=imagecolorallocate($im, 0xC9, 0xBE, 0x62);
    $im_khaki2=imagecolorallocate($im, 0xED, 0xE2, 0x75);
    $im_whitesmoke=imagecolorallocate($im, 0xF5, 0xF5, 0xF5);
    $im_tomato=imagecolorallocate($im, 0xFF, 0x63, 0x47);
    $im_royalblue=imagecolorallocate($im, 0x41, 0x69, 0xE1);
    $im_salmon=imagecolorallocate($im, 0xFA, 0x80, 0x72);
    $im_seagreen=imagecolorallocate($im, 0x2E, 0x8B, 0x57);
    $im_mediumseagreen=imagecolorallocate($im, 0x3C, 0xB3, 0x71);
    $im_skyblue=imagecolorallocate($im, 0x87, 0xCE, 0xEB);
    imagefill($im, 0, 0, $im_bgblue);	
    // $style = array($im_blue, $im_blue, $im_blue, $im_blue, $im_blue, $im_bgblue, $im_bgblue, $im_bgblue, $im_bgblue, $im_bgblue);
    // imagesetstyle($im, $style);
	$font="../images/trebuchet.ttf";
   	for ($j=0; $j<count($dlns); $j++) {
   		imageline($im, $dlns[$j][0], $dlns[$j][1], $dlns[$j][2],$dlns[$j][3], ${$dlns[$j][4]});
   	}
   	for ($i=0; $i<count($blks); $i++) {
   		draw_block($im, $blks[$i]['coords'], ${$blks[$i]['imgclr']},$blks[$i]['text'], $blks[$i]['textsize'], $blks[$i]['border'], ${$blks[$i]['border_color']});
   	}
   	for ($k=0; $k<count($dtxs); $k++) {
   		imagestring($im, $dtxs[$k]['fontsize'], $dtxs[$k]['x'], $dtxs[$k]['y'], $dtxs[$k]['text'], ${$dtxs[$k]['text_clr']});
   		//imagettftext($im, $dtxs[$k]['fontsize'],0,$dtxs[$k]['x'], $dtxs[$k]['y'], ${$dtxs[$k]['text_clr']}, $font, $dtxs[$k]['text']);
   	}
    Header('Content-type: image/png');
    imagepng($im);
    imagedestroy($im);
}

connect();

ini_set("memory_limit","36M");
if (isset($_SESSION['draw_map_matrix'])) {
	$darr=$_SESSION['draw_map_matrix'];
	draw_mapmx($darr);
}
else {
	echo "No Image";
}

?>
