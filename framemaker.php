<?php 

/**
 * Framemaker for framelet plugin
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Peeter Vois <peeter@tauria.ee>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

function framemaker( $data )
{
    $rv = "";
    $rv .= '<div id="'. $data["name"].'_data" style="display:none" >'. $data["database"] .'</div>';
    $rv .= '<input type="button" onclick="framelet_pull('."'".$data['name']."'".')" value="SAVE">';
    $rv .= '<input type="button" onclick="framelet_push('."'".$data['name']."'".')" value="REVERT">';
    $rv .= '<iframe ' .base64_decode($data["iframeparams"]).
    ' id="'. $data["name"].'_frame" frameborder=0 '.
    ' src=" ' . DOKU_BASE . base64_decode($data["iframehref"]) .'" ></iframe>';
    $rv .= '<script type="text/javascript" defer="defer">framelet_push("'.$data['name'].'")</script>';
    
    return $rv;
}

?>

