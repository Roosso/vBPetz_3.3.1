<?php
/*******************************************\
*	P3tz [vb]		  	File: battle.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($op==""){
	$op=petz_ui("naop","p","TYPE_STRING");
	$naoppo=petz_ui("naoppo","p","TYPE_INT");
}
if ($op>0) {
	$sid=$op;
	$op="magic";
}
if ($op!="") {
	require_once("./petz/core/battle_functions.php");
}
if (($op=="viewmove") AND ($id>0)) {
	$lid=petz_ui("lid","r","TYPE_INT");
	if ($lid<1){
		$getlid = $vbulletin->db->query_first("SELECT id FROM ". TABLE_PREFIX ."petz_battlelog WHERE battleid=".$id."
		ORDER BY footprint DESC");
		$lid=$getlid[id];
	}
	if ($lid>0) {
	/*************************************************************************************/
	$move = $vbulletin->db->query_first("SELECT
	pet.id AS petid, pet.type AS pettype, pet.color AS petcolor, pet.battle AS petbattle,
	opp.id AS oppid, opp.type AS opptype, opp.color AS oppcolor, opp.battle AS oppbattle,
	log.damage AS damage, magic.element AS spelltype
	FROM " . TABLE_PREFIX . "petz_battlelog AS log
	LEFT JOIN ".TABLE_PREFIX."petz_petz AS pet ON (pet.id = log.petid)
	LEFT JOIN ".TABLE_PREFIX."petz_petz AS opp ON (opp.id = log.oppid)
	LEFT JOIN ".TABLE_PREFIX."petz_spells AS magic ON (magic.id = log.spell)
	WHERE log.id=".$lid." AND log.battleid=".$id."");
	if($move[petbattle]==$id){
		$atk[id]=$move[petid];
		$atk[type]=$move[pettype];
	} else {
		$atk[id]=0;
	}
	if($move[oppbattle]==$id){
		$def[id]=$move[oppid];
		$def[type]=$move[opptype];
	} else {
		$def[id]=0;
	}
	if($move[spelltype]!=""){
		$move[atype]="cast";
		$move[dtype]="spell";
		if ($move[damage]==0){
			$move[spelltype]="";
		}
	} else {
		$move[atype]="attack";
		if($move[damage]>0){
			$move[dtype]="defend";
		} else {
			$move[dtype]="miss";
		}
	}
	if ($atk[id]>0){
		$apcv=explode("-", $move[petcolor]);
		$atk['Rpcv'] = $apcv[0];
		$atk['Gpcv'] = $apcv[1];
		$atk['Bpcv'] = $apcv[2];
	} else { //bot?
		$atk = $vbulletin->db->query_first("SELECT id,type FROM " . TABLE_PREFIX . "petz_training WHERE battle=".$id."");
		$atk['Rpcv'] = 0;
		$atk['Gpcv'] = 100;
		$atk['Bpcv'] = 0;
	}
	if ($def[id]>0){
		$dpcv=explode("-", $move[oppcolor]);
		$def['Rpcv'] = $dpcv[0];
		$def['Gpcv'] = $dpcv[1];
		$def['Bpcv'] = $dpcv[2];
	} else { //bot?
		$def = $vbulletin->db->query_first("SELECT id,type FROM " . TABLE_PREFIX . "petz_training WHERE battle=".$id."");
		$def['Rpcv'] = 0;
		$def['Gpcv'] = 100;
		$def['Bpcv'] = 0;
	}
	eval('$output = "' . fetch_template('petz_battle_view') . '";');
	/*************************************************************************************/
	}
	echo $output;
	exit;
} elseif (($op=="quit") AND ($id>0) AND ($vbulletin->userinfo['userid']>0)) {
	$pet = $vbulletin->db->query_first("SELECT id, battle FROM " . TABLE_PREFIX . "petz_petz
	WHERE id=$id AND battle>0 AND ownerid=".$vbulletin->userinfo['userid']."");
	if($pet['id']>0){
		$battle=$vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_battle
		WHERE id=".$pet['battle']." AND round=0 AND type!=4");
		if($battle['id']>0){
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET battle=0, round=0 WHERE id=$id");
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena&id=$battle[id]";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		} else {
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	} else {
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif (($op=="end") AND ($id>0) AND ($vbulletin->userinfo['userid']>0)) {
	$pid=petz_ui("pid","r","TYPE_INT");
	if($pid>0){
		$pet = $vbulletin->db->query_first("SELECT id, guildid FROM " . TABLE_PREFIX . "petz_petz
		WHERE id=$pid AND battle=$id AND ownerid=".$vbulletin->userinfo['userid']."");
		$battle = $vbulletin->db->query_first("SELECT type, maxpetz FROM " . TABLE_PREFIX . "petz_battle WHERE id=$id");
		if($battle[type]==3){
			$xcon="AND guildid!=$pet[guildid]";
		} else {
			$xcon="";
		}
		$contender = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_petz
		WHERE id!=$pid AND battle=$id $xcon");
		if($battle[type]<1){
			$endit=0;
		} elseif($pet[id]<1){
			$endit=0;
		} elseif($contender[id]>0){
			$endit=0;
		} else {
			$endit=1;
		}
		if($endit==1){
			if($battle[type]==4){
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET
				battle=0, round=0 WHERE battle=$id");
			} elseif($battle[type]==3){
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET
				battle=0, round=0 WHERE battle=$id");
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guild SET wins=wins+1 WHERE id=$pet[guildid]");
			} else {
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET
				battle=0, round=0, wins=wins+1 WHERE battle=$id");
				petz_gamble($pid,$id);
				$prize=$vbulletin->options['petz_entrybrawl']*2;
				$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
				".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+$prize
				WHERE userid='".$vbulletin->userinfo['userid']."'");
			}
			if ($vbulletin->options['petz_battlelog']==0){
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battle WHERE id=$id");
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battlelog WHERE battleid=$id");
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battlechat WHERE battleid=$id");
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_training WHERE battle=$id");
			}
			if($battle[type]==4){
				$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=training";
				eval(print_standard_redirect('petz_redirect'));
				exit;
			} else {
				$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena";
				eval(print_standard_redirect('petz_redirect'));
				exit;
			}
		} else {
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=battle&id=$id";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	} else {
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=battle&id=$id";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif (($op=="resign") AND ($id>0) AND ($vbulletin->userinfo['userid']>0)) {
	$pid=petz_ui("pid","r","TYPE_INT");
	$pet = $vbulletin->db->query_first("SELECT id, name, battle FROM " . TABLE_PREFIX . "petz_petz
	WHERE id=$pid AND battle=$id AND ownerid=".$vbulletin->userinfo['userid']."");
	$battle = $vbulletin->db->query_first("SELECT round FROM " . TABLE_PREFIX . "petz_battle WHERE id=$id AND type!=4");
	$round=$battle['round'];
	if($round>0){
		if($pet['id']>0){
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET
			battle=0, round=0, losses=losses+1 WHERE ownerid=".$vbulletin->userinfo['userid']." AND battle=$id AND id=$pid");
			$message="".$pet['name']." сдался и покинул сражение.";
			petz_battlelog($pid,0,$id,$round,$message,0,0);
			petz_round($id,$round);
		}
	}
	$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=battle&id=$id";
	eval(print_standard_redirect('petz_redirect'));
	exit;
} elseif (($op=="attack") AND ($id>0) AND ($vbulletin->userinfo['userid']>0)) {
	/*************************************************************************************/	
	$pid=petz_ui("pid","r","TYPE_INT");
	$oid=petz_ui("oppo","r","TYPE_INT");
	if($oid<1){ $oid=$naoppo; }
	$battle = $vbulletin->db->query_first("SELECT id, round, type FROM " . TABLE_PREFIX . "petz_battle WHERE id=$id");
	if ($battle['id']>0){
		if($battle['type']==4){
			if (($oid>0) AND ($pid>0)) {
				petz_attack($pid,$oid,$id,$battle['round'],0,1);
			}
		} else {
			if (($oid>0) AND ($pid>0)) {
				petz_attack($pid,$oid,$id,$battle['round'],0,0);
			}
		}
	}
	$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=battle&id=$id";
	eval(print_standard_redirect('petz_redirect'));
	exit;
	/*************************************************************************************/
} elseif (($op=="magic") AND ($id>0) AND ($vbulletin->userinfo['userid']>0)) {
	/*************************************************************************************/
	$pid=petz_ui("pid","r","TYPE_INT");
	$oid=petz_ui("oppo","r","TYPE_INT");
	if($oid<1){ $oid=$naoppo; }
	$battle = $vbulletin->db->query_first("SELECT id, round, type FROM " . TABLE_PREFIX . "petz_battle WHERE id=$id");
	if ($battle['id']>0){
		if($sid>0){
			if($battle['type']==4){
				if (($oid>0) AND ($pid>0)) {
					petz_magic($pid,$oid,$sid,$id,$battle['round'],0,1);
				}
			} else {
				if (($oid>0) AND ($pid>0)) {
					petz_magic($pid,$oid,$sid,$id,$battle['round'],0,0);
				}
			}
		}
	}
	$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=battle&id=$id";
	eval(print_standard_redirect('petz_redirect'));
	exit;
	/*************************************************************************************/
} elseif (($op=="updatelog") AND ($id>0)) {
	$logs=$vbulletin->db->query_read("SELECT id,message,round,footprint FROM " . TABLE_PREFIX . "petz_battlelog WHERE battleid=".$id."
	ORDER BY id ASC");
	while ($log = $vbulletin->db->fetch_array($logs)) {
		$log['time'] = vbdate($vbulletin->options['dateformat'], $log['footprint']);
		$log['time'] .= " - ";
		$log['time'] .= vbdate($vbulletin->options['timeformat'], $log['footprint']);
		$log['message'] = stripslashes($log['message']);
		$log['link']="<a href=\"javascript:viewMove(".$log['id'].")\">".$vbphrase['petz_view']."</a>";
		$output .="<span style=\"float:right;\"><i>".$log['time']."</i> | ".$log['link']."
		| ".$vbphrase['petz_round'].": ".$log['round']."</span>".$log['message']."<br /><br />";
	}
	if (empty($output))	{
		$output .="<div>".$vbphrase['petz_battle_no_events']."</div>";
	}
	echo $output;
	exit;
} elseif (($op=="updateround") AND ($id>0)) {
	$round = $vbulletin->db->query_first("SELECT round FROM " . TABLE_PREFIX . "petz_battle WHERE id=".$id."");
	if($round['round']<1){
		echo $vbphrase['petz_battle_ended'];
	} else {
		echo $round['round'];
	}
	exit;
} elseif (($op=="control") AND ($id>0) AND ($vbulletin->userinfo['userid']>0)) {
	$pid=petz_ui("pid","r","TYPE_INT");
	if($pid>0){
		$pet = $vbulletin->db->query_first("SELECT
		petz.id AS id, petz.type AS type, petz.name AS name, petz.gender AS gender, petz.color AS color, petz.moral AS moral,
		petz.dob AS dob, petz.hunger AS hunger, petz.health AS health, petz.mhealth AS mhealth, petz.strength AS strength,
		petz.defence AS defence, petz.agility AS agility, petz.experience AS experience, petz.level AS level, petz.round AS round,
		petz.guildid AS guildid, battle.round AS battleround, battle.type AS battletype
		FROM " . TABLE_PREFIX . "petz_petz AS petz
		LEFT JOIN ".TABLE_PREFIX."petz_battle AS battle ON (petz.battle = battle.id)
		WHERE petz.ownerid=".$vbulletin->userinfo['userid']." AND petz.id=".$pid." AND petz.battle=".$id."");
		if ($pet[id]>0){
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
			$pet['chealth']=$pet['health'];
			if ($pet['health']!=0) {
				$health=($pet['health']/$pet['mhealth'])*100;
				$pet['health']=($health/100)*65;
				$pet['health']=round($pet['health'], 0);
			}
			if ($pet['health']<1) {
				$pet['health']=0;
			} else {
				$pet['phealth']=round($health,0);
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
			if ($pet['hunger']==0) {
				$pet['hunger']=0;
			} else {
				$pet['hunger']=65/(100/$pet['hunger']);
				$pet['hunger']=round($pet['hunger'], 0);
			}
			$pet['battlecontrol']=1;
			$spells = $vbulletin->db->query_read("SELECT spell.name AS name, magic.id AS mid, magic.level AS level
			FROM ".TABLE_PREFIX."petz_magic AS magic
			LEFT JOIN ".TABLE_PREFIX."petz_spells AS spell ON (magic.sid = spell.id)
			WHERE magic.pid = ".$pet['id']." AND spell.moral = '".$pet['moral']."'
			ORDER BY magic.level ASC");
			$totalspells=$vbulletin->db->num_rows($spells);
			if ($totalspells!=0) {
				while ($spell=$vbulletin->db->fetch_array($spells)) {
					$spell[name]=stripslashes($spell[name]);
					$pet['spells'].="<option value=\"$spell[mid]\">$spell[name] ($spell[level])</option>\n";
				}
			}
			if ($pet['battletype']==4) {
				$pet[endtraining]=1;
				$bot = $vbulletin->db->query_first("SELECT id,type FROM " . TABLE_PREFIX . "petz_training
				WHERE battle=".$id." AND dead=0");
				if($bot[id]>0){
					$pet['opponents'].="<option value=\"$bot[id]\">".ucfirst($bot[type])."</option>\n";
				}
			} else {
				$pets = $vbulletin->db->query_read("SELECT id, name, guildid FROM ".TABLE_PREFIX."petz_petz
				WHERE battle=".$id." AND id!=".$pet['id']."
				ORDER BY level ASC");
				$totalpets=$vbulletin->db->num_rows($pets);
				if ($totalpets!=0) {
					while ($opponent=$vbulletin->db->fetch_array($pets)) {
						$opponent[name]=stripslashes($opponent[name]);
						if ($pet['battletype']==3) {
							if ($pet['guildid']!=$opponent[guildid]) {
								$pet['opponents'].="<option value=\"$opponent[id]\">$opponent[name]</option>\n";
							} else {
								$totalpets--; // cool way of doing it!
							}
						} else {
							$pet['opponents'].="<option value=\"$opponent[id]\">$opponent[name]</option>\n";
						}
					}
				}
			}
			if ($totalpets<1){
				$pet['endbattle']=1;
			} else {
				$pet['endbattle']=0;			
			}
			if ($pet['round']<$pet['battleround']){
				$pet[turn]=1;
			} else {
				$pet[turn]=0;
			}
			eval('$petz_control = "' . fetch_template('petz_battle_contender') . '";');
		}
	}
	if (empty($petz_control))	{
		$petz_control ="<td>".$vbphrase['petz_battle_nocontrol']."</td>";
	}
	echo $petz_control;
	exit;
} elseif (($op=="contenders") AND ($id>0)) {
	$petz = $vbulletin->db->query_read("SELECT
	id,type,name,ownerid,gender,color,moral,dob,hunger,health,mhealth,strength,defence,agility,experience,level FROM
	" . TABLE_PREFIX . "petz_petz WHERE battle=".$id."");
	$petcount=$vbulletin->db->num_rows($petz);
	if($petcount>0){
		while ($pet = $vbulletin->db->fetch_array($petz)){
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
			$pet['chealth']=$pet['health'];
			if ($pet['health']!=0) {
				$health=($pet['health']/$pet['mhealth'])*100;
				$pet['health']=($health/100)*65;
				$pet['health']=round($pet['health'], 0);
			}
			if ($pet['health']<1) {
				$pet['health']=0;
			} else {
				$pet['phealth']=round($health,0);
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
			if ($pet['hunger']==0) {
				$pet['hunger']=0;
			} else {
				$pet['hunger']=65/(100/$pet['hunger']);
				$pet['hunger']=round($pet['hunger'], 0);
			}
			if ($pet['ownerid']==$vbulletin->userinfo['userid']) {
				$pet['control']=1;
			} else {
				$pet['control']=0;
			}
			if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
				$bgcolor = "alt2";
				$newrow=2;
			} else {
				$bgcolor = "alt1";
				$newrow=0;
			}
			eval('$petz_contender_bit .= "' . fetch_template('petz_battle_contender') . '";');
		}
		if($petcount<2){ // check for bot (dont want to waste a query!)
			unset($pet);
			$pet = $vbulletin->db->query_first("SELECT id,type,health,mhealth,strength,defence,agility,level FROM
			" . TABLE_PREFIX . "petz_training WHERE battle=".$id." AND dead=0");
			if($pet[id]>0){
				$pet['name'] = ucfirst($pet['type']);
				$pet['chealth']=$pet['health'];
				if ($pet['health']!=0) {
					$health=($pet['health']/$pet['mhealth'])*100;
					$pet['health']=($health/100)*65;
					$pet['health']=round($pet['health'], 0);
				}
				if ($pet['health']<1) {
					$pet['health']=0;
				} else {
					$pet['phealth']=round($health,0);
				}
				if ($health<30) {
					$pet['status'] = "Injured";
				}
				$pet['experience']=0;
				$newrow=0;
				$pet['bot']=1;
				eval('$petz_contender_bit .= "' . fetch_template('petz_battle_contender') . '";');
			}
		}		
		if (empty($petz_contender_bit))	{
			$petz_contender_bit .="<td>".$vbphrase['petz_battle_nocontender']."</td>";
		}
	}
	echo $petz_contender_bit;
	exit;
} elseif (($op=="chat") AND ($id>0) AND ($vbulletin->userinfo['userid']>0)) {
	$chat=petz_ui("chat","r","TYPE_NOHTML");
	if ($chat==""){
		$chat=petz_ui("message","p","TYPE_NOHTML");
	}
	
 	$chat=strtr($chat, array(
			'%u0430'=>'а', '%u0431'=>'б', '%u0432'=>'в', '%u0433'=>'г', '%u0434'=>'д', '%u0435'=>'е', 
			'%u0451'=>'ё', '%u0436'=>'ж', '%u0437'=>'з', '%u0438'=>'и', '%u0439'=>'й', '%u043A'=>'к', 
			'%u043B'=>'л', '%u043C'=>'м', '%u043D'=>'н', '%u043E'=>'о', '%u043F'=>'п', '%u0440'=>'р', 
			'%u0441'=>'с', '%u0442'=>'т', '%u0443'=>'у', '%u0444'=>'ф', '%u0445'=>'х', '%u0446'=>'ц', 
			'%u0447'=>'ч', '%u0448'=>'ш', '%u0449'=>'щ', '%u044A'=>'ъ', '%u044B'=>'ы', '%u044C'=>'ь', 
			'%u044D'=>'э', '%u044E'=>'ю', '%u044F'=>'я',
			'%u0410'=>'А', '%u0411'=>'Б', '%u0412'=>'В', '%u0413'=>'Г', '%u0414'=>'Д', '%u0415'=>'Е', 
			'%u0401'=>'Ё', '%u0416'=>'Ж', '%u0417'=>'З', '%u0418'=>'И', '%u0419'=>'Й', '%u041A'=>'К', 
			'%u041B'=>'Л', '%u041C'=>'М', '%u041D'=>'Н', '%u041E'=>'О', '%u041F'=>'П', '%u0420'=>'Р', 
			'%u0421'=>'С', '%u0422'=>'Т', '%u0423'=>'У', '%u0424'=>'Ф', '%u0425'=>'Х', '%u0426'=>'Ц', 
			'%u0427'=>'Ч', '%u0428'=>'Ш', '%u0429'=>'Щ', '%u042A'=>'Ъ', '%u042B'=>'Ы', '%u042C'=>'Ь', 
			'%u042D'=>'Э', '%u042E'=>'Ю', '%u042F'=>'Я',
		));

	$message=$vbulletin->db->escape_string($chat);

	if($message!=""){
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_battlechat (id,userid,message,battleid,footprint)
		VALUES('',".$vbulletin->userinfo['userid'].",'".$message."',".$id.",".TIMENOW.")");
	}
	$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=battle&id=$id";
	eval(print_standard_redirect('petz_redirect'));
	exit;
} elseif (($op=="getchat") AND ($id>0)) {
	$chats = $vbulletin->db->query_read("SELECT chat.message AS message, chat.footprint AS footprint, u.username AS username, u.usergroupid AS usergroupid
		FROM " . TABLE_PREFIX . "petz_battlechat AS chat
		LEFT JOIN ".TABLE_PREFIX."user AS u ON (chat.userid = u.userid)
		WHERE chat.battleid=".$id." ORDER BY chat.footprint ASC");
	while ($chat = $vbulletin->db->fetch_array($chats)) {
		$chat['time'] = vbdate($vbulletin->options['dateformat'], $chat['footprint']);
		$chat['time'] .= " - ";
		$chat['time'] .= vbdate($vbulletin->options['timeformat'], $chat['footprint']);
		$chat['message'] = stripslashes($chat['message']);
		$chat['username'] = fetch_musername($chat, 'usergroupid');
		if($chat['username']==""){
			$chat['username']=$vbphrase['petz_battle_bot'];
		}
		$chatmessages .="<div><i style=\"float:right;\">".$chat['time']."</i>".$chat[username].": ".$chat['message']."</div>";
	}
	if (empty($chatmessages))	{
		$chatmessages ="<div>".$vbphrase['petz_battle_nobatchat']."</div>";
	}
	echo $chatmessages;
	exit;
} elseif ($id>0) {
	$battle = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_battle WHERE id=".$id."");
	if ($battle[id]<1) {
		show_alert($vbphrase['petz_no_battle']);
	} elseif ($battle['round']==0) {
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena&id=$id";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	} else {
		$battle['title']=stripslashes($battle['title']);
		if ($battle['type']<3){
			$showbets=1;
			$totalpoints=0;
			$totalbets=0;
			$ticketq = $vbulletin->db->query_read("SELECT betpoints FROM ".TABLE_PREFIX."petz_gamble WHERE bid=$id");
			while ($ticket=$vbulletin->db->fetch_array($ticketq)) {
			$totalpoints=$totalpoints+$ticket[betpoints];
			$totalbets++;
			}
			$totalpoints=number_format($totalpoints,2);
		} else {
			$showbets=0;
		}
		if ($battle['type']==1) {
			$battle['type']=$vbphrase['petz_battle_oneonone'];
		} elseif ($battle['type']==2) {
			$battle['type']=$vbphrase['petz_battle_freeforall'];
		} elseif ($battle['type']==3) {
			$battle['type']=$vbphrase['petz_battle_guildwar'];
		} elseif ($battle['type']==4) {
			$battle['type']=$vbphrase['petz_battle_training'];
		}
		$battle['created']=vbdate($vbulletin->options['dateformat'],$battle['created']);
		if($vbulletin->userinfo['userid']>0){
			$canchat=1;
		} else {
			$canchat=0;
		}
		eval('$petzbody = "' . fetch_template('petz_battle') . '";');
	}
} else {
	$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=arena";
	eval(print_standard_redirect('petz_redirect'));
	exit;
}
// Cleanup
unset($battle, $ticketq, $ticket);
?>