<?php
/*******************************************\
*	P3tz [vb]		  	File: guildwar.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
$pid=petz_ui("pid","r","TYPE_INT");
$gid=petz_ui("gid","r","TYPE_INT");
if (($op=="create") AND ($id==1)) {
	$maxpetz=petz_ui("maxpetz","p","TYPE_INT");
	$title=petz_ui("title","p","TYPE_NOHTML");
	$background=petz_ui("background","p","TYPE_NOHTML");
	if ($petgroup['own']==0) {
		print_no_permission();
		exit;
	} elseif ($title=="") {
		show_alert($vbphrase['petz_no_title']);
	} elseif ($maxpetz>$vbulletin->options['petz_maxbrawl']) {
		show_alert($vbphrase['petz_too_many_contenders']);
	} else {
		$type=3;
		$title=$vbulletin->db->escape_string($title);	
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_battle
		(id, title, background, type, maxpetz, round, created) VALUES
		('','".$title."','".$background."','".$type."','".$maxpetz."','0','".TIMENOW."')");
		$bid=$vbulletin->db->insert_id();
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_guildwar (id,guilda,guildb) VALUES ('$bid','','')");
		if ($vbulletin->options['petz_log']==1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena&id=$bid";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif ($op=="create") {
	$images = opendir("petz/images/battlegrounds/");
	while ($image = readdir($images)) {
		if(preg_match("/.jpg/",$image)) {
			if($image != '.' || $image  != '..') {
				$image=preg_replace("/.jpg/", "", $image);
				$bgoptions .= "<option value='".$image."'>".$image."</option>\n";
			}
		}
	}
	$peeps=4;
	while ($peeps<=$vbulletin->options['petz_maxbrawl']){
		$petoptions .= "<option value='".$peeps."'>".$peeps."</option>\n";
		$peeps=$peeps+2;
	}
} elseif (($op=="join") AND ($pid>0)) {
	if ($petgroup['own']==0) {
		print_no_permission();
		exit;
	} elseif($pid<1) {
		show_alert($vbphrase['petz_not_pet']);
	} elseif($id<1) {
		show_alert($vbphrase['petz_no_battle']);
	} elseif($gid<1) {
		show_alert($vbphrase['petz_youhavenoguild']);
	} elseif ($vbulletin->options['petz_entrybrawl']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_entry_cantafford']);
	} else {
		$battle=$vbulletin->db->query_first("SELECT battle.id AS id, battle.maxpetz AS maxpetz, war.guilda AS guilda, war.guildb AS guildb
		FROM " . TABLE_PREFIX . "petz_battle AS battle
		LEFT JOIN ".TABLE_PREFIX."petz_guildwar AS war ON (battle.id = war.id)
		WHERE battle.id=".$id." AND battle.round=0");
		if ($battle[id]<1) {
			show_alert($vbphrase['petz_no_battle']);
		} else {
			$petq=$vbulletin->db->query_read("SELECT id, guildid FROM " . TABLE_PREFIX . "petz_petz WHERE battle='".$battle[id]."'");
			$totalpetz=$vbulletin->db->num_rows($petq);
			if ($totalpetz<$battle[maxpetz]) {
				$gpetcount=0;
				while ($petc=$vbulletin->db->fetch_array($petq)) {
					if($petc[guildid]==$gid){
						$gpetcount++;
					}
				}	
				if($battle[guilda]==$gid){
					$guildok=1;
				} elseif($battle[guildb]==$gid){
					$guildok=1;
				} elseif($battle[guilda]<1){
					$guildok=1;
				} elseif($battle[guildb]<1){
					$guildok=1;
				} else {
					$guildok=0;
				}
				if($guildok==0){
					show_alert($vbphrase['petz_guildwar_nojoin']);
				} elseif ($gpetcount>=$battle[maxpetz]/2){
					show_alert($vbphrase['petz_guildwar_toomany']);
				} else {
					$guild=$vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_guild
					WHERE id='".$gid."' AND userid=".$vbulletin->userinfo['userid']."");
					if ($guild[id]<1){
						show_alert($vbphrase['petz_notyourguild']);
					} else {
						$pet=$vbulletin->db->query_first("SELECT id, battle, care, guildid, dead, dob, battle
						FROM " . TABLE_PREFIX . "petz_petz
						WHERE id='".$pid."'");
						$minimumage=TIMENOW-$vbulletin->options['petz_bbdate'];
						if ($pet[id]<1) {
							show_alert($vbphrase['petz_no_pet']);
						} elseif ($pet[guildid]!=$gid) {
							show_alert($vbphrase['petz_not_your_guild_pet']);
						} elseif ($pet[care]>0) {
							show_alert($vbphrase['petz_in_care']);
						} elseif ($pet[dead]==1) {
							show_alert($vbphrase['petz_is_dead']);
						} elseif ($pet[battle]>0) {
							show_alert($vbphrase['petz_in_battle']);
						} elseif ($pet[dob]>$minimumage) {
							show_alert($vbphrase['petz_too_young']);
						} else {
							$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".
							$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".
							$vbulletin->options['petz_entrybrawl']." WHERE userid='".$vbulletin->userinfo['userid']."'");
							$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz
							SET battle='".$battle[id]."', round=0 WHERE id='".$pet[id]."'");
							if(($battle[guilda]<1) AND ($gid!=$battle[guildb])){
								$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guildwar
								SET guilda=$gid WHERE id='".$battle[id]."'");
							} elseif(($battle[guildb]<1) AND ($gid!=$battle[guilda])){
								$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guildwar
								SET guildb=$gid WHERE id='".$battle[id]."'");
							}
							if($totalpetz+1==$battle[maxpetz]) {
								if($battle[guilda]<1) { $battle[guilda]=$gid; }
								if($battle[guildb]<1) { $battle[guildb]=$gid; }
								$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guild
								SET battles=battles+1 WHERE id='".$battle[guilda]."'");
								$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guild
								SET battles=battles+1 WHERE id='".$battle[guildb]."'");
								$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_battle
								SET round=1 WHERE id='".$battle[id]."'");
								$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_guildwar WHERE id=".$battle[id]."");
							}
							if ($vbulletin->options['petz_log']==1) {
								petz_log($op,$id,$do,$extra);
							}
							$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena&id=$id";
							eval(print_standard_redirect('petz_redirect'));
							exit;
						}
					}
				}
			} else {
				if($battle[guilda]<1) { $battle[guilda]=$gid; }
				if($battle[guildb]<1) { $battle[guildb]=$gid; }
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guild
				SET battles=battles+1 WHERE id='".$battle[guilda]."'");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guild
				SET battles=battles+1 WHERE id='".$battle[guildb]."'");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_battle SET round=1 WHERE id='".$battle[id]."'");
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_guildwar WHERE id=".$battle[id]."");
				if ($vbulletin->options['petz_log']==1) {
					petz_log($op,$id,$do,$extra);
				}
				show_alert($vbphrase['petz_battle_started']);
			}
		}
	}
} elseif ($op=="join") {
	$guild=$vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_guild
	WHERE userid=".$vbulletin->userinfo['userid']."");
	$gid=$guild[id];
	if ($petgroup['own']==0) {
		print_no_permission();
		exit;
	} elseif ($gid<1){
		show_alert($vbphrase['petz_youhavenoguild']);
	} elseif($id<1) {
		show_alert($vbphrase['petz_no_battle']);
	} elseif ($vbulletin->options['petz_entrybrawl']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_entry_cantafford']);
	} else {
		$battle=$vbulletin->db->query_first("SELECT battle.id AS id, battle.maxpetz AS maxpetz, war.guilda AS guilda, war.guildb AS guildb
		FROM " . TABLE_PREFIX . "petz_battle AS battle
		LEFT JOIN ".TABLE_PREFIX."petz_guildwar AS war ON (battle.id = war.id)
		WHERE battle.id=".$id." AND battle.round=0");
		if ($battle[id]<1) {
			show_alert($vbphrase['petz_no_battle']);
		} else {
			$petq=$vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE battle='".$id."'");
			$gpetcount=0;
			while ($petc=$vbulletin->db->fetch_array($petq)) {
				if($petc[guildid]==$guild[id]){
					$gpetcount++;
				}
			}
			$totalpetz=$vbulletin->db->num_rows($petq);
			if ($totalpetz<$battle[maxpetz]) {
				if($battle[guilda]==$gid){
					$guildok=1;
				} elseif($battle[guildb]==$gid){
					$guildok=1;
				} elseif($battle[guilda]<1){
					$guildok=1;
				} elseif($battle[guildb]<1){
					$guildok=1;
				} else {
					$guildok=0;
				}
				if($guildok==0){
					show_alert($vbphrase['petz_guildwar_nojoin']);
				} elseif ($gpetcount>=$battle[maxpetz]/2){
					show_alert($vbphrase['petz_guildwar_toomany']);
				} else {
					$minimumage=TIMENOW-$vbulletin->options['petz_bbdate'];
					unset($petq);
					$petq=$vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz
					WHERE dead=0 AND type!='egg' AND care=0 AND battle=0 AND dob<$minimumage AND guildid=$gid");
					$peti=$vbulletin->db->num_rows($petq);
					if ($peti==0) {
						show_alert($vbphrase['petz_no_battle_pet']);
					} else {
						while ($pet=$vbulletin->db->fetch_array($petq)) {
							$pet[name]=stripslashes($pet[name]);
							$option.="<option value=\"$pet[id]\">$pet[name]</option>\n";
						}
					}
				}
			} else {
				if($battle[guilda]<1) { $battle[guilda]=$gid; }
				if($battle[guildb]<1) { $battle[guildb]=$gid; }
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guild
				SET battles=battles+1 WHERE id='".$battle[guilda]."'");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guild
				SET battles=battles+1 WHERE id='".$battle[guildb]."'");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_battle SET round=1 WHERE id='".$battle[id]."'");
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_guildwar WHERE id=".$battle[id]."");
				if ($vbulletin->options['petz_log']==1) {
					petz_log($op,$id,$do,$extra);
				}
				show_alert($vbphrase['petz_battle_started']);
			}
		}
	}
} else {
	$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=guildwar&op=create";
	eval(print_standard_redirect('petz_redirect'));
	exit;
}
eval('$petzbody = "' . fetch_template('petz_guildwar') . '";');
// Cleanup
unset($battle, $petq, $pet, $guild);
?>