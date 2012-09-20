<?php
/*******************************************\
*	P3tz [vb]		  	File: buy.php		*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($id<1) {
	show_alert($vbphrase['petz_item_none']);
} else {
	$item = $vbulletin->db->query_first("SELECT id, cost, stock FROM " . TABLE_PREFIX . "petz_items WHERE id='".$id."'");
	if ($item[id]<1) {
		show_alert($vbphrase['petz_item_none']);
	} elseif (($item[stock]<1) AND ($vbulletin->options['petz_stock']==1)) {
		show_alert($vbphrase['petz_item_nostock']);
	} elseif ($item[cost]>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_item_cantafford']);
	} else {
		$inventoryq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_inventory WHERE userid='".$vbulletin->userinfo[userid]."' LIMIT ".$vbulletin->options['petz_maxitems']."");
		$inventory = $vbulletin->db->num_rows($inventoryq);
		if ($inventory>=$vbulletin->options['petz_maxitems']) {
			show_alert($vbphrase['petz_item_toomany']);
		} else {
			if ($vbulletin->options['petz_stock']==1) {
				$item[stock]=$item[stock]-1;
			}
			$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-$item[cost] WHERE userid='".$vbulletin->userinfo['userid']."'");
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_inventory (id,userid,itemid) VALUES('',".$vbulletin->userinfo['userid'].",".$item[id].")");
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_items SET sold=sold+1, stock='".$item[stock]."' WHERE id='".$item[id]."'");
			if ($vbulletin->options['petz_log']==1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=market";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	}
}
// Cleanup
unset($item, $inventoryq);
?>