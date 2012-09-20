<?php
/*******************************************\
*	P3tz [vb]		  	File: adoption.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if (($op == "sell") AND ($id < 1)) {
	$petq = $vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo['userid']."'");
	$peti = $vbulletin->db->num_rows($petq);
	if ($peti == 0) {
		show_alert($vbphrase['petz_no_pets']);
	} else {
		while ($pet=$vbulletin->db->fetch_array($petq)) {
			$pet['name'] = stripslashes($pet['name']);
			$option .= "<option value=\"".$pet['id']."\">".$pet['name']."</option>\n";
		}
	}
} elseif ($op=="sell") {
	$pet=$vbulletin->db->query_first("SELECT petz.id as id, petz.ownerid as ownerid, adopt.id as adopt
	FROM " . TABLE_PREFIX . "petz_petz AS petz
	LEFT JOIN ".TABLE_PREFIX."petz_adopt AS adopt ON (petz.id = adopt.petid)
	WHERE petz.id='".$id."'");
	$cost=petz_ui("cost","p","TYPE_INT");
	if ($pet['id'] < 1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($cost < 1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($pet['adopt'] > 0) {
		show_alert($vbphrase['petz_being_sold']);
	} elseif ($vbulletin->userinfo['userid'] != $pet['ownerid']) {
		show_alert($vbphrase['petz_not_your_pet']);
	} else {
		$description = petz_ui("description","p","TYPE_NOHTML");
		$description = $vbulletin->db->escape_string($description);
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_adopt (id,userid,petid,cost,description,upfor)
		VALUES('',".$vbulletin->userinfo['userid'].",".$pet[id].",'".$cost."','".$description."','".TIMENOW."')");
		if ($vbulletin->options['petz_log'] == 1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=adoption&id=$id";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif ($op == "remove" AND ($id > 0)) {
	$pet = $vbulletin->db->query_first("SELECT petz.id as id, petz.ownerid as ownerid, adopt.id as adopt
	FROM " . TABLE_PREFIX . "petz_petz AS petz
	LEFT JOIN ".TABLE_PREFIX."petz_adopt AS adopt ON (petz.id = adopt.petid)
	WHERE petz.id='".$id."'");
	if ($pet['id'] != $id) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($vbulletin->userinfo['userid'] != $pet['ownerid']) {
		show_alert($vbphrase['petz_not_your_pet']);
	} elseif ($pet['adopt'] < 1) {
		show_alert($vbphrase['petz_no_adopt_pet']);
	} else {
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_adopt WHERE id='".$pet['adopt']."'");
		if ($vbulletin->options['petz_log'] == 1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=adoption";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif ($op == "buy" AND ($id > 0)) {
	$pet=$vbulletin->db->query_first("SELECT petz.id as id, petz.ownerid as ownerid, adopt.id as adopt, adopt.cost as cost, owner.username as owner
	FROM " . TABLE_PREFIX . "petz_adopt AS adopt
	LEFT JOIN ".TABLE_PREFIX."petz_petz AS petz ON (petz.id = adopt.petid)
	LEFT JOIN ".TABLE_PREFIX."user AS owner ON (adopt.userid = owner.userid)
	WHERE adopt.id='".$id."'");
	if ($petgroup['own'] == 0) {
		print_no_permission();
		exit;
	} elseif ($pet['id'] < 1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($vbulletin->userinfo['userid'] == $pet['ownerid']) {
		show_alert($vbphrase['petz_adopt_your_pet']);
	} elseif ($pet['adopt'] < 1) {
		show_alert($vbphrase['petz_no_adopt_pet']);
	} else {
		if ($pet['cost']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
			show_alert($vbphrase['petz_not_enough_points']);
		} else {
			$petq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND
			ownerid='".$vbulletin->userinfo['userid']."'");
			$peti = $vbulletin->db->num_rows($petq);
			if ($peti >= $vbulletin->options['petz_maxpetz']) {
				show_alert($vbphrase['petz_pet_toomany']);
			} else {
				$payment = round($pet['cost'] - ($pet['cost']/100*$vbulletin->options['petz_taxadopt']),0);
				if($payment < 1){ $payment = 1; }
				$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$pet['cost']." WHERE userid='".$vbulletin->userinfo['userid']."'");
				$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+$payment WHERE userid='".$pet['ownerid']."'");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET ownerid='".$vbulletin->userinfo['userid']."' WHERE id='".$pet['id']."'");
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_adopt WHERE id='".$pet['adopt']."'");
				petz_message($pet['ownerid'],6);
				if ($vbulletin->options['petz_log'] == 1) {
					petz_log($op,$id,$do,$extra);
				}
				$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=adoption";
				eval(print_standard_redirect('petz_redirect'));
				exit;
			}
		}
	}
} else {
	if ($id > 0) {
		$xcon = "WHERE petz.id='$id'";
	}
	if ($id == "mine") {
		$xcon = "WHERE petz.ownerid='".$vbulletin->userinfo['userid']."'";
	}
	$pets = $vbulletin->db->query_read("SELECT petz.id AS id, petz.type AS type, petz.name AS name, petz.ownerid AS ownerid, petz.color AS color, petz.moral AS moral, petz.health AS health, petz.mhealth AS mhealth, petz.hunger AS hunger, petz.dob AS dob, petz.strength AS strength, petz.defence AS defence, petz.agility AS agility, petz.level AS level, petz.care AS care, petz.dead AS dead, owner.username AS owner, adopt.id AS adopt, adopt.cost AS cost, adopt.description AS description, adopt.upfor AS upfor
		FROM " . TABLE_PREFIX . "petz_adopt AS adopt
		LEFT JOIN ".TABLE_PREFIX."user AS owner ON (adopt.userid = owner.userid)
		LEFT JOIN ".TABLE_PREFIX."petz_petz AS petz ON (petz.id = adopt.petid)
		$xcon
	");
	$adoptresults = $vbulletin->db->num_rows($pets);
	$perpage = 10;
	$pagenumber = petz_ui("page","r","TYPE_INT");
	if (empty($pagenumber)) { $pagenumber = 1; }
	$startingfrom = ($pagenumber*$perpage)-$perpage;
	$upperlimit = $startingfrom + $perpage;
	$i=0;
	if ($adoptresults != 0) {
		while ($pet = $vbulletin->db->fetch_array($pets)) {
			$pet['name'] = stripslashes($pet['name']);
			$pet['owner'] = stripslashes($pet['owner']);
			$pet['description'] = stripslashes($pet['description']);
			$pcv = explode("-", $pet['color']);
			$pet['Rpcv'] = $pcv[0];
			$pet['Gpcv'] = $pcv[1];
			$pet['Bpcv'] = $pcv[2];
			$pet['cost'] = round($pet['cost'],2);
			if ($pet['dead'] == 1) {
				$pet['age'] = "Dead";
			} else {
				$pet['age'] = intval((TIMENOW-$pet['dob']) / 86400);
			}
			if ($pet['ownerid'] == $vbulletin->userinfo['userid']) {
				$pet['options'] = "<a href=\"petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=adoption&amp;op=remove&amp;id=".$pet['id']."\">".$vbphrase['petz_remove']."</a>";
			} elseif ($petgroup['own'] == 1) {
				$pet['options'] = "<a href=\"petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=adoption&amp;op=buy&amp;id=".$pet['adopt']."\">".$vbphrase['petz_buy']." ($pet[cost])</a>";
			} else {
				$pet['options'] = "&nbsp;";
			}
			if ($pet['health'] != 0) {
				$health=($pet['health']/$pet['mhealth'])*100;
				$pet['health'] = ($health/100)*65;
				$pet['health'] = round($pet['health'], 0);
			}
			if ($pet['health'] < 1) {
			 	$pet['health'] = 0;
			} else {
				$pet['health'] = $pet['health'];
			}
			if ($pet['hunger'] == 0) {
				$pet['hunger'] = 0;
			} else {
				$pet['hunger'] = 65/(100/$pet['hunger']);
				$pet['hunger'] = round($pet['hunger'], 0);
			}
			if ($health < 20) {
				$pet['status'] = "Injured";
			} elseif ($health < 50) {
				$pet['status'] = "Ill";
			} elseif ($pet['hunger'] > 50) {
				$pet['status'] = "Hungry";
			} else {
				$pet['status'] = "Ok";
			}
			if ($pet['moral'] < -50) {
				$pet['moral'] = "Evil";
			} elseif ($pet['moral'] > 50) {
				$pet['moral'] = "Good";
			} else {
				$pet['moral'] = "Neutral";
			}
			$pet['since'] = vbdate($vbulletin->options['dateformat'], $pet['upfor']);
			if ((!isset($bgcolor)) OR ($bgcolor == "alt1")) {
				$bgcolor = "alt2";
			} else {
				$bgcolor = "alt1";
			}
			$nonefound = 0;
			if (($i >= $startingfrom) AND ($i < $upperlimit)) {
				eval('$petz_adopt_bit .= "' . fetch_template('petz_adopt_bit') . '";');
			}
			$i++;
		}
		$i++;
		$pagenav = construct_page_nav($pagenumber,$perpage,$i, "petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=adoption");
	} else {
		$nonefound = 1;
		eval('$petz_adopt_bit = "' . fetch_template('petz_adopt_bit') . '";');
	}
}
eval('$petzbody = "' . fetch_template('petz_adopt') . '";');
// Cleanup
unset($pet, $pets);
?>