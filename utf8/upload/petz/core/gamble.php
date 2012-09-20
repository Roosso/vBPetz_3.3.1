<?php
/*******************************************\
*	P3tz [vb]		  	File: gamble.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($vbulletin->userinfo['userid']<1){
	print_no_permission();
	exit;
} elseif(($op=="buy") AND ($id<1)) {
	$battles = $vbulletin->db->query_read("SELECT id,title,round FROM " . TABLE_PREFIX . "petz_battle WHERE
	type<3 AND round<5 ORDER by created DESC");
	while ($battle=$vbulletin->db->fetch_array($battles)) {
		if ($battle[round]>0){
			$battle[title]=stripslashes($battle[title]);
			$option.="<option value=\"$battle[id]\">$battle[title]</option>\n";
		}
	}
	if($option==""){
		show_alert($vbphrase['petz_no_battles']);
	}
} elseif(($op=="claim") AND ($id>0)){
	$ticket = $vbulletin->db->query_first("SELECT * FROM ".TABLE_PREFIX."petz_gamble WHERE id=$id AND
	userid = '".$vbulletin->userinfo['userid']."'");
	if ($ticket[won]==0) {
		show_alert($vbphrase['petz_battle_not_finish']);
	} elseif ($ticket[won]==2) {
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
		".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+".$ticket[betpoints]."
		WHERE userid='".$vbulletin->userinfo['userid']."'");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_gamble WHERE id=$id");
		if ($vbulletin->options['petz_log']==1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=gamble";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	} else {
		$prize=$ticket[betpoints]*$ticket[betodds];
		$prize=$prize-(($prize/100)*$vbulletin->options['petz_taxbet']);
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
		".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+$prize
		WHERE userid='".$vbulletin->userinfo['userid']."'");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_gamble WHERE id=$id");
		if ($vbulletin->options['petz_log']==1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=gamble";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif($id>0) {
	$op="buy";
	$pid=petz_ui("pid","r","TYPE_INT");
	$amount=petz_ui("amount","r","TYPE_INT");
	if(($pid>0) AND ($amount>0)){
		if ($amount>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
			show_alert($vbphrase['petz_not_enough_points']);
		} else {
			$pet = $vbulletin->db->query_first("SELECT pet.id AS id, battle.title AS title, battle.maxpetz AS maxpetz
			FROM " . TABLE_PREFIX . "petz_petz AS pet
			LEFT JOIN ".TABLE_PREFIX."petz_battle AS battle ON (pet.battle = battle.id)
			WHERE pet.battle=$id AND pet.id=$pid");
			if($pet['id']<1){
				show_alert($vbphrase['petz_not_pet']);
			} else {
				$petz = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_petz
				WHERE battle=$id AND ownerid=".$vbulletin->userinfo['userid']."");
				if ($petz[id]>0) {
					show_alert($vbphrase['petz_no_battles']);
				} else {
					if($vbulletin->options['petz_betodds']==1){
						$nextodd=2;
						$petso = $vbulletin->db->query_read("SELECT id FROM
						" . TABLE_PREFIX . "petz_petz WHERE battle=$id ORDER BY level DESC");
						$totalpets=$vbulletin->db->num_rows($petso);
						if  ($totalpets!=$pet['maxpetz']) {
							show_alert($vbphrase['petz_battle_too_progressed']);
						}
						while ($peto = $vbulletin->db->fetch_array($petso)){
							$peto['odds']=$nextodd;
							if ($nextodd==64){
								$nextodd=75;
							} elseif ($nextodd>70){
								$nextodd=100;
							} else {
								$nextodd=$nextodd*2;
							}
							if($peto['id']==$pid){
								$odds=$peto['odds'];
							}
						}
					} else {
						$odds=2;
					}
					$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_gamble
					(id,petid,bid,battle,betpoints,betodds,userid,won)
					VALUES('',$pid,$id,'".$pet['title']."',$amount,$odds,".$vbulletin->userinfo['userid'].",0)");
					$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
					".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-$amount
					WHERE userid='".$vbulletin->userinfo['userid']."'");
					if ($vbulletin->options['petz_log']==1) {
						petz_log($op,$id,$do,$extra);
					}
					$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=gamble";
					eval(print_standard_redirect('petz_redirect'));
					exit;
				}
			}
		}
	} else {
		$nextodd=2;
		$pets = $vbulletin->db->query_read("SELECT id,name,level FROM
		" . TABLE_PREFIX . "petz_petz WHERE battle=$id ORDER BY level DESC");
		while ($pet = $vbulletin->db->fetch_array($pets)){
			$pet['name'] = stripslashes($pet['name']);
			if($vbulletin->options['petz_betodds']==1){
				$pet['odds']=$nextodd;
				if ($nextodd==64){
					$nextodd=75;
				} elseif ($nextodd>70){
					$nextodd=100;
				} else {
					$nextodd=$nextodd*2;
				}
			} else {
				$pet['odds']=2;
			}
			$option.="<option value=\"$pet[id]\">$pet[name] ($pet[odds]:1) </option>\n";
		}
		if($option==""){
			show_alert($vbphrase['petz_no_bets_pets']);
		}
	}
} else {
	$ticketq = $vbulletin->db->query_read("SELECT ticket.*, pet.name AS pet FROM ".TABLE_PREFIX."petz_gamble AS ticket
	LEFT JOIN ".TABLE_PREFIX."petz_petz AS pet ON (ticket.petid = pet.id)
	WHERE ticket.userid = '".$vbulletin->userinfo['userid']."'");
	$totaltickets=$vbulletin->db->num_rows($ticketq);
	if ($totaltickets!=0) {
		while ($ticket=$vbulletin->db->fetch_array($ticketq)) {
			$ticket[pet]=stripslashes($ticket[pet]);
			$ticket[battle]=stripslashes($ticket[battle]);
			if ($ticket[won]>0) { $ticket[won]=1; }
			if ((!isset($bgcolor)) OR ($bgcolor=="alt2")) {
				$bgcolor = "alt1";
			} else {
				$bgcolor = "alt2";
			}
			eval('$petz_ticket_bit .= "' . fetch_template('petz_gamble_ticket') . '";');
		}
	}
}
eval('$petzbody = "' . fetch_template('petz_gamble') . '";');
// Cleanup
unset($ticket, $battle, $pets);
?>