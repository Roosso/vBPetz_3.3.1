<?php
/*******************************************\
*	P3tz [vb]		  	File: stealpet.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($id<1) {
	show_alert($vbphrase['petz_no_pet']);
} else {
	$pet = $vbulletin->db->query_first("SELECT p.id AS id, p.ownerid AS ownerid, p.care AS care, p.battle AS battle, p.dead AS dead,
	u.userid AS userid, u.usergroupid AS usergroupid, u.membergroupids AS membergroupids
	FROM " . TABLE_PREFIX . "petz_petz AS p
	LEFT JOIN ".TABLE_PREFIX."user AS u ON (p.ownerid = u.userid)
	WHERE p.id='".$id."'");
	if ($pet[id]<1) {
		show_alert($vbphrase['petz_no_pet']);
	} else {
		/* Immunity */
		$petgroup[immune]=0;
		$groupsid=explode(",", $vbulletin->options['petz_groupimmune']);
		foreach ($groupsid as $groupi) {
			if (is_member_of($pet, $groupi)) {
				$petgroup[immune]=1;
			}
		}
		if ($petgroup[immune]==1) {
			show_alert($vbphrase['steal_immune']);
		} elseif ($petgroup['own']==0) {
			print_no_permission();
			exit;
		} elseif ($pet[dead]==1) {
			show_alert($vbphrase['petz_is_dead']);
		} elseif ($pet[care]>0) {
			show_alert($vbphrase['petz_in_care']);
		} elseif ($pet[battle]>0) {
			show_alert($vbphrase['petz_in_battle']);
		} elseif (($vbulletin->options['petz_stealpets']==0) OR ($vbulletin->userinfo['userid']==$pet[ownerid])) {
			show_alert($vbphrase['petz_cant_steal']);
		} elseif ($vbulletin->userinfo['reputation']<$vbulletin->options['petz_stealrepfine']) {
			show_alert($vbphrase['petz_reputation_too_low']);
		} elseif ($vbulletin->userinfo[$vbulletin->options['petz_pfield']]<1){
			show_alert($vbphrase['petz_not_enough_points']);
		} else {
			$petq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo['userid']."'");
			$peti=$vbulletin->db->num_rows($petq);
			if ($peti>=$vbulletin->options['petz_maxpetz']) {
				show_alert($vbphrase['petz_pet_toomany']);
			} else {
				if ($vbulletin->options['petz_stealpets']>rand(0,100)) {
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET ownerid='".$vbulletin->userinfo['userid']."' WHERE id='".$pet[id]."'");
					petz_message($pet[ownerid],2);
					if ($vbulletin->options['petz_log']==1) {
						petz_log($op,$id,$do,$extra);
					}
					$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=view&id=$id";
					eval(print_standard_redirect('petz_redirect'));
					exit;
				} else {
					$fine=($vbulletin->userinfo[$vbulletin->options['petz_pfield']]/100)*$vbulletin->options['petz_stealpetfine'];
					$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$fine." WHERE userid='".$vbulletin->userinfo['userid']."'");
					$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+".$fine." WHERE userid='".$pet[ownerid]."'");
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET reputation=reputation-".$vbulletin->options['petz_stealrepfine']." WHERE userid='".$vbulletin->userinfo['userid']."'");
					petz_message($pet[ownerid],4);
					if ($vbulletin->options['petz_log']==1) {
						petz_log($op,$id,$do,$extra);
					}
					$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=view&id=$id";
					eval(print_standard_redirect('petz_redirect'));
					exit;
				}
			}
		}
	}
}
// Cleanup
unset($pet, $petq);
?>