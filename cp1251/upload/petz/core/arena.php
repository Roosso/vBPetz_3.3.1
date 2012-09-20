<?php
/*******************************************\
*	P3tz [vb]		  	File: arena.php		*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
$pid = petz_ui("pid","r","TYPE_INT");

if (($op == "create") AND ($id == 1)) {
	$title = petz_ui("title","p","TYPE_NOHTML");
	$background = petz_ui("background","p","TYPE_STR");
	$maxpetz = petz_ui("maxpetz","p","TYPE_INT");
	if ($petgroup['own'] == 0) {
		print_no_permission();
		exit;
	} elseif ($title == "") {
		show_alert($vbphrase['petz_no_title']);
	} elseif ($maxpetz == "") {
		show_alert($vbphrase['petz_no_contenders']);
	} elseif ($maxpetz > $vbulletin->options['petz_maxbrawl']) {
		show_alert($vbphrase['petz_too_many_contenders']);
	} else {
		if ($maxpetz < 3) { $type = 1; $maxpetz = 2; } else { $type = 2; }
		$title = $vbulletin->db->escape_string($title);
		$background = $vbulletin->db->escape_string($background);
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_battle
		(id, title, background, type, maxpetz, round, created) VALUES
		('','".$title."','".$background."','".$type."','".$maxpetz."','0','".TIMENOW."')");
		if ($vbulletin->options['petz_battleforum'] > 0){
			$battleid = $db->insert_id();
			// INSERT THREAD
			require_once('./includes/class_dm.php');
			require_once('./includes/class_dm_threadpost.php');
			$threaddm = new vB_DataManager_Thread_FirstPost($vbulletin, ERRTYPE_STANDARD);
			$pagetext = $vbphrase['battle_thread_start'];
			$pagetext .= "[url=".$vbulletin->options['bburl']."/petz.php?do=arena&id=$battleid]".
			$vbphrase['petz_goto_battle']."[/url]";
			$num = 1;
			$threaddm->do_set('forumid', $vbulletin->options['petz_battleforum']);
			$threaddm->do_set('postuserid', $vbulletin->userinfo['userid']);
			$threaddm->do_set('userid', $vbulletin->userinfo['userid']);
			$threaddm->do_set('username', $vbulletin->userinfo['username']);
			$threaddm->do_set('pagetext', $pagetext);
			$threaddm->do_set('title', $vbphrase['petz_battle_thread_title']);
			$threaddm->do_set('allowsmilie', $num);
			$threaddm->do_set('visible', $num);
			$threaddm->save();
			$extra="Inserted a thread!";
		}
		if ($vbulletin->options['petz_log'] == 1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif ($op == "create") {
	if ($petgroup['own'] == 0) {
		print_no_permission();
		exit;
	} else {
		$images = opendir("petz/images/battlegrounds/");
		while ($image = readdir($images)) {
			if(preg_match("/.jpg/",$image)) {
				if($image != '.' || $image  != '..') {
					$image = preg_replace("/.jpg/", "", $image);
					$options .= "<option value='".$image."'>".$image."</option>\n";
				}
			}
		}
	}
} elseif (($op == "join") AND ($pid > 0)) {
	if ($petgroup['own'] == 0) {
		print_no_permission();
		exit;
	} elseif($id < 1) {
		show_alert($vbphrase['petz_no_battle']);
	} elseif ($vbulletin->options['petz_entrybrawl']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_entry_cantafford']);
	} else {
		$battle = $vbulletin->db->query_first("SELECT id, type, maxpetz, round FROM " . TABLE_PREFIX . "petz_battle WHERE id='".$id."'");
		if ($battle['id'] < 1) {
			show_alert($vbphrase['petz_no_battle']);
		} elseif ($battle['round'] != 0) {
			show_alert($vbphrase['petz_battle_started']);
		} elseif ($battle['type'] == 3) {
			show_alert($vbphrase['petz_guild_war_no_join']);
		} elseif ($battle['type'] == 4) {
			show_alert($vbphrase['petz_no_battle']);
		} else {
			$petq = $vbulletin->db->query_read("SELECT id, level FROM " . TABLE_PREFIX . "petz_petz WHERE battle='".$battle['id']."'");
			$totalpetz = $vbulletin->db->num_rows($petq);
			if ($totalpetz < $battle['maxpetz']) {
				while ($check = $vbulletin->db->fetch_array($petq)) {
					$level = $check['level'];
				}
				$minimumage=TIMENOW-$vbulletin->options['petz_bbdate'];
				$pet=$vbulletin->db->query_first("SELECT id, battle, care, dead, dob, ownerid, battle, level FROM " . TABLE_PREFIX . "petz_petz
				WHERE id='".$pid."'");
				if ($pet[id]<1) {
					show_alert($vbphrase['petz_no_pet']);
				} elseif ($pet['ownerid'] != $vbulletin->userinfo['userid']) {
					show_alert($vbphrase['petz_not_your_pet']);
				} elseif ($pet['care'] > 0) {
					show_alert($vbphrase['petz_in_care']);
				} elseif ($pet['dead'] == 1) {
					show_alert($vbphrase['petz_is_dead']);
				} elseif ($pet['battle'] > 0) {
					show_alert($vbphrase['petz_in_battle']);
				} elseif ($pet['dob'] > $minimumage) {
					show_alert($vbphrase['petz_too_young']);
				} elseif ((($pet['level'] - $level) > ($vbulletin->options['petz_battlelevel'])) AND ($totalpetz > 0)) {
					show_alert($vbphrase['petz_levels_too_different']);
				} elseif ((($level - $pet['level']) > ($vbulletin->options['petz_battlelevel'])) AND ($totalpetz > 0)) {
					show_alert($vbphrase['petz_levels_too_different']);
				} else {
					$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".
					$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$vbulletin->options['petz_entrybrawl']."
					WHERE userid='".$vbulletin->userinfo['userid']."'");
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz
					SET battle='".$battle[id]."', round=0 WHERE id='".$pet['id']."'");
					if($totalpetz+1 == $battle['maxpetz']) {
						$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_battle
						SET round=1 WHERE id='".$battle['id']."'");
					}
					if ($vbulletin->options['petz_log'] == 1) {
						petz_log($op,$id,$do,$extra);
					}
					$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena&id=$id";
					eval(print_standard_redirect('petz_redirect'));
					exit;
				}
			} else {
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_battle SET round=1 WHERE id='".$battle[id]."'");
				if ($vbulletin->options['petz_log']==1) {
					petz_log($op,$id,$do,$extra);
				}
				show_alert($vbphrase['petz_battle_started']);
			}
		}
	}
} elseif ($op == "join") {
	if ($petgroup['own'] == 0) {
		print_no_permission();
		exit;
	} elseif($id < 1) {
		show_alert($vbphrase['petz_no_battle']);
	} elseif ($vbulletin->options['petz_entrybrawl']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_entry_cantafford']);
	} else {
		$battle = $vbulletin->db->query_first("SELECT id, type, maxpetz, round FROM " . TABLE_PREFIX . "petz_battle WHERE id='".$id."'");
		if ($battle['id'] < 1) {
			show_alert($vbphrase['petz_no_battle']);
		} elseif ($battle['round'] != 0) {
			show_alert($vbphrase['petz_battle_started']);
		} elseif ($battle['type'] == 3) {
			show_alert($vbphrase['petz_guild_war_no_join']);
		} elseif ($battle['type'] == 4) {
			show_alert($vbphrase['petz_no_battle']);
		} else {
			$petq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE battle='".$battle['id']."'");
			$totalpetz = $vbulletin->db->num_rows($petq);
			if ($totalpetz<$battle['maxpetz']) {
				$minimumage=TIMENOW-$vbulletin->options['petz_bbdate'];
				$petq = $vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz
				WHERE dead=0 AND type!='egg' AND care=0 AND battle=0 AND dob<$minimumage AND ownerid='".$vbulletin->userinfo['userid']."'");
				$peti = $vbulletin->db->num_rows($petq);
				if ($peti == 0) {
					show_alert($vbphrase['petz_no_battle_pet']);
				} else {
					while ($pet=$vbulletin->db->fetch_array($petq)) {
						$pet['name'] = stripslashes($pet['name']);
						$option.="<option value=\"".$pet['id']."\">".$pet['name']."</option>\n";
					}
				}
			} else {
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_battle SET round=1 WHERE id='".$battle['id']."'");
				if ($vbulletin->options['petz_log'] == 1) {
					petz_log($op,$id,$do,$extra);
				}
				show_alert($vbphrase['petz_battle_started']);
			}
		}
	}
} elseif ($id>0) {
	$battle = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_battle WHERE type!=4 AND id=".$id."");
	if ($battle['id']<1) {
		show_alert($vbphrase['petz_no_battle']);
	} elseif ($battle['round'] != 0) {
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=battle&id=$id";
		eval(print_standard_redirect('petz_petz'));
		exit;
	} else {
		$battle['title'] = stripslashes($battle['title']);
		if ($battle['type'] == 1) {
			$nolink = 0;
			$battle['type'] = $vbphrase['petz_battle_oneonone'];
		} elseif ($battle['type'] == 2) {
			$nolink = 0;
			$battle['type'] = $vbphrase['petz_battle_freeforall'];
		} elseif ($battle['type']==3) {
			$nolink = 1;
			$battle['type'] = $vbphrase['petz_battle_guildwar'];
		}
		$battle['created'] = vbdate($vbulletin->options['dateformat'],$battle['created']);
		$petcount = 0;
		$pets = $vbulletin->db->query_read("SELECT id,type,name,ownerid,gender,color,moral,dob,hunger,health,mhealth,level FROM
		" . TABLE_PREFIX . "petz_petz WHERE battle=". $battle['id'] ."");
		while ($pet = $vbulletin->db->fetch_array($pets)){
			$pet['name'] = stripslashes($pet['name']);
			$pet['age'] = intval((TIMENOW-$pet['dob']) / 86400);
			$pcv=explode("-", $pet['color']);
			$pet['Rpcv'] = $pcv[0];
			$pet['Gpcv'] = $pcv[1];
			$pet['Bpcv'] = $pcv[2];
			if ($pet['moral'] < -50) {
				$pet['moral'] = "Evil";
			} elseif ($pet['moral'] > 50) {
				$pet['moral'] = "Good";
			} else {
				$pet['moral'] = "Neutral";
			}
			if ($pet['health']!=0) {
				$health = ($pet['health']/$pet['mhealth'])*100;
				$pet['health'] = ($health/100)*65;
				$pet['health'] = round($pet['health'], 0);
			}
			if ($pet['health'] < 1) {
			 	$pet['health'] = 0;
			} else {
				$pet['health'] = $pet['health'];
			}
			if ($health<20) {
				$pet['status'] = "Injured";
			} elseif ($health<50) {
				$pet['status'] = "Ill";
			} elseif ($pet['hunger']>50) {
				$pet['status'] = "Hungry";
			} else {
				$pet['status'] = "Ok";
			}
			if ($pet['hunger'] < 1) {
				$pet['hunger'] = 0;
			} else {
				$pet['hunger'] = 65/(100/$pet['hunger']);
				$pet['hunger'] = round($pet['hunger'], 0);
			}
			if ($pet['ownerid']==$vbulletin->userinfo['userid']){
				$pet['exitarena']=1;
			} else {
				$pet['exitarena']=0;
			}
			eval('$petz_petz_bit .= "' . fetch_template('petz_view') . '";');
			$petcount++;
		}
		while($petcount<$battle[maxpetz]){
			if($nolink==1){
				$petz_petz_bit.="<a href=\"petz.php?
				".$vbulletin->session->vars['sessionurl']."do=guildwar&amp;op=join&amp;id=$battle[id]\">
				<img src=\"petz/images/misc/arenaslot.png\" width=\"120\" height=\"150\" border=\"0\" alt=\"".
				$vbphrase['petz_battle_slot']."\" /></a>";
			} else {
				$petz_petz_bit.="<a href=\"petz.php?".$vbulletin->session->vars['sessionurl']."do=arena&amp;op=join&amp;id=$battle[id]\">
				<img src=\"petz/images/misc/arenaslot.png\" width=\"120\" height=\"150\" border=\"0\" alt=\"".
				$vbphrase['petz_battle_slot']."\" /></a>";
			}
			$petcount++;
		}
	}
} else {
	$battles = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "petz_battle WHERE type!=4 ORDER by created DESC");
	$totalbattles=$vbulletin->db->num_rows($battles);
	$perpage = 5;
	$pagenumber = petz_ui("page","r","TYPE_INT");
	if (empty($pagenumber)) { $pagenumber = 1; }
	$startingfrom = ($pagenumber*$perpage)-$perpage;
	$upperlimit = $startingfrom + $perpage;
	$ib=0;
	$ic=0;
	if ($totalbattles!=0) {
		while ($battle=$vbulletin->db->fetch_array($battles)) {
			$battle['title']=stripslashes($battle['title']);
			if ($battle['type']==1) {
				$battle['typetext']=$vbphrase['petz_battle_oneonone'];
			} elseif ($battle['type']==2) {
				$battle['typetext']=$vbphrase['petz_battle_freeforall'];
			} elseif ($battle['type']==3) {
				$battle['typetext']=$vbphrase['petz_battle_guildwar'];
			}
			$battle['created']=vbdate($vbulletin->options['dateformat'],$battle['created']);
			if ($battle['round']==0) {
				if ((!isset($bgalt1)) OR ($bgalt1=="alt1")) {
					$bgcolor = "alt2";
				} else {
					$bgcolor = "alt1";
					$bgalt1="alt1";
				}
				$nonefound=0;
				if (($ic >= $startingfrom) AND ($ic<$upperlimit)) {
					eval('$petz_challenge_bit .= "' . fetch_template('petz_arena_bit') . '";');
				}
				$ic++;
			} else {
				if ((!isset($bgalt2)) OR ($bgalt2=="alt1")) {
					$bgcolor = "alt2";
				} else {
					$bgcolor = "alt1";
					$bgalt2="alt1";
				}
				$nonefound=0;
				if (($ib >= $startingfrom) AND ($ib<$upperlimit)) {
					eval('$petz_battle_bit .= "' . fetch_template('petz_arena_bit') . '";');
				}
				$ib++;
			}
		}
		$ic++;
		$ib++;
		if ($ic>$ib) { $i=$ic; } else { $i=$ib; }
		$pagenav = construct_page_nav($pagenumber,$perpage,$i-1, "petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=arena");
	} else {
		$nonefound=1;
		eval('$petz_challenge_bit = "' . fetch_template('petz_arena_bit') . '";');
		eval('$petz_battle_bit = "' . fetch_template('petz_arena_bit') . '";');
	}
}
eval('$petzbody = "' . fetch_template('petz_arena') . '";');
// Cleanup
unset($battle, $petq, $pet);
?>