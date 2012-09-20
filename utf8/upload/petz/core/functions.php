<?php
/*******************************************\
*	P3tz [vb]		  	File: functions.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
function show_alert($message) { // Stop Error
	if($message==""){ $message="Untrapped Error"; }
	eval(standard_error($message));
	exit;
}
function petz_message($rid,$mid) { // P3tz Message System (PMS)
	global $vbulletin;
	if($mid>0){
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_pms (id,userid,message,reciept,footprint)
		VALUES('',".$vbulletin->userinfo['userid'].",'".$mid."','".$rid."','".TIMENOW."')");
	}
}
function petz_ui($vname,$vflag,$vtype){ // User Inputs
	global $vbulletin;
	$value=$vbulletin->input->clean_gpc("$vflag", "$vname", $vtype);
	if ($value=="") {
		if ($vflag=="p") {
			$value=$_POST[$vname];
		} elseif ($vflag=="g") {
			$value=$_GET[$vname];
		} else {
			$value=$_REQUEST[$vname];
		}
		$value=preg_replace('/</', '&lt;', $value);
	}
	return $value;
}
function petz_log($op,$id,$do,$extra) { // P3tz Log System
	global $vbulletin;
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_log (id,userid,pdo,pid,pop,extra,logtime)
	VALUES('',".$vbulletin->userinfo['userid'].",'".$vbulletin->db->escape_string($do)."', '".$vbulletin->db->escape_string($id)."',
	'".$vbulletin->db->escape_string($op)."', '".$vbulletin->db->escape_string($extra)."', '".TIMENOW."')");
}
?>