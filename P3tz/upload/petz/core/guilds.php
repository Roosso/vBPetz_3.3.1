<?php
/*******************************************\
*	P3tz [vb]		  	File: guilds.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
$pid = petz_ui("pid","r","TYPE_INT");
if (($op == "join") AND ($pid < 1)) {
	$petq = $vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo[userid]."' AND guildid=0");
	$peti = $vbulletin->db->num_rows($petq);
	if ($peti == 0) {
		show_alert($vbphrase['petz_no_pets']);
	} else {
		while ($pet = $vbulletin->db->fetch_array($petq)) {
			$pet['name'] = stripslashes($pet['name']);
			$option .= "<option value=\"".$pet['id']."\">".$pet['name']."</option>\n";
		}
	}
} elseif ($op == "join") {
	$pet = $vbulletin->db->query_first("SELECT id, ownerid, guildid, guildreq FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$pid."'");
	$guild = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_guild WHERE id='".$id."'");
	if ($pet['id'] < 1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($guild['id'] < 1) {
		show_alert($vbphrase['petz_no_guild']);
	} elseif ($pet['guildid'] > 0) {
		show_alert($vbphrase['petz_already_in_guild']);
	} elseif ($vbulletin->userinfo['userid'] != $pet['ownerid']) {
		show_alert($vbphrase['petz_not_your_pet']);
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET guildreq='".$guild['id']."' WHERE id='".$pet['id']."'");
		if ($vbulletin->options['petz_log'] == 1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=guilds&id=$id";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif ($op == "update" AND ($id > 0)) {
	$guildq = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_guild WHERE userid='".$vbulletin->userinfo['userid']."'");
	if ($guildq['id'] != $id) {
		show_alert($vbphrase['petz_no_guild']);
	} else {
		$gname = petz_ui("name","p","TYPE_NOHTML");
		$gdcrp = petz_ui("dcrp","p","TYPE_NOHTML");
		$glogo = petz_ui("logo","p","TYPE_STR");
		$gname = $vbulletin->db->escape_string($gname);
		$gdcrp = $vbulletin->db->escape_string($gdcrp);
		if ($gname == '') {
			show_alert($vbphrase['petz_no_name']);
		} elseif ($gdcrp == '') {
			show_alert($vbphrase['petz_no_description']);
		} elseif ($glogo == '') {
			show_alert($vbphrase['petz_logo_notfound']);
		} else {
			$glogo = $vbulletin->db->escape_string($glogo);
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guild SET name='".$gname."', description='".$gdcrp."', image='".$glogo."' WHERE id='".$guildq[id]."'");
			if ($vbulletin->options['petz_log']==1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=guilds&id=$guildq[id]";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	}
} elseif ($op == "delete" AND ($id>0)) {
	$guildq = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_guild WHERE userid='".$vbulletin->userinfo['userid']."'");
	if ($guildq['id'] != $id) {
		show_alert($vbphrase['petz_no_guild']);
	} else {
		$petzq = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE guildid='".$guildq['id']."'");
		if ($petzq['id'] > 0) {
			show_alert($vbphrase['petz_remove_before_delete_guild']);
		} else {
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_guild WHERE id='".$guildq['id']."'");
			if ($vbulletin->options['petz_log'] == 1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=guilds";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	}
} elseif ($op == "remove" AND ($id > 0) AND ($pid > 0)) {
	$pet = $vbulletin->db->query_first("SELECT id, guildid, ownerid FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$pid."'");
	$guild = $vbulletin->db->query_first("SELECT id, userid FROM " . TABLE_PREFIX . "petz_guild WHERE id='".$id."'");
	if ($pet['id'] < 1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($guild['id'] < 1) {
		show_alert($vbphrase['petz_no_guild']);
	} elseif ($guild['id'] != $pet['guildid']) {
		show_alert($vbphrase['petz_not_in_this_guild']);
	} elseif (($pet['ownerid'] != $vbulletin->userinfo['userid']) AND ($guild['userid'] != $vbulletin->userinfo['userid'])) {
		show_alert($vbphrase['petz_not_your_pet']);
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET guildid='0', guildreq='0' WHERE id='".$pet['id']."'");
		if ($pet['ownerid'] != $vbulletin->userinfo['userid']) {
			petz_message($pet['ownerid'],8);
		}
		if ($vbulletin->options['petz_log'] == 1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=guilds&id=$id";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif ($op == "accept" AND ($id > 0) AND ($pid > 0)) {
	$pet = $vbulletin->db->query_first("SELECT id, guildid, guildreq, ownerid FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$pid." AND battle=0'");
	$guild = $vbulletin->db->query_first("SELECT id, userid FROM " . TABLE_PREFIX . "petz_guild WHERE id='".$id."'");
	if ($pet['id'] < 1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($guild['id'] < 1) {
		show_alert($vbphrase['petz_no_guild']);
	} elseif ($pet['guildid'] > 0) {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET guildreq='0' WHERE id='".$pet['id']."'");
		show_alert($vbphrase['petz_already_in_guild']);
	} elseif ($vbulletin->userinfo['userid'] != $guild['userid']) {
		show_alert($vbphrase['petz_you_are_a_spoon']);
	} elseif ($guild['id'] != $pet['guildreq']) {
		show_alert($vbphrase['petz_you_are_a_spoon']);
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET guildid='".$guild['id']."', guildreq='0' WHERE id='".$pet['id']."'");
		petz_message($pet['ownerid'],7);
		if ($vbulletin->options['petz_log'] == 1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=guilds&id=$id";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif ($op == "noaccept") {
	$pet = $vbulletin->db->query_first("SELECT id, ownerid, guildid, guildreq FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$pid."'");
	$guild = $vbulletin->db->query_first("SELECT id, userid FROM " . TABLE_PREFIX . "petz_guild WHERE id='".$id."'");
	if ($pet['id'] < 1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif ($guild['id'] < 1) {
		show_alert($vbphrase['petz_no_guild']);
	} elseif ($vbulletin->userinfo['userid'] != $guild['userid']) {
		show_alert($vbphrase['petz_you_are_a_spoon']);
	} elseif ($guild['id'] != $pet['guildreq']) {
		show_alert($vbphrase['petz_you_are_a_spoon']);
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET guildreq='0' WHERE id='".$pet['id']."'");
		petz_message($pet['ownerid'],8);
		if ($vbulletin->options['petz_log'] == 1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=guilds&id=$id";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif (($op == "create") AND ($id < 1)) {
	$guildq = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_guild WHERE userid='".$vbulletin->userinfo['userid']."'");
	if ($guildq['id'] > 0) {
		show_alert($vbphrase['petz_guild_youhaveone']);
	} elseif ($vbulletin->options['petz_guild']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_guild_cantafford']);
	}
	$images = opendir("petz/images/guilds/");
	while ($logo = readdir($images)) {
		if(preg_match("/.gif/",$logo)) {
			if($logo != '.' || $logo != '..') {
				$logo=preg_replace("/.gif/", "", $logo);
				$options .= "<option value='".$logo."'>".$logo."</option>\n";
			}
		}
	}
} elseif ($op == "create") {
	$guildq = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_guild WHERE userid='".$vbulletin->userinfo['userid']."'");
	if ($guildq['id'] > 0) {
		show_alert($vbphrase['petz_guild_youhaveone']);
	} elseif ($vbulletin->options['petz_guild']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_guild_cantafford']);
	} else {
		$gname = petz_ui("name","p","TYPE_NOHTML");
		$gdcrp = petz_ui("dcrp","p","TYPE_NOHTML");
		$glogo = petz_ui("logo","p","TYPE_STR");
		$gname = $vbulletin->db->escape_string($gname);
		$gdcrp = $vbulletin->db->escape_string($gdcrp);
		if ($gname == '') {
			show_alert($vbphrase['petz_no_name']);
		} elseif ($gdcrp == '') {
			show_alert($vbphrase['petz_no_description']);
		} elseif ($glogo == '') {
			show_alert($vbphrase['petz_logo_notfound']);
		} else {
			$glogo=$vbulletin->db->escape_string($glogo);
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$vbulletin->options['petz_guild']." WHERE userid='".$vbulletin->userinfo['userid']."'");
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_guild (id, name, description, image, userid, wins, battles) VALUES('','$gname','$gdcrp','$glogo','".$vbulletin->userinfo['userid']."','0','0')");
			if ($vbulletin->options['petz_log'] == 1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=guilds";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	}
} elseif ($id < 1) {
	$guilds = $vbulletin->db->query_read("SELECT id, name, wins, battles FROM " . TABLE_PREFIX . "petz_guild");
	$totalguilds = $vbulletin->db->num_rows($guilds);
	if ($totalguilds!=0) {
		while ($guild=$vbulletin->db->fetch_array($guilds)) {
			$guild['name'] = stripslashes($guild['name']);
			$guild['losses'] = $guild['battles'] - $guild['wins'];
			if ((!isset($bgcolor)) OR ($bgcolor == "alt2")) {
				$bgcolor = "alt1";
			} else {
				$bgcolor = "alt2";
			}
			eval('$petz_guild_bit .= "' . fetch_template('petz_guild_bit') . '";');
		}
	} else {
		eval('$petz_guild_bit .= "' . fetch_template('petz_guild_bit') . '";');
	}
	if ($vbulletin->options['petz_guild']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		$createguild = 0;
	} else {
		$createq = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_guild WHERE userid='".$vbulletin->userinfo['userid']."'");
		if ($createq['id'] < 1) {
			$createguild = 1;
		} else {
			$createguild = 0;
		}
	}
} else {
	$guild = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_guild WHERE id='".$id."'");
	if ($guild['id'] < 1) {
		show_alert($vbphrase['petz_no_guild']);
	} else {
		$guild['name'] = stripslashes($guild['name']);
		$guild['description'] = stripslashes($guild['description']);
		$guild['losses'] = $guild['battles']-$guild['wins'];
		$guild['image'] = stripslashes($guild['image']);
		if ($guild['userid'] == $vbulletin->userinfo['userid']) {
			$guild['options'] = 1;
		} else {
			$guild['options'] = 0;
		}
		$pets = $vbulletin->db->query_read("
		SELECT pet.id AS id, pet.type AS type, pet.name AS name, pet.dob AS dob, pet.level AS level, pet.ownerid AS ownerid, user.username As owner, pet.wins AS wins, pet.losses AS losses
		FROM ".TABLE_PREFIX."petz_petz AS pet
		LEFT JOIN ".TABLE_PREFIX."user AS user ON (pet.ownerid = user.userid)
		WHERE guildid = '".$guild['id']."'
		ORDER BY pet.wins DESC");
		$guild['members'] = $vbulletin->db->num_rows($pets);
		if ($guild['members'] != 0) {
			while ($pet=$vbulletin->db->fetch_array($pets)) {
				if (($pet['ownerid'] == $vbulletin->userinfo['userid']) OR ($guild['userid'] == $vbulletin->userinfo['userid'])) {
					$pet['options'] = "<a href=\"petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=guilds&amp;op=remove&amp;id=$guild[id]&amp;pid=$pet[id]\">".$vbphrase['petz_remove']."</a>";
				} else {
					$pet['options']="&nbsp;";
				}
				$pet['name'] = stripslashes($pet['name']);
				$pet['owner'] = stripslashes($pet['owner']);
				if ($pet['wins'] < 1) { $pet['wins'] = $vbphrase['petz_not_any']; }
				if ($pet['losses'] < 1) { $pet['losses'] = $vbphrase['petz_not_any']; }
				$pet['age'] = intval((TIMENOW - $pet['dob']) / 86400);
				$pet['type'] = ucfirst($pet['type']);
				if ((!isset($bgcolor)) OR ($bgcolor == "alt1")) {
					$bgcolor = "alt2";
				} else {
					$bgcolor = "alt1";
				}
				$nonefound = 0;
				eval('$petz_guild_bit .= "' . fetch_template('petz_pet_bit') . '";');
			}
		} else {
			$nonefound = 1;
			eval('$petz_guild_bit = "' . fetch_template('petz_pet_bit') . '";');
		}
		unset($pet);
		unset($pets);
		unset($bgcolor);
		if ($guild['userid'] == $vbulletin->userinfo['userid']) {
			$images = opendir("petz/images/guilds/");
			while ($logo = readdir($images)) {
				if(preg_match("/.gif/",$logo)) {
					if($logo != '.' || $icon  != '..') {
						$logo = preg_replace("/.gif/", "", $logo);
						if ($logo == $guild[image]) {
							$selected = "selected";
						} else {
							unset($selected);
						}
						$options .= "<option value='".$logo."' ".$selected.">".$logo."</option>\n";
					}
				}
			}
			$pets = $vbulletin->db->query_read("
			SELECT pet.id AS id, pet.type AS type, pet.name AS name, pet.dob AS dob, pet.level AS level, pet.ownerid AS ownerid, user.username As owner, pet.wins AS wins, pet.losses AS losses
			FROM ".TABLE_PREFIX."petz_petz AS pet
			LEFT JOIN ".TABLE_PREFIX."user AS user ON (pet.ownerid = user.userid)
			WHERE guildreq = '".$guild['id']."'
			ORDER BY pet.wins DESC");
			$guild['requests'] = $vbulletin->db->num_rows($pets);
			if ($guild['requests'] != 0) {
				while ($pet=$vbulletin->db->fetch_array($pets)) {
					$pet['options'] = "<a href=\"petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=guilds&amp;op=accept&amp;id=".$guild['id']."&amp;pid=".$pet['id']."\">".$vbphrase['petz_accept']."</a> 
								/ <a href=\"petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=guilds&amp;op=noaccept&amp;id=".$guild['id']."&amp;pid=".$pet['id']."\">".$vbphrase['petz_noaccept']."</a>";
					$pet['name'] = stripslashes($pet['name']);
					$pet['owner'] = stripslashes($pet['owner']);
					if ($pet['wins'] < 1) { $pet['wins'] = $vbphrase['petz_not_any']; }
					if ($pet[losses]<1) { $pet['losses']=$vbphrase['petz_not_any']; }
					$pet['age'] = intval((TIMENOW - $pet['dob']) / 86400);
					$pet['type'] = ucfirst($pet['type']);
					if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
						$bgcolor = "alt2";
					} else {
						$bgcolor = "alt1";
					}
					$nonefound = 0;
					eval('$petz_join_bit .= "' . fetch_template('petz_pet_bit') . '";');
				}
			} else {
				$nonefound = 1;
				eval('$petz_join_bit = "' . fetch_template('petz_pet_bit') . '";');
			}
		}
	}
}
eval('$petzbody = "' . fetch_template('petz_guild') . '";');
// Cleanup
unset($guild, $guilds, $pet, $pets);
?>