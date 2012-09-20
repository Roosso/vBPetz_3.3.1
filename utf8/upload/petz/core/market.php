<?php
/*******************************************\
*	P3tz [vb]		  	File: market.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
$marketq = $vbulletin->db->query_read("SELECT * FROM ".TABLE_PREFIX."petz_items ORDER BY cost ASC");
$marketi = $vbulletin->db->num_rows($marketq);
if ($marketi != 0) {
	$noitems = 0;
	while ($item = $vbulletin->db->fetch_array($marketq)) {
		if ($item['hunger'] != 0) { $item['effect'] .= "".$vbphrase['petz_hunger'].": ".$item['hunger']."\n"; }
		if (($item['effect'] != "") AND ($item['health'] != 0)) { $item['effect'].="<br>\n"; }
		if ($item['health'] != 0) { $item['effect'] .= $vbphrase['petz_health'].": ".$item['health']."\n"; }
		if (($item['effect'] != "") AND ($item['moral'] != 0)) { $item['effect'].="<br>\n"; }
		if ($item['moral'] != 0) { $item['effect'] .= $vbphrase['petz_moral'].": ".$item['moral']; }
		if (($item['effect'] != "") AND ($item['special'] != 0)) { $item['effect'].="<br>\n"; }
		if ($item['special'] == 1) { $item['effect'] .= $vbphrase['petz_change_name']; }
		if ($item['special'] == 2) { $item['effect'] .= $vbphrase['petz_change_color']; }
		if ($item['special'] == 3) { $item['effect'] .= $vbphrase['petz_kill']; }
		$item['name'] = stripslashes($item['name']);
		$item['description'] = stripslashes($item['description']);
		$item['canuse'] = 0;
		if ($vbulletin->options['petz_stock'] == 1) {
			$item['hasstock'] = 1;
		} else {
			$item['hasstock'] = 0;
		}
		if (($item['stock'] > 0) OR ($vbulletin->options['petz_stock'] == 0)) {
			if ($item['cost'] <= $vbulletin->userinfo[$vbulletin->options['petz_pfield']]){
				$item['canbuy'] = 1;
				$item['cantbuy'] = 0;
			} else {
				$item['canbuy'] = 0;
				$item['cantbuy'] = 1;
			}
		} else {
			$item['canbuy'] = 0;
			$item['cantbuy'] = 1;
		}
		$item['vetuse'] = 0;
		$item['cansell'] = 0;
		$item['cansteal'] = 0;
		if ((!isset($bgcolor)) OR ($bgcolor == "alt2")) {
			$bgcolor = "alt1";
		} else {
			$bgcolor = "alt2";
		}
		if (($item['stock'] > 0) OR ($item['restock'] > 0)) {
			eval('$petz_shop_bit .= "' . fetch_template('petz_item') . '";');
		}
	}
} else {
	$noitems = 1;
	eval('$petz_shop_bit .= "' . fetch_template('petz_item') . '";');
}
unset($item);
// Inventory
$items = $vbulletin->db->query_read("
		SELECT inventory.*,inventory.id AS iid, inventory.itemid AS itemid, inventory.userid AS userid, item.id AS id, item.name As name, item.description AS description, item.cost AS cost, item.image AS image, item.moral AS moral, item.hunger AS hunger, item.health AS health, item.special AS special
		FROM ".TABLE_PREFIX."petz_inventory AS inventory
		LEFT JOIN ".TABLE_PREFIX."petz_items AS item ON (inventory.itemid = item.id)
		WHERE userid = '".$vbulletin->userinfo['userid']."'
		ORDER BY inventory.id ASC");
$totalitems = $vbulletin->db->num_rows($items);
if ($totalitems != 0) {
	$noitems = 0;
	while ($item = $vbulletin->db->fetch_array($items)) {
		if ($item['hunger'] != 0) { $item['effect'] .= $vbphrase['petz_hunger'].": ".$item['hunger']."\n"; }
		if (($item['effect'] != "") AND ($item['health'] != 0)) { $item['effect'] .= "<br>\n"; }
		if ($item['health'] != 0) { $item['effect'] .= $vbphrase['petz_health'].": ".$item['health']."\n"; }
		if (($item['effect'] != "") AND ($item['moral'] != 0)) { $item['effect'] .= "<br>\n"; }
		if ($item['moral'] != 0) { $item['effect'] .= $vbphrase['petz_moral'].": ".$item['moral']; }
		if (($item['effect'] != "") AND ($item['special'] != 0)) { $item['effect'] .= "<br>\n"; }
		if ($item['special'] == 1) { $item['effect'] .= $vbphrase['petz_change_name']; }
		if ($item['special'] == 2) { $item['effect'] .= $vbphrase['petz_change_color']; }
		if ($item['special'] == 3) { $item['effect'] .= $vbphrase['petz_kill']; }
		$item['value'] = round($item['cost']*$vbulletin->options['petz_taxsell']/100, 0);
		$item['value'] = $item['cost'] - $item['value'];
		$item['name'] = stripslashes($item['name']);
		$item['description'] = stripslashes($item['description']);
		$item['hasstock'] = 0;
		$item['canuse']=0;
		$item['canbuy']=0;
		$item['cantbuy']=0;
		$item['cansell']=1;
		$item['cansteal']=0;
		$item['vetuse']=0;
		if ((!isset($bgcolor)) OR ($bgcolor == "alt2")) {
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
eval('$petzbody = "' . fetch_template('petz_market') . '";');
// Cleanup
unset($item, $marketq, $items);
?>