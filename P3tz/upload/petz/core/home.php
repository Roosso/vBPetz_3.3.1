<?php
/*******************************************\
*	P3tz [vb]		  	File: home.php		*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}

if ($petgroup['own'] == "1") {
	$pets = $vbulletin->db->query_read("SELECT id,type,name,ownerid,gender,color,moral,dob,hunger,health,mhealth,level
	FROM " . TABLE_PREFIX . "petz_petz WHERE dead=0 AND ownerid=". $vbulletin->userinfo['userid'] ."");
	while ($pet = $vbulletin->db->fetch_array($pets)){
		$pet['name'] = stripslashes($pet['name']);
		$pet['age'] = intval((TIMENOW - $pet['dob']) / 86400);
		$pcv = explode("-", $pet['color']);
		$pet['Rpcv'] = $pcv[0];
		$pet['Gpcv'] = $pcv[1];
		$pet['Bpcv'] = $pcv[2];
		if ($pet['moral'] < -50) {
			$pet['moral'] = "Evil";
		} elseif ($pet['moral'] > 50) {
			$pet['moral'] = "Good";
		} else {
			$pet['moral'] = "Neutral";
		}
		if ($pet[health]!=0) {
			$health=($pet['health']/$pet['mhealth'])*100;
			$pet['health']=($health/100)*65;
			$pet['health']=round($pet['health'], 0);
		}
		if ($pet['health']<1) {
		 	$pet['health']=0;
		} else {
			$pet['health']=$pet['health'];
		}
		if ($health<20) {
			$pet['status'] = "Injured";
		} elseif ($health<50) {
			$pet['status'] = "Ill";
		} elseif ($pet['hunger']>50) {
			$pet['status'] = "Hungry";
		} else {
			$pet['status'] = "Ok";
		}
		if ($pet['hunger']<1) {
			$pet['hunger']=0;
		} else {
			$pet['hunger']=65/(100/$pet['hunger']);
			$pet['hunger']=round($pet['hunger'], 0);
		}
		eval('$petz_petz_bit .= "' . fetch_template('petz_view') . '";');
	}
}
$items = $vbulletin->db->query_read("
		SELECT inventory.*,inventory.id AS iid, inventory.itemid AS itemid, inventory.userid AS userid, item.id AS id, item.name As name, item.description AS description, item.cost AS cost, item.image AS image, item.moral AS moral, item.hunger AS hunger, item.health AS health, item.special AS special
		FROM ".TABLE_PREFIX."petz_inventory AS inventory
		LEFT JOIN ".TABLE_PREFIX."petz_items AS item ON (inventory.itemid = item.id)
		WHERE userid = '".$vbulletin->userinfo['userid']."'
		ORDER BY inventory.id ASC");
$totalitems=$vbulletin->db->num_rows($items);
if ($totalitems!=0) {
	$noitems=0;
	while ($item=$vbulletin->db->fetch_array($items)) {
		if ($item['hunger']!=0) { $item['effect'].="".$vbphrase['petz_hunger'].": ".$item['hunger']."\n"; }
		if (($item['effect']!="") AND ($item['health']!=0)) { $item['effect'].="<br>\n"; }
		if ($item['health']!=0) { $item['effect'].="".$vbphrase['petz_health'].": ".$item['health']."\n"; }
		if (($item['effect']!="") AND ($item['moral']!=0)) { $item['effect'].="<br>\n"; }
		if ($item['moral']!=0) { $item['effect'].="".$vbphrase['petz_moral'].": ".$item['moral']; }
		if (($item['effect'] != "") AND ($item['special']!=0)) { $item['effect'].="<br>\n"; }
		if ($item['special'] == 1) { $item['effect'] .= $vbphrase['petz_change_name']; }
		if ($item['special'] == 2) { $item['effect'] .= $vbphrase['petz_change_color']; }
		if ($item['special'] == 3) { $item['effect'] .= $vbphrase['petz_kill']; }
		$item['value'] = round($item['cost']*$vbulletin->options['petz_taxsell']/100, 0);
		$item['value'] = $item['cost'] - $item['value'];
		$item['name'] = stripslashes($item['name']);
		$item['description'] = stripslashes($item['description']);
		$item['hasstock'] = 0;
		$item['canuse'] = 0;
		$item['canbuy'] = 0;
		$item['cantbuy'] = 0;
		$item['cansell'] = 0;
		$item['cansteal'] = 0;
		$item['vetuse'] = 0;
		if ((!isset($bgcolor)) OR ($bgcolor=="alt2")) {
			$bgcolor = "alt1";
		} else {
			$bgcolor = "alt2";
		}
		eval('$petz_inventory_bit .= "' . fetch_template('petz_item') . '";');
	}
} else {
	$noitems = 1;
	eval('$petz_inventory_bit .= "' . fetch_template('petz_item') . '";');
}
$totalmessages=0;
$pmss = $vbulletin->db->query_read("SELECT pms.*, sender.username AS username, sender.usergroupid AS usergroupid
	FROM " . TABLE_PREFIX . "petz_pms AS pms
	LEFT JOIN ".TABLE_PREFIX."user AS sender ON (pms.userid = sender.userid)
	WHERE pms.reciept=". $vbulletin->userinfo['userid'] ."");
while ($pms = $vbulletin->db->fetch_array($pmss)){
	if($pms['message']>0) {
		if ($pms['message']>5){
			$type = "<img src=\"petz/images/misc/info.gif\" alt=\"Информация\">";
		} else {
			$type = "<img src=\"petz/images/misc/alert.gif\" alt=\"Важно\">";
		}
		$message = $vbphrase['petz_pms_'.$pms['message'].''];
		$when = vbdate($vbulletin->options['dateformat'], $pms['footprint']);
		$when .= " - ";
		$when .= vbdate($vbulletin->options['timeformat'], $pms['footprint']);
		if ((!isset($bgcolor)) OR ($bgcolor == "alt2")) {
			$bgcolor = "alt1";
		} else {
			$bgcolor = "alt2";
		}
		if($pms['message'] == 1 || $pms['message'] == 2 || $pms['message'] == 5)
		{
			$who = "<b>Кто-то</b>";
		}
		else
		{
			$who = "<a href=\"member.php?u=".$pms['userid']."\">".fetch_musername($pms, 'usergroupid')."</a>";
		}
		$thepmsrow = "<tr class=\"$bgcolor\"><td>$type</td><td>$who $message</td><td>$when</td></tr>";
		eval('$petz_messages_bit .= $thepmsrow;');
		$totalmessages++;
	}
}
if($totalmessages > 0){
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_pms WHERE reciept=". $vbulletin->userinfo['userid'] ."");
}
eval('$petzbody = "' . fetch_template('petz_home') . '";');
// Cleanup
unset($pets, $pet, $items, $item, $pms, $pmss);
?>