<?php
/*******************************************\
*	P3tz [vb]		  	File: spells.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if (($op>0) AND ($id>0)) { // level up spell
	$spell = $vbulletin->db->query_first("SELECT id, cost FROM ".TABLE_PREFIX."petz_spells WHERE id=".$op."");
	if ($spell[id]<1){
		show_alert($vbphrase['petz_no_spell']);
	} elseif ($spell[cost]>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]){
		show_alert($vbphrase['petz_not_enough_points']);
	} else {
		$petq=$vbulletin->db->query_first("SELECT id, level FROM " . TABLE_PREFIX . "petz_petz
		WHERE dead=0 AND ownerid=".$vbulletin->userinfo[userid]." AND battle=0 AND id=".$id."");
		if($petq[id]<1){
			show_alert($vbphrase['petz_not_pet']);
		} else {
			$spellq=$vbulletin->db->query_first("SELECT id, level FROM " . TABLE_PREFIX . "petz_magic
			WHERE pid=".$id." AND sid=".$op."");
			if($spellq[id]>0){
				if($spellq[level]>=100){
					show_alert($vbphrase['petz_level_is_max']);
				} elseif($spellq[level]>=$petq[level]){
					show_alert($vbphrase['petz_not_leveled_up']);
				} else {
					$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
					".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$spell[cost]."
					WHERE userid='".$vbulletin->userinfo['userid']."'");
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_magic SET level=level+1 WHERE id=".$spellq[id]."");
					if ($vbulletin->options['petz_log']==1) {
						petz_log($op,$id,$do,$extra);
					}
					$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=view&id=$id";
					eval(print_standard_redirect('petz_redirect'));
					exit;
				}
			} else {
				$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
				".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$spell[cost]."
				WHERE userid='".$vbulletin->userinfo['userid']."'");
				$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_magic (id,pid,sid,level)
				VALUES('',".$id.",".$op.",1)");
				if ($vbulletin->options['petz_log']==1) {
					petz_log($op,$id,$do,$extra);
				}
				$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=view&id=$id";
				eval(print_standard_redirect('petz_redirect'));
				exit;
			}
		}
	}
} elseif ($op>0) { // choose pet
	$spell = $vbulletin->db->query_first("SELECT * FROM ".TABLE_PREFIX."petz_spells WHERE id=".$op."");
	if ($spell[id]<1){
		show_alert($vbphrase['petz_no_spell']);
	} elseif ($spell[cost]>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]){
		show_alert($vbphrase['petz_not_enough_points']);
	} else {
		$petq=$vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz WHERE dead=0 AND ownerid=".$vbulletin->userinfo[userid]." AND battle=0");
		$peti=$vbulletin->db->num_rows($petq);
		if ($peti==0) {
			show_alert($vbphrase['petz_no_pets']);
		} else {
			while ($pet=$vbulletin->db->fetch_array($petq)) {
				$pet[name]=stripslashes($pet[name]);
				$option.="<option value=\"$pet[id]\">$pet[name]</option>\n";
			}
		}
		eval('$petzbody = "' . fetch_template('petz_spell') . '";');
	}
} else {
	$spellq = $vbulletin->db->query_read("SELECT * FROM ".TABLE_PREFIX."petz_spells ORDER BY cost ASC");
	$spellsi=$vbulletin->db->num_rows($spellq);
	if ($spellsi>0) {
		while ($spell=$vbulletin->db->fetch_array($spellq)) {
			$spell[name]=stripslashes($spell[name]);
			$spell[description]=stripslashes($spell[description]);
			$spell[image]=$spell[element];
			$spell[element]=$vbphrase['petz_'.$spell[element].''];
			if ((!isset($bgcolor)) OR ($bgcolor=="alt2")) {
				$bgcolor = "alt1";
			} else {
				$bgcolor = "alt2";
			}
			if ($spell[cost]<=$vbulletin->userinfo[$vbulletin->options['petz_pfield']]){
				$spell[canuse]=1;
			} else {
				$spell[canuse]=0;
			}			
			eval('$petz_spell_bit .= "' . fetch_template('petz_spell_bit') . '";');
		}
	}
	eval('$petzbody = "' . fetch_template('petz_spell') . '";');
}
// Cleanup
unset($spell, $spellq, $petq);
?>