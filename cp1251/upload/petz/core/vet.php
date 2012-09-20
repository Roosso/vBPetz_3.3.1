<?php
/*******************************************\
*	P3tz [vb]		  	File: vet.php		*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if (($op=="terminate") AND ($id<1)) {
	if ($vbulletin->options['petz_putdown']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_item_cantafford']);
	} else {
		$petq = $vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz WHERE dead=0 AND battle=0 AND ownerid='".$vbulletin->userinfo['userid']."'");
		$peti=$vbulletin->db->num_rows($petq);
		if ($peti==0) {
			show_alert($vbphrase['petz_no_pets']);
		} else {
			while ($pet=$vbulletin->db->fetch_array($petq)) {
				$pet[name]=stripslashes($pet[name]);
				$option.="<option value=\"$pet[id]\">$pet[name]</option>\n";
			}
			eval('$petzbody = "' . fetch_template('petz_vet') . '";');
		}
	}
} elseif ($op=="terminate") {
	$pet = $vbulletin->db->query_first("SELECT id, ownerid, dead, care, battle FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$id."'");
	if ($pet[id]<1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($vbulletin->options['petz_putdown']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_item_cantafford']);
	} else {
		if ($pet[dead]==1) {
			show_alert($vbphrase['petz_is_dead']);
		} elseif ($pet[care]>0) {
			show_alert($vbphrase['petz_in_care']);
		} elseif ($pet[battle]>0) {
			show_alert($vbphrase['petz_in_battle']);
		} elseif ($vbulletin->userinfo['userid']!=$pet[ownerid]) {
			show_alert($vbphrase['petz_item_you_cant_use']);
		} else {
			$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$vbulletin->options['petz_putdown']." WHERE userid='".$vbulletin->userinfo['userid']."'");
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET health='0', dead='1' WHERE id='".$pet[id]."'");
			if ($vbulletin->options['petz_log']==1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=vet";
			eval(print_standard_redirect('petz_redirect'));
			exit;	
		}
	}
} elseif (($op=="create") AND ($id<1)) {
	if ($petgroup['create']==0) {
		print_no_permission();
		exit;
	} else {
		$petq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo['userid']."'");
		$peti=$vbulletin->db->num_rows($petq);
		if ($peti>=$vbulletin->options['petz_maxpetz']) {
			show_alert($vbphrase['petz_pet_toomany']);
		} else {
			$defq = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "petz_default WHERE type!='egg' ");
			while ($pet=$vbulletin->db->fetch_array($defq)) {
				$pet['description']=stripslashes($pet[description]);
				$pet['name']=ucfirst($pet[type]);
				$pet['status']="Ok";
				$pet['moral']=0;
				$pet['Rpcv']=0;
				$pet['Gpcv']=0;
				$pet['Bpcv']=0;
				$pet['level']=1;
				$pet['age']=1;
				$pet['hunger']=65;
				$pet['phealth']=65;
				$pet['gender']="Male";
				$pet['choose']=0;
				if ($pet[cost]<=$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) { $pet[choose]=1; }
				eval('$choose_bit .= "' . fetch_template('petz_create') . '";');
				if ($i==4) {
					$i=0;
				} else {
					$i++;
				}
			}
			eval('$petzbody = "' . fetch_template('petz_vet') . '";');
		}
	}
} elseif ($op=="create") {
	if ($petgroup['create']==0) {
		print_no_permission();
		exit;
	} else {
		$petq = $vbulletin->db->query("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo['userid']."'");
		$peti=$vbulletin->db->num_rows($petq);
		if ($peti>=$vbulletin->options['petz_maxpetz']) {
			show_alert($vbphrase['petz_pet_toomany']);
		} else {
			$pet = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_default WHERE id=$id");
			if ($pet[cost]>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
				show_alert($vbphrase['petz_pet_cantafford']);
			} else {
				$name=petz_ui("name","p","TYPE_NOHTML");
				$gender=petz_ui("gender","p","TYPE_NOHTML");
				$hex=petz_ui("color","p","TYPE_NOHTML");
				$name=$vbulletin->db->escape_string($name);
				$gender=$vbulletin->db->escape_string($gender);
				if ($name=="") {
					show_alert($vbphrase['petz_no_name']);
				} elseif ($hex=="") {
					show_alert($vbphrase['petz_no_color']);
				} else {
					$pcv[r]=hexdec(substr($hex,0,2));
					$pcv[g]=hexdec(substr($hex,2,2));
					$pcv[b]=hexdec(substr($hex,4,2));
					$color="$pcv[r]-$pcv[g]-$pcv[b]";
					$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$pet[cost]." WHERE userid='".$vbulletin->userinfo['userid']."'");
					$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_petz (id, type, name, ownerid, gender, color, moral, dob, guildreq, guildid, wins, losses, mother, father, hunger, health, mhealth, strength, defence, agility, experience, level, battle, care, dead) VALUES('','$pet[type]','$name','".$vbulletin->userinfo['userid']."','$gender','$color','0','". TIMENOW ."','0','0','0','0','0','0','0','$pet[health]','$pet[health]','$pet[strength]','$pet[defence]','$pet[agility]','0','1','0','0','0')");
					if ($vbulletin->options['petz_log']==1) {
						petz_log($op,$id,$do,$extra);
					}
					$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=home";
					eval(print_standard_redirect('petz_redirect'));
					exit;
				}
			}
		}
	}
} elseif (($op>0) AND ($id<1)) {
	$petq = $vbulletin->db->query_read("SELECT id, name, health, mhealth FROM " . TABLE_PREFIX . "petz_petz WHERE dead=0 AND battle=0 AND ownerid='".$vbulletin->userinfo['userid']."'");
	$peti=$vbulletin->db->num_rows($petq);
	if ($peti==0) {
		show_alert($vbphrase['petz_no_pets']);
	} else {
		while ($pet=$vbulletin->db->fetch_array($petq)) {
			$pet['name']=stripslashes($pet['name']);
			$option.="<option value=\"".$pet['id']."\">".$pet['name']." (".$pet['health']."/".$pet['mhealth'].")</option>\n";
		}
		$item = $vbulletin->db->query_first("SELECT id, name, description, cost FROM " . TABLE_PREFIX . "petz_heal WHERE id='".$op."'");
		if ($item['id'] < 1) {
			show_alert($vbphrase['petz_item_none']);
		} else {
			$item['name']=stripslashes($item['name']);
			$item['description']=stripslashes($item['description']);
			eval('$petzbody = "' . fetch_template('petz_vet') . '";');
		}
	}
} elseif ($op>0) {
	$pet = $vbulletin->db->query_first("SELECT id, ownerid, health, mhealth, moral, dead, care, battle FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$id."'");
	if ($pet['id']<1) {
		show_alert($vbphrase['petz_not_pet']);
	} else {
		if ($pet['dead']==1) {
			show_alert($vbphrase['petz_is_dead']);
		} elseif ($pet['care']>0) {
			show_alert($vbphrase['petz_in_care']);
		} elseif ($pet['battle']>0) {
			show_alert($vbphrase['petz_in_battle']);
		} elseif ($vbulletin->userinfo['userid']!=$pet[ownerid]) {
			show_alert($vbphrase['petz_item_you_cant_use']);
		} else {
			$item = $vbulletin->db->query_first("SELECT id, cost, health, moral, stock FROM " . TABLE_PREFIX . "petz_heal WHERE id='".$op."'");
			if ($item['cost']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
				show_alert($vbphrase['petz_item_cantafford']);
			} elseif (($item['stock']<1) AND ($vbulletin->options['petz_stock']==1)) {
				show_alert($vbphrase['petz_item_nostock']);
			} else {
				if ($vbulletin->options['petz_stock']==1) {
					$item[stock]=$item[stock]-1;
				}
				$pet[health]=$pet[health]+$item[health];
				if ($pet[health]<1) { $pet[health]=0; }
				if ($pet[health]>$pet[mhealth]) { $pet[health]=$pet[mhealth]; }
				$pet[moral]=$pet[moral]+$item[moral];
				if ($pet[moral]<-99) { $pet[moral]=-100; }
				if ($pet[moral]>99) { $pet[moral]=100; }
				$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$item[cost]." WHERE userid='".$vbulletin->userinfo['userid']."'");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET health='".$pet[health]."', moral='".$pet[moral]."' WHERE id='".$pet[id]."'");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_heal SET stock='".$item[stock]."' WHERE id='".$item[id]."'");
				if ($vbulletin->options['petz_log']==1) {
					petz_log($op,$id,$do,$extra);
				}
				$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=vet";
				eval(print_standard_redirect('petz_redirect'));
				exit;
			}	
		}
	}
} else {
	$vetq = $vbulletin->db->query_read("SELECT * FROM ".TABLE_PREFIX."petz_heal ORDER BY cost ASC");
	$veti=$vbulletin->db->num_rows($vetq);
	if ($veti!=0) {
		$noitems=0;
		while ($item=$vbulletin->db->fetch_array($vetq)) {
			if ($item[health]!=0) { $item[effect].="".$vbphrase['petz_health'].": $item[health]\n"; }
			if (($item[effect]!="") AND ($item[moral]!=0)) { $item[effect].="<br>\n"; }
			if ($item[moral]!=0) { $item[effect].="".$vbphrase['petz_moral'].": $item[moral]"; }
			$item[name]=stripslashes($item[name]);
			$item[description]=stripslashes($item[description]);
			$item[canuse]=0;
			$item[canbuy]=0;
			if ($vbulletin->options['petz_stock']==1) {
				$item[hasstock]=1;
			} else {
				$item[hasstock]=0;
			}
			if (($item[stock]>0) OR ($vbulletin->options['petz_stock']==0)) {
				if ($item[cost]<=$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
					$item[vetuse]=1;
					$item[cantbuy]=0;
				} else {
					$item[vetuse]=0;
					$item[cantbuy]=1;
				}
			} else {
				$item[vetuse]=0;
				$item[cantbuy]=1;
			}
			$item[cansell]=0;
			$item[cansteal]=0;
			if ((!isset($bgcolor)) OR ($bgcolor=="alt2")) {
				$bgcolor = "alt1";
			} else {
				$bgcolor = "alt2";
			}
			eval('$petz_vet_bit .= "' . fetch_template('petz_item') . '";');
		}
	} else {
		$noitems=1;
		eval('$petz_vet_bit .= "' . fetch_template('petz_item') . '";');
	}
	
	eval('$petzbody = "' . fetch_template('petz_vet') . '";');
}
// Cleanup
unset($vetq, $item, $petq, $pet, $defq);
?>