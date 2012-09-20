<?php
/*******************************************\
*	P3tz [vb]		  	File: use.php		*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
$v=petz_ui("v","r","TYPE_NOHTML");
if (($id < 1) OR ($op < 1)) {
	show_alert($vbphrase['petz_no_pet']);
} else {
	$pet = $vbulletin->db->query_first("SELECT id, type, ownerid, hunger, moral, health, mhealth, dead, care, battle FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$op."'");
	if ($pet['id'] < 1) {
		show_alert($vbphrase['petz_no_pet']);
	} else {
		if ($pet['dead'] == 1) {
			show_alert($vbphrase['petz_is_dead']);
		} elseif ($pet['care'] > 0) {
			show_alert($vbphrase['petz_in_care']);
		} elseif ($pet['battle'] > 0) {
			show_alert($vbphrase['petz_in_battle']);
		} elseif (($vbulletin->options['petz_anypetitem'] == 0) AND ($vbulletin->userinfo['userid'] != $pet['ownerid'])) {
			show_alert($vbphrase['petz_item_you_cant_use']);
		} else {
			$iitem = $vbulletin->db->query_first("SELECT i.id AS id, i.itemid AS itemid, a.id AS aid, a.type AS atype
			FROM " . TABLE_PREFIX . "petz_inventory AS i
			LEFT JOIN ".TABLE_PREFIX."petz_auction AS a ON (i.id = a.article)
			WHERE i.id='".$id."' AND i.userid='".$vbulletin->userinfo['userid']."'");
			if ($iitem['id'] < 1) {
				show_alert($vbphrase['petz_item_none']);
			} elseif (($iitem['aid'] > 0) AND ($iitem['atype'] == 2)) {
				show_alert($vbphrase['petz_auction_exists']);
			} else {
				$item = $vbulletin->db->query_first("SELECT id, name, description, hunger, moral, health, special, pettype FROM " . TABLE_PREFIX . "petz_items WHERE id='".$iitem[itemid]."'");
				if  (($item['pettype'] != $pet['type']) AND ($item['pettype'] != '')) {
					show_alert($vbphrase['petz_item_not_pettype']);
				} elseif (($item['special'] > 0) AND ($v == "")) {
					$item['name'] = stripslashes($item['name']);
					$item['description'] = stripslashes($item['description']);
					eval('$petzbody = "' . fetch_template('petz_inventory_use') . '";');
				} else {
					if ($item['special'] == 1) {
						$squery="name='".$vbulletin->db->escape_string($v)."',";
					} elseif ($item['special'] == 2) {
						$hex = $v;
						if ($hex == '') {
							show_alert($vbphrase['petz_no_color']);
						} else {
							$pcv['r'] = hexdec(substr($hex,0,2));
							$pcv['g'] = hexdec(substr($hex,2,2));
							$pcv['b'] = hexdec(substr($hex,4,2));
							$color = $pcv['r']."-".$pcv['g']."-".$pcv['b'];
							$squery = "color='".$color."',";
						}
					} elseif ($item['special'] == 3) {
						if (rand(0,100) <= $vbulletin->options['petz_killchance']) {
							$squery = "health='0', dead='1',";
							$kill = "killed";
						} else {
							$kill = "failed";
						}
					}
					//change moral
					$pet['moral'] += $item['moral'];
					if ($pet['moral'] < -99) { $pet['moral']=-100; }
					if ($pet['moral'] > 99)  { $pet['moral']=100; }
					//change hunger
					$pet['hunger'] += $item['hunger'];
					if ($pet['hunger'] < 1)  { $pet['hunger'] = 0; }
					if ($pet['hunger'] > 99) { $pet['hunger'] = 100; }
					//change health
					$pet['health'] += $item['health'];
					if ($pet['health'] < 1) { $pet['health'] = 0; }
					if ($pet['health'] >= $pet['mhealth']) { $pet['health'] = $pet['mhealth']; }
					
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET $squery hunger='".$pet['hunger']."', moral='".$pet['moral']."', health='".$pet['health']."' WHERE id='".$pet['id']."'");
					$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_inventory WHERE id='".$iitem['id']."'");
					if ($vbulletin->options['petz_log']==1) {
						petz_log($op,$id,$do,$extra);
					}
					if ($kill=="killed") {
						if ($pet['ownerid']!=$vbulletin->userinfo['userid']) {
							petz_message($pet['ownerid'],5);
						}
						$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=view&id=$op";
						eval(print_standard_redirect('petz_redirect'));
						exit;
					} elseif ($kill=="failed") {
						if ($pet['ownerid']!=$vbulletin->userinfo['userid']) {
							petz_message($pet['ownerid'],5);
						}
						$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=view&id=$op";
						eval(print_standard_redirect('petz_redirect'));
						exit;
					} else {
						$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=view&id=$op";
						eval(print_standard_redirect('petz_redirect'));
						exit;
					}
				}
			}
		}
	}
}
// Cleanup
unset($pet, $iitem, $item);
?>