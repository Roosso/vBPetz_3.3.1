<?php
/*******************************************\
*	P3tz [vb]		  	File: training.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
$pid=petz_ui("pid","r","TYPE_INT");
$level=petz_ui("level","r","TYPE_INT");
if (($op=="create") AND ($id>0) AND ($pid>0) AND ($level>0)) {
	if ($petgroup['own']==0) {
		print_no_permission();
		exit;
	} elseif ($vbulletin->options['petz_traincost']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_train_cantafford']);
	} else {
		$pet=$vbulletin->db->query_first("SELECT id, battle, care, dead, ownerid, battle, level FROM " . TABLE_PREFIX . "petz_petz
		WHERE id=".$pid." AND type!='egg'");
		if ($pet[id]<1) {
			show_alert($vbphrase['petz_no_pets']);
		} elseif ($pet['ownerid']!=$vbulletin->userinfo['userid']) {
			show_alert($vbphrase['petz_not_your_pet']);
		} elseif ($pet['care']>0) {
			show_alert($vbphrase['petz_in_care']);
		} elseif ($pet['dead']==1) {
			show_alert($vbphrase['petz_is_dead']);
		} elseif ($pet['battle']>0) {
			show_alert($vbphrase['petz_in_battle']);
		} elseif (($pet['level']-$level)>($vbulletin->options['petz_battlelevel'])) {
			show_alert($vbphrase['petz_levels_too_different']);
		} elseif (($level-$pet['level'])>($vbulletin->options['petz_battlelevel'])) {
			show_alert($vbphrase['petz_levels_too_different']);
		} else {
			$def=$vbulletin->db->query_first("SELECT type, strength, defence, agility, health FROM " . TABLE_PREFIX . "petz_default
			WHERE id=".$id." AND type!='egg'");
			if ($def['type']==""){
				show_alert($vbphrase['petz_invalid_hollogram']);
			} else {
				if($level>1){ $levelm=$level-1; }
				if(($levelm>0) AND ($vbulletin->options['petz_levelup']>0)){
					$def['strength']=round($def['strength']+($def['strength']/100*$vbulletin->options['petz_levelup']*$levelm),0);
					$def['defence']=round($def['defence']+($def['defence']/100*$vbulletin->options['petz_levelup']*$levelm),0);
					$def['agility']=round($def['agility']+($def['agility']/100*$vbulletin->options['petz_levelup']*$levelm),0);
					$def['health']=round($def['health']+($def['health']/100*$vbulletin->options['petz_levelup']*$levelm),0);	
				}
				$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".
				$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$vbulletin->options['petz_traincost']."
				WHERE userid='".$vbulletin->userinfo['userid']."'");
				$battle['title']="Тренировка";
				$battle['title']=$vbulletin->db->escape_string($battle[title]);
				$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_battle
				(id, title, background, type, maxpetz, round, created) VALUES
				('','".$battle['title']."','Arena',4,2,1,".TIMENOW.")");
				$bid=$vbulletin->db->insert_id();
				$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_training
				(id,type,health,mhealth,strength,defence,agility,level,battle,dead) VALUES
				('','$def[type]',$def[health],$def[health],$def[strength],$def[defence],$def[agility],$level,$bid,0)");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET battle=$bid, round=0 WHERE id=$pid");
				if ($vbulletin->options['petz_log']==1) {
					petz_log($op,$id,$do,$extra);
				}
				$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=battle&id=$bid";
				eval(print_standard_redirect('petz_redirect'));
				exit;
			}
		}
	}
} else {
	$battles = $vbulletin->db->query_read("SELECT battle.*, pet.name AS pet, pet.id AS petid
	FROM " . TABLE_PREFIX . "petz_battle AS battle
	LEFT JOIN ".TABLE_PREFIX."petz_petz AS pet ON (pet.battle = battle.id)
	WHERE battle.type=4 ORDER by battle.created DESC");
	$totalbattles=$vbulletin->db->num_rows($battles);
	$perpage = 10;
	$pagenumber=petz_ui("page","r","TYPE_INT");
	if (empty($pagenumber)) { $pagenumber = 1; }
	$startingfrom = ($pagenumber*$perpage)-$perpage;
	$upperlimit = $startingfrom + $perpage;
	$i=0;
	if ($totalbattles!=0) {
		while ($battle=$vbulletin->db->fetch_array($battles)) {
			if($battle['pet']!="") {
				$battle['title']=stripslashes($battle['title']);
				$pnme=stripslashes($battle['pet']);
				$battle['type']=4;
				$battle['text']="<a href=\"petz.php?".$vbulletin->session->vars['sessionurl']."do=view&id=".$battle['petid']."\">$pnme</a>";
				$battle['created']=vbdate($vbulletin->options['dateformat'],$battle['created']);
				if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
					$bgcolor = "alt2";
				} else {
					$bgcolor = "alt1";
				}
				$nonefound=0;
				if (($i >= $startingfrom) AND ($i<$upperlimit)) {
					eval('$petz_training_bit .= "' . fetch_template('petz_arena_bit') . '";');
				}
				$i++;
			} else {
				$totalbattles--;
			}
		}
		$i++;
		$pagenav = construct_page_nav($pagenumber,$perpage,$i, "petz.php?".  $vbulletin->session->vars['sessionurl'] ."do=training");
	} else {
		$nonefound=1;
		eval('$petz_training_bit = "' . fetch_template('petz_arena_bit') . '";');
	}
	$highlevel=1;
	$lowlevel=100;
	$pets=$vbulletin->db->query_read("SELECT id, name, level, health, mhealth FROM " . TABLE_PREFIX . "petz_petz
	WHERE dead=0 AND type!='egg' AND care=0 AND battle=0 AND ownerid='".$vbulletin->userinfo['userid']."'");
	while ($pet=$vbulletin->db->fetch_array($pets)) {
		$pet['name']=stripslashes($pet['name']);
		$petoptions.="<option value=\"".$pet['id']."\">Ур.".$pet['level']." - ".$pet['name']." (".$pet['health']."/".$pet['mhealth'].")</option>\n";
		if ($pet['level']>$highlevel) { $highlevel=$pet['level']; }
		if ($pet['level']<$lowlevel) { $lowlevel=$pet['level']; }
	}
	if ($petoptions) {
		$level=$lowlevel-$vbulletin->options['petz_battlelevel'];
		if ($level<1) { $level=1; }
		$hlevel=$highlevel+$vbulletin->options['petz_battlelevel']+1;
		if ($hlevel>101) { $hlevel=101; }
		while ($level<$hlevel) {
			$lvloptions.="<option value=\"{$level}\"> {$level} </option>\n";
			$level++;
		}
		$defs = $vbulletin->db->query_read("SELECT id,type FROM " . TABLE_PREFIX . "petz_default WHERE type!='egg' ");
		while ($def=$vbulletin->db->fetch_array($defs)) {
			$def[type]=ucfirst(stripslashes($def['type']));
			$defoptions.="<option value=\"$def[id]\">$def[type]</option>\n";
		}
	}
	eval('$petzbody = "' . fetch_template('petz_training') . '";');
}
// Cleanup
unset($battle, $pets, $pet, $defs, $def);
?>