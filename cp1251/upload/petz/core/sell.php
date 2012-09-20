<?php
/*******************************************\
*	P3tz [vb]		  	File: sell.php		*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($id<1) {
	show_alert($vbphrase['petz_no_item']);
} else {
	$iitem = $vbulletin->db->query_first("SELECT id, itemid FROM " . TABLE_PREFIX . "petz_inventory WHERE id='".$id."' AND userid='".$vbulletin->userinfo['userid']."'");
	if ($iitem[id]<1) {
		show_alert($vbphrase['petz_no_item']);
	} else {
		$item = $vbulletin->db->query_first("SELECT id, cost, stock FROM " . TABLE_PREFIX . "petz_items WHERE id='".$iitem[itemid]."'");
		$item[value]=round($item[cost]*$vbulletin->options['petz_taxsell']/100, 0);
		$item[value]=$item[cost]-$item[value];
		if ($vbulletin->options['petz_stock']==1) {
			$item[stock]=$item[stock]+1;
		}
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+$item[value] WHERE userid='".$vbulletin->userinfo['userid']."'");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_inventory WHERE id='".$iitem[id]."'");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_items SET bought=bought+1, stock='".$item[stock]."' WHERE id='".$item[id]."'");
		if ($vbulletin->options['petz_log']==1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=market";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
}
// Cleanup
unset($item, $iitem);
?>