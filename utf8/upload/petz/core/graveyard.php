<?php
/*******************************************\
*	P3tz [vb]		  	File: graveyard.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($op == "mydead") {
	$petq = $vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz WHERE dead='1' AND ownerid='".$vbulletin->userinfo['userid']."'");
	$peti = $vbulletin->db->num_rows($petq);
	if ($peti == 0) {
		show_alert($vbphrase['petz_no_pets']);
	} else {
		while ($pet = $vbulletin->db->fetch_array($petq)) {
			$pet['name'] = stripslashes($pet['name']);
			$option .= "<option value=\"".$pet['id']."\">".$pet['name']."</option>\n";
		}
	}
} elseif (($op == "buy") AND ($id < 1)) {
	$petq = $vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz WHERE dead='1' AND ownerid='".$vbulletin->userinfo['userid']."'");
	$peti = $vbulletin->db->num_rows($petq);
	if ($peti == 0) {
		show_alert($vbphrase['petz_no_pets']);
	} else {
		while ($pet = $vbulletin->db->fetch_array($petq)) {
			$pet['name'] = stripslashes($pet['name']);
			$option .= "<option value=\"".$pet['id']."\">".$pet['name']."</option>\n";
		}
		$images = opendir("petz/images/gravestones/");
		while ($logo = readdir($images)) {
			if(preg_match("/.gif/",$logo)) {
				if($logo != '.' || $icon  != '..') {
					$logo = preg_replace("/.gif/", "", $logo);
					$grave_options .= "<option value='".$logo."'>".$logo."</option>\n";
				}
			}
		}
	}
} elseif ($op == "buy") {
	$pet = $vbulletin->db->query_first("SELECT petz.id as id, petz.ownerid as ownerid, petz.dead as dead, grave.id as grave
	FROM " . TABLE_PREFIX . "petz_petz AS petz
	LEFT JOIN ".TABLE_PREFIX."petz_grave AS grave ON (petz.id = grave.petid)
	WHERE petz.id='".$id."'");
	if ($pet['id'] < 1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($pet['dead'] != 1) {
		show_alert($vbphrase['petz_not_dead']);
	} elseif ($pet['grave'] > 0) {
		show_alert($vbphrase['petz_has_grave']);
	} elseif ($vbulletin->userinfo['userid'] != $pet['ownerid']) {
		show_alert($vbphrase['petz_not_your_pet']);
	} elseif ($vbulletin->options['petz_taxdeath']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_item_cant_afford']);
	} else {
		$memorial = petz_ui("message","p","TYPE_NOHTML");
		$stone = petz_ui("stone","p","TYPE_STR");
		$memorial = $vbulletin->db->escape_string($memorial);
		$stone = $vbulletin->db->escape_string($stone);
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$vbulletin->options['petz_taxdeath']." WHERE userid='".$vbulletin->userinfo['userid']."'");
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_grave (id,userid,petid,stone,memorial,burried) VALUES('',".$vbulletin->userinfo['userid'].",".$pet['id'].",'".$stone."','".$memorial."','".TIMENOW."')");
		if ($vbulletin->options['petz_log'] == 1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=graveyard&id=$id";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif (($op == "rise") AND ($id > 0)) {
	if ($vbulletin->options['petz_resurrect']==0) {
		show_alert($vbphrase['petz_no_resurrect']);
	} elseif ($petgroup[own]==0) {
		show_alert($vbphrase['petz_cant_own']);
	} else {
		$petq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo['userid']."'");
		$peti=$vbulletin->db->num_rows($petq);
		if ($peti>=$vbulletin->options['petz_maxpetz']) {
			show_alert($vbphrase['petz_pet_toomany']);
		} else {
			$pet=$vbulletin->db->query_first("SELECT petz.id as id, petz.ownerid as ownerid, petz.dead as dead, grave.id as grave
			FROM " . TABLE_PREFIX . "petz_petz AS petz
			LEFT JOIN ".TABLE_PREFIX."petz_grave AS grave ON (petz.id = grave.petid)
			WHERE petz.id='".$id."'");
			if ($pet[id]<1) {
				show_alert($vbphrase['petz_no_pet']);
			} elseif ($pet[dead]!=1) {
				show_alert($vbphrase['petz_not_dead']);
			} elseif ($vbulletin->userinfo['userid']!=$pet[ownerid]) {
				show_alert($vbphrase['petz_not_your_pet']);
			} elseif ($vbulletin->options['petz_resurrect']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
				show_alert($vbphrase['resurrect_no_points']);
			} else {
				$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$vbulletin->options['petz_resurrect']." WHERE userid='".$vbulletin->userinfo['userid']."'");
				if ($pet[grave]>0) {
					$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_grave WHERE id='".$pet[grave]."'");
				}
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET dead=0, health=mhealth, hunger=0 WHERE id='".$pet[id]."'");
				if ($vbulletin->options['petz_log']==1) {
					petz_log($op,$id,$do,$extra);
				}
				$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=view&id=$pet[id]";
				eval(print_standard_redirect('petz_redirect'));
				exit;
			}
		}
	}
} else {
	if ($id>0) {
		$xcon="WHERE petz.id='$id'";
	}
	if ($id=="mine") {
		$xcon="WHERE petz.ownerid='".$vbulletin->userinfo['userid']."'";
	}
	$pets = $vbulletin->db->query_read("SELECT petz.id AS id, petz.dob AS dob, grave.id AS grave, grave.stone AS stone, grave.memorial AS memorial, grave.burried AS burried
		FROM " . TABLE_PREFIX . "petz_grave AS grave
		LEFT JOIN ".TABLE_PREFIX."petz_petz AS petz ON (petz.id = grave.petid)
		$xcon
	");
	$graveresults=$vbulletin->db->num_rows($pets);
	$perpage = 40;
	$pagenumber=petz_ui("page","r","TYPE_INT");
	if (empty($pagenumber)) { $pagenumber = 1; }
	$startingfrom = ($pagenumber*$perpage)-$perpage;
	$upperlimit = $startingfrom + $perpage;
	$i=0;
	if ($graveresults!=0) {
		while ($pet=$vbulletin->db->fetch_array($pets)) {
			$pet['memorial']=stripslashes($pet['memorial']);
			$pet['birth']=vbdate($vbulletin->options['dateformat'], $pet['dob']);
			$pet['death']=vbdate($vbulletin->options['dateformat'], $pet['burried']);
			if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
				$bgcolor = "alt2";
			} else {
				$bgcolor = "alt1";
			}
			$nonefound=0;
			if (($i >= $startingfrom) AND ($i<$upperlimit)) {
				eval('$petz_grave_bit .= "' . fetch_template('petz_grave_bit') . '";');
			}
			$i++;
		}
		$i++;
		$pagenav = construct_page_nav($pagenumber,$perpage,$i, "petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=graveyard");
	} else {
		$nonefound=1;
		eval('$petz_grave_bit .= "' . fetch_template('petz_grave_bit') . '";');
	}
}
eval('$petzbody = "' . fetch_template('petz_graveyard') . '";');
// Cleanup
unset($pet, $pets);
?>