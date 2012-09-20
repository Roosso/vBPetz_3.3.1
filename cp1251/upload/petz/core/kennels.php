<?php
/*******************************************\
*	P3tz [vb]		  	File: kennels.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if (($op=="enter") AND ($id<1)) {
	$petq=$vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND battle=0 AND ownerid='".$vbulletin->userinfo[userid]."'");
	$peti=$vbulletin->db->num_rows($petq);
	if ($peti==0) {
		show_alert($vbphrase['petz_no_pets']);
	} else {
		while ($pet=$vbulletin->db->fetch_array($petq)) {
			$pet[name]=stripslashes($pet[name]);
			$option.="<option value=\"$pet[id]\">$pet[name]</option>\n";
		}
	}
} elseif ($op=="enter") {
	$pet=$vbulletin->db->query_first("SELECT id, ownerid, battle, care, dead FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$id."'");
	if ($pet[id]<1) {
		show_alert($vbphrase['petz_no_pet']);
	} elseif ($pet[care]>0) {
		show_alert($vbphrase['petz_in_care']);
	} elseif ($pet[battle]>0) {
		show_alert($vbphrase['petz_in_battle']);
	} elseif ($pet[dead]==1) {
		show_alert($vbphrase['petz_is_dead']);
	} elseif ($vbulletin->userinfo['userid']!=$pet[ownerid]) {
		show_alert($vbphrase['petz_not_your_pet']);
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET care='".TIMENOW."' WHERE id='".$pet[id]."'");
		if ($vbulletin->options['petz_log']==1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=kennels&id=$id";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif ($op=="remove" AND ($id>0)) {
	$kennelq = $vbulletin->db->query_first("SELECT id, care FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$id."' AND ownerid='".$vbulletin->userinfo['userid']."'");
	if ($kennelq[id]!=$id) {
		show_alert($vbphrase['petz_no_care']);
	} else {
		$petcost=intval((TIMENOW-$kennelq['care']) / 86400);
		$petcost=$petcost+1;
		$petcost=round($petcost*$vbulletin->options['petz_daycare'],2);
		if($petcost<1){ $petcost=1; }
		if ($petcost>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
			show_alert($vbphrase['petz_not_enough_kennel']);
		} else {
			$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-$petcost WHERE userid='".$vbulletin->userinfo['userid']."'");
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET care='' WHERE id='".$kennelq[id]."'");
			if ($vbulletin->options['petz_log']==1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=kennels";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	}
} else {
	if ($id>0) {
		$xcon="AND petz.id='$id'";
	}
	if ($op=="mine") {
		$xcon="AND petz.ownerid='".$vbulletin->userinfo['userid']."'";
	}
	$pets = $vbulletin->db->query_read("SELECT petz.id AS id, petz.type AS type, petz.name AS name, petz.ownerid AS ownerid, petz.dob AS dob, petz.level AS level, petz.care AS care, petz.dead AS dead, owner.username AS owner
		FROM " . TABLE_PREFIX . "petz_petz AS petz
		LEFT JOIN ".TABLE_PREFIX."user AS owner ON (petz.ownerid = owner.userid)
		WHERE petz.care!='0' $xcon
	");
	$kennelresults=$vbulletin->db->num_rows($pets);
	if ($kennelresults!=0) {
		while ($pet=$vbulletin->db->fetch_array($pets)) {
			$pet['name']=stripslashes($pet['name']);
			$pet['owner']=stripslashes($pet['owner']);
			if ($pet['dead']==1) {
				$pet['age']=$vbphrase['petz_dead'];
			} else {
				$pet['age']=intval((TIMENOW-$pet['dob']) / 86400);
			}
			$pet['type']=ucfirst($pet['type']);
			if ($pet['ownerid']==$vbulletin->userinfo['userid']) {
				$pet['cost']=intval((TIMENOW-$pet['care']) / 86400);
				$pet['cost']=$pet['cost']+1;
				$pet['cost']=round($pet['cost']*$vbulletin->options['petz_daycare'],2);
				$pet['options']="<a href=\"petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=kennels&amp;op=remove&amp;id=$pet[id]\">".$vbphrase['petz_remove']." ($pet[cost])</a>";
				} else {
					$pet['options']="&nbsp;";
				}
			if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
				$bgcolor = "alt2";
			} else {
				$bgcolor = "alt1";
			}
			$nonefound=0;
			eval('$petz_kennel_bit .= "' . fetch_template('petz_pet_bit') . '";');
		}
	} else {
		$nonefound=1;
		eval('$petz_kennel_bit = "' . fetch_template('petz_pet_bit') . '";');
	}
}
eval('$petzbody = "' . fetch_template('petz_kennel') . '";');
// Cleanup
unset($pet, $pets);
?>