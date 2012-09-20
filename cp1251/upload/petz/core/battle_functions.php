<?php
/*******************************************\
*	P3tz [vb]		  	File: battle_f..php	
*	Version: 3.3.1	   	Licensed	
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
function petz_gamble($pet,$battle) { // Rewards?
	global $vbulletin;
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_gamble SET won=1 WHERE bid=$battle AND petid=$pet");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_gamble WHERE bid=$battle AND won!=1");
}
function petz_bot_attack($petid,$pet,$pstr,$pdef,$pspeed,$phealth,$botid,$bot,$botstr,$botdef,$botspeed,$bid,$round, $pmhealth) { // Auto Attack
	global $vbulletin, $vbphrase;
	if($botspeed>$pspeed) {
		$hit=100;
	} else {
		$hit=round(($botspeed/$pspeed)*100,0);
	}
	
	if($çhealth > $pmhealth) {
		$vbulletin->db->query_first("UPDATE " . TABLE_PREFIX . "petz_petz SET health='0' WHERE id='".$petid."'");
		petz_die();
	}
	
	if($hit>rand(0,100)) {
		if($botstr>$pdef){
			$damage=$botstr-($pdef);
			if($damage>0){
				$damage=rand($damage,$damage/2);
			}
		} else {
			$damage=($botstr)-($pdef/2);
			if($damage>0){
				$damage=rand(0,$damage/2);
			}
		}
		
		$damage=round($damage,0);
		if ($phealth-$damage<=0) {
			$killed=1;
			$killingit=" ".$vbphrase['petz_battle_killing']." $pet!";
		} else {
			$killed=0;
			$killingit="";
		}
		if($damage<1){
			$damage=0;
		}
		$message="$bot ".$vbphrase['petz_battle_hit']." $pet. ".$vbphrase['petz_battle_causing']." $damage ".$vbphrase['petz_battle_damage']."!";
		$message.=$killingit;
	} else {
		$damage=0;
		$message="$bot ".$vbphrase['petz_battle_hit']." $pet. ".$vbphrase['petz_battle_miss']."!";
	}
	if ($killed==1) {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_training SET dead=1 WHERE battle=$bid");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz
			SET health=0, fexperience=0 WHERE id='".$petid."'");
	} else {
		if($damage!=0){
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz
			SET health=health-$damage WHERE id='".$petid."'");
		}
	}
	petz_battlelog($botid,$petid,$bid,$round,$message,$damage,"x");
}
function petz_die() { // Die
	global $vbulletin;
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET
	health=0, battle=0, round=0, losses=losses+1, dead=1 WHERE health<1 AND dead=0 AND battle>0");
}
function petz_attack($attacker,$defender,$bid,$round,$auto,$bot) { // Attack
	global $vbulletin, $vbphrase;
	if ($floodkill!=1) {
		if($auto!=1){
			$exwhere="AND ownerid=".$vbulletin->userinfo['userid']."";
		} else {
			$exwhere="";
		}
		$atk = $vbulletin->db->query_first("SELECT id, type, name, health, mhealth, strength, defence, agility, experience, fexperience, level FROM
		" . TABLE_PREFIX . "petz_petz WHERE id=$attacker AND battle=$bid AND round<$round $exwhere");
		if($bot==1){
			$def = $vbulletin->db->query_first("SELECT id, type, strength, defence, agility, health, level, mhealth FROM
			" . TABLE_PREFIX . "petz_training WHERE id=$defender AND battle=$bid AND dead=0");
			$def['name']=ucfirst($def['type']);
		} else {
			$def = $vbulletin->db->query_first("SELECT id, name, strength, defence, agility, health, level, mhealth FROM
			" . TABLE_PREFIX . "petz_petz WHERE id=$defender AND battle=$bid");
		}
		

		if($atk['health'] > $atk['mhealth']) {
			$vbulletin->db->query_first("UPDATE " . TABLE_PREFIX . "petz_petz SET health='0' WHERE id='$attacker'");
			petz_die();
		}

		
		if (($atk['id']>0) AND ($def['id']>0)) {
			
			if($atk['level'] > $def['level']) {
				$tofine = $atk['level'] - $def['level'];
			} else {
				$tofine = 1;
			}
			
			if($atk['health'] >= 1) {
				if ($vbulletin->options['petz_slowexp']==1) {
					$atk['fexperience']=round($atk['fexperience']+(rand(4,8)/$tofine)+$round);
					($atk['level'] < $def['level']) ? $atk['fexperience'] += ($def['level'] - $atk['level']): "" ;
				} else {
					$atk['fexperience']=round($atk['fexperience']+(rand(10,20)/$tofine)+$round);
					($atk['level'] < $def['level']) ? $atk['fexperience'] += ($def['level'] - $atk['level']): "" ;

				}
			}
			
			if($atk['agility']>$def['agility']) {
				$hit=100;
			} else {
				$hit=round(($atk['agility']/$def['agility'])*100,0);
			}
			if($hit>rand(0,100)) {
				if($atk['strength']>$def['defence']){
					$damage=$atk['strength']-($def['defence']);
					if($damage>0){
						$damage=rand($damage,$damage/2);
					}
				} else {
					$damage=($atk['strength'])-$def['defence']/2;
					if($damage>0){
						$damage=rand(0,$damage/2);
					}
				}
				if ($atk['health']!=0) {
					$health=($atk['health']/$atk['mhealth'])*100;
				}
				if ($health<10) {
					$damage=$damage*2;
					$atktype=$vbphrase['petz_battle_furiously'];
				} else {
					$atktype="";
				}
				$damage=round($damage,0);
				if ($def['health']-$damage<=0) {
					
					$killed=1;
					
					$expp = round($atk['fexperience'] / $atk['level']);
					$atk['fexperience'] = 0;
					
					if($expp < 1) { $expp = 1; }
					
					($atk['level'] < $def['level']) ? $expp += (($def['level'] - $atk['level'])*3): "" ;
					
					$atk['experience'] += $expp;


					$killingit=" ".$vbphrase['petz_battle_killing']." $def[name]! <br />+".$expp." ".$vbphrase['petz_experience']."";
					
				} else {
					$killed=0;
					$killingit="";
				}
				if($damage<1){
					$damage=0;
				}
				$message="$atk[name] ".$vbphrase['petz_battle_hit']." $def[name]. $atktype ".$vbphrase['petz_battle_causing']." $damage ".$vbphrase['petz_battle_damage']."!";
				$message.=$killingit;
			} else {
				$damage=0;
				$message="$atk[name] ".$vbphrase['petz_battle_hit']." $def[name]. ".$vbphrase['petz_battle_miss']."!";
			}

			if($atk['experience']>99){
				petz_levelup($atk['id'],$atk['type'],$bid,$round);
			} else {
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz
				SET round=round+1, experience=".$atk['experience'].", fexperience=".$atk['fexperience']." WHERE id='".$atk['id']."'");
			}

			if($bot == 1){
				if($damage != 0){
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_training
					SET health=health-$damage WHERE id='".$def['id']."'");
				}
				if($killed != 0){
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_training
					SET health=0, dead=1 WHERE id='".$def['id']."'");
				}
			} else {
				if($damage != 0){
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz
					SET health=health-$damage WHERE id='".$def['id']."'");
				}
				if($killed != 0){
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz
					SET health=0, fexperience=0 WHERE id='".$def['id']."'");
					petz_die();
				}
			}
			petz_round($bid,$round);
			petz_battlelog($attacker,$defender,$bid,$round,$message,$damage,0);
			if(($bot == 1) AND ($killed == 0)){
				petz_bot_attack($atk['id'],$atk['name'],$atk['strength'],$atk['defence'],$atk['agility'],$atk['health'],$def['id'],$def['name'],$def['strength'],$def['defence'],$def['agility'],$bid,$round, $atk['mhealth']);
			}
		}
		$floodkill = 1;
	}
}
function petz_magic($attacker,$defender,$spell,$bid,$round,$auto,$bot) { // Magic
	global $vbulletin, $vbphrase;
	if ($floodkill != 1) {
		if($auto != 1){
			$exwhere = "AND ownerid=".$vbulletin->userinfo['userid']."";
		} else {
			$exwhere = "";
		}
		$atk = $vbulletin->db->query_first("SELECT id, type, name, health, strength, defence, agility FROM
		" . TABLE_PREFIX . "petz_petz WHERE id=$attacker AND battle=$bid AND round<$round $exwhere");
		if($bot == 1){
			$def = $vbulletin->db->query_first("SELECT id, type, strength, defence, agility, health FROM
			" . TABLE_PREFIX . "petz_training WHERE id=$defender AND battle=$bid AND dead=0");
			$def[name]=ucfirst($def[type]);
		} else {
			$def = $vbulletin->db->query_first("SELECT id, name, health FROM
			" . TABLE_PREFIX . "petz_petz WHERE id=$defender AND battle=$bid");
		}
		if (($atk['id'] > 0) AND ($def['id'] > 0)) {
			$atks = $vbulletin->db->query_first("SELECT magic.sid AS sid, magic.pid AS pid, magic.level AS level, spell.name AS name,
			spell.damage AS damage, spell.chance AS chance FROM
			" . TABLE_PREFIX . "petz_magic AS magic
			LEFT JOIN ".TABLE_PREFIX."petz_spells AS spell ON (magic.sid = spell.id)
			WHERE magic.id=$spell");
			if($bot == 0){
				$defs = $vbulletin->db->query_first("SELECT id, level FROM " . TABLE_PREFIX . "petz_magic WHERE id='{$spell}'
				AND pid='{$defender}'");
			}
			if($atks['pid'] == $atk['id']){
				if ($atks['level'] == 100){
					$hit = 1;
				} elseif ($atks['chance'] == 100){
					$hit = 1;
				} elseif ($atks['chance'] > rand(0,100)) {
					$hit = 1;
				} else {
					$hit = 0;
				}
				if($hit==1) {
					$lowerdamage = round(($atks['damage']/100)*$atks['level'],0);
					$damage = rand($lowerdamage,$atks['damage']);
					$damage = round(($def['health']/100)*$damage,0);
					if($atks['level'] > $defs['level']){
						$message = $atk['name']." ".$vbphrase['petz_battle_magic_cast']." ".$atks['level']." ".$atks['name']." ".$vbphrase['petz_battle_magic_upon']." $def[name]. ".$vbphrase['petz_battle_causing']." $damage ".$vbphrase['petz_battle_damage']."!";
					} else {
						$damage = round($damage/2,0);
						$message = $atk['name']." ".$vbphrase['petz_battle_magic_cast']." $atks[level] $atks[name] ".$vbphrase['petz_battle_magic_upon']." $def[name] ".$vbphrase['petz_battle_magic_countered']." $damage!";
					}
					if($bot == 1){
						if($damage != 0){
							if($damage == $def['health']) {
								$killed = 1;
								$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_training
								SET health=0, dead=1 WHERE id='".$def['id']."'");
							} else {
								$killed = 0;
								$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_training
								SET health=health-$damage WHERE id='".$def['id']."'");
							}
						}
					} else {
						if($damage != 0){
							$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz
							SET health=health-$damage WHERE id='".$def['id']."'");
							petz_die();
						}
					}
				} else {
					$message="$atk[name] ".$vbphrase['petz_battle_magic_cast']." $atks[level] $atks[name] ".$vbphrase['petz_battle_magic_upon']." $def[name]. ".$vbphrase['petz_battle_magic_fail']."";
				}
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz
				SET round=round+1 WHERE id='".$atk['id']."'");
				petz_round($bid,$round);
				petz_battlelog($attacker,$defender,$bid,$round,$message,$damage,$atks['sid']);
				if(($bot == 1) AND ($killed == 0)){
petz_bot_attack($atk['id'],$atk['name'],$atk['strength'],$atk['defence'],$atk['agility'],$atk['health'],$def['id'],$def['name'],$def['strength'],$def['defence'],$def['agility'],$bid,$round, $atk['mhealth']);
				}
			}
			$floodkill=1;
		}
	}
}
function petz_round($battle,$round) { // Battle Round
	global $vbulletin;
	$pet = $vbulletin->db->query_first("SELECT id FROM
	" . TABLE_PREFIX . "petz_petz WHERE battle=$battle AND round<$round");
	if ($pet['id'] < 1){
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_battle SET round=round+1 WHERE id=$battle");
	}
}
function petz_levelup($pet,$type,$battle,$round) { // Level
	global $vbulletin, $vbphrase;
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET level=level+1 WHERE id=$pet AND level<100");
	$default = $vbulletin->db->query_first("SELECT health, strength, defence, agility FROM
	" . TABLE_PREFIX . "petz_default WHERE type='$type'");
	if ($vbulletin->options['petz_levelup']>0) {
		$default['strength'] = ($default['strength']/100)*$vbulletin->options['petz_levelup'];
		$default['defence'] = ($default['defence']/100)*$vbulletin->options['petz_levelup'];
		$default['agility'] = ($default['agility']/100)*$vbulletin->options['petz_levelup'];
		$default['health'] = ($default['health']/100)*$vbulletin->options['petz_levelup'];
	} else {
		$default['strength'] = 0;
		$default['defence'] = 0;
		$default['agility'] = 0;
		$default['health'] = 0;
	}
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET
	health=health+$default[health], mhealth=mhealth+$default[health], strength=strength+$default[strength],
	defence=defence+$default[defence], agility=agility+$default[agility], round=round+1, experience=0, fexperience=0 WHERE id=$pet");
	$message = $vbphrase['petz_battle_levelup'];
	petz_battlelog($pet,0,$battle,$round,$message,0,0);
}
function petz_battlelog($pid,$oid,$bid,$round,$message,$damage,$spell) { // Store Log
	global $vbulletin;
	$msg=$vbulletin->db->escape_string($message);
	if ($spell=="x") {
		$timestamp=TIMENOW+60;
		$spell=0;
	} else {
		$timestamp=TIMENOW;
	}
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_battlelog
	(id,battleid,petid,oppid,round,message,damage,spell,footprint)
	VALUES('','".$bid."','".$pid."','".$oid."','".$round."','".$msg."','".$damage."','".$spell."',".$timestamp.")");
}
?>