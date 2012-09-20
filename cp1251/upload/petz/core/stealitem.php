<?php
/*******************************************\
*	P3tz [vb]		  	File: stealitem.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($id<1) {
	show_alert($vbphrase['petz_no_item']);
} else {
	$item = $vbulletin->db->query_first("SELECT i.id AS id, u.userid AS userid, u.usergroupid AS usergroupid,
	u.membergroupids AS membergroupids
	FROM " . TABLE_PREFIX . "petz_inventory AS i
	LEFT JOIN ".TABLE_PREFIX."user AS u ON (i.userid = u.userid)
	WHERE i.id='".$id."'");
	if ($item[id]<1) {
		show_alert($vbphrase['petz_no_item']);
	} else {
		/* Immunity */
		$petgroup[immune]=0;
		$groupsid=explode(",", $vbulletin->options['petz_groupimmune']);
		foreach ($groupsid as $groupi) {
			if (is_member_of($item, $groupi)) {
				$petgroup[immune]=1;
			}
		}
		if ($petgroup[immune]==1) {
			show_alert($vbphrase['steal_immune']);
		} elseif (($vbulletin->options['petz_stealitems']==0) OR ($vbulletin->userinfo['userid']==$item[userid])) {
			show_alert($vbphrase['petz_item_cant_steal']);
		} elseif ($vbulletin->userinfo['reputation']<$vbulletin->options['petz_stealrepfine']) {
			show_alert($vbphrase['petz_reputation_too_low']);
		} elseif ($vbulletin->userinfo[$vbulletin->options['petz_pfield']]<1){
			show_alert($vbphrase['petz_not_enough_points']);
		} else {
			$inventoryq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_inventory WHERE userid='".$vbulletin->userinfo[userid]."' LIMIT ".$vbulletin->options['petz_maxitems']."");
			$inventory = $vbulletin->db->num_rows($inventoryq);
			if ($inventory>=$vbulletin->options['petz_maxitems']) {
				show_alert($vbphrase['petz_item_toomany']);
			} else {
				if ($vbulletin->options['petz_stealitems']>rand(0,100)) {
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_inventory SET userid='".$vbulletin->userinfo['userid']."' WHERE id='".$item[id]."'");
					petz_message($item[userid],1);
					if ($vbulletin->options['petz_log']==1) {
						petz_log($op,$id,$do,$extra);
					}
					$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=home";
					eval(print_standard_redirect('petz_redirect'));
					exit;
				} else {
					$fine=($vbulletin->userinfo[$vbulletin->options['petz_pfield']]/100)*$vbulletin->options['petz_stealitemfine'];
					$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$fine." WHERE userid='".$vbulletin->userinfo['userid']."'");
					$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+".$fine." WHERE userid='".$item[userid]."'");
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET reputation=reputation-".$vbulletin->options['petz_stealrepfine']." WHERE userid='".$vbulletin->userinfo['userid']."'");
					petz_message($item[userid],3);
					if ($vbulletin->options['petz_log']==1) {
						petz_log($op,$id,$do,$extra);
					}
					$vbulletin->url = 'member.php?' . $vbulletin->session->vars['sessionurl'] . "u=".$item['userid']."";
					eval(print_standard_redirect('petz_redirect'));
					exit;
				}
			}
		}
	}
}
// Cleanup
unset($item, $inventoryq);
?>