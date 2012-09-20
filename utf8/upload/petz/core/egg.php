<?php
/*******************************************\
*	P3tz [vb]		  	File: egg.php		*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($petgroup['own'] == 0) {
	print_no_permission();
	exit;
} else {

	$petq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo['userid']."'");
	$peti = $vbulletin->db->num_rows($petq);
	if ($peti >= $vbulletin->options['petz_maxpetz']) {
		show_alert($vbphrase['petz_pet_toomany']);
	} else {
		$pet = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_default WHERE id=1 ");
		$pet['description'] = stripslashes($pet['description']);
		$pet['name'] = ucfirst($pet['type']);
		$pet['Rpcv'] = 255;
		$pet['Gpcv'] = 0;
		$pet['Bpcv'] = 0;
		$pet['id'] = 0;
		if (!$id) {
			if ($vbulletin->options['petz_bbdate'] > 86399) {
				$numberof=round(($vbulletin->options['petz_bbdate']/86400),0);
			} else {
				$numberof = 1;
			}
			eval('$egg_bit = "' . fetch_template('petz_view') . '";');
			$petq = $vbulletin->db->query_read("SELECT id, name, gender FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo['userid']."' AND type!='egg'");
			while ($epet=$vbulletin->db->fetch_array($petq)) {
				$epet['name'] = stripslashes($epet['name']);
				if ($epet['gender'] == "Male") {
					$dadoption .= "<option value=\"".$epet['id']."\">".$epet['name']."</option>\n";
				} else {
					$momoption .= "<option value=\"".$epet['id']."\">".$epet['name']."</option>\n";
				}
			}
			eval('$petzbody = "' . fetch_template('petz_egg') . '";');
		} else {
			$name = petz_ui("name","p","TYPE_NOHTML");
			$npet['name'] = $vbulletin->db->escape_string($name);
			if ($npet['name'] == '') {
				show_alert($vbphrase['petz_no_name']);
			} elseif ($pet['cost']>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
				show_alert($vbphrase['petz_not_enough_points']);
			} else {
				if ($id > 0) {
					$mom = $vbulletin->db->query_first("SELECT id, color, mhealth, strength, defence, agility, level, wins, losses FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo['userid']."' AND id='".$id."' AND gender='Female' AND type!='egg'");
					$pcv = explode("-", $mom['color']);
					$mom['Rpcv'] = $pcv[0];
					$mom['Gpcv'] = $pcv[1];
					$mom['Bpcv'] = $pcv[2];
					
					if ($mom['level'] > 1)
					{
						for($i = $mom['level']-1;$i > 1;$i--)
						{
							$d = floor(($vbulletin->options['petz_levelup']/10));
							
							$ph = floor(($mom['mhealth']/100)*($vbulletin->options['petz_levelup'] - $d));
							$mom['mhealth'] -= $ph;
							$sh = floor(($mom['strength']/100)*($vbulletin->options['petz_levelup'] - $d));
							$mom['strength'] -= $sh;
							$dh = floor(($mom['defence']/100)*($vbulletin->options['petz_levelup'] - $d));
							$mom['defence'] -= $dh;
							$ah = floor(($mom['agility']/100)*($vbulletin->options['petz_levelup'] - $d));
							$mom['agility'] -= $ah;
						}
						
						$pp = $mom['wins'] - $mom['losses'];
						if($pp <= 0) { $pp = 0; }
						
						$mom['mhealth'] += $pp;
						$mom['strength'] += $pp;
						$mom['defence'] += $pp;
						$mom['agility'] += $pp;
						
						$mom['health'] = $mom['mhealth'];
					}
					else 
					{ 
						$pp = $mom['wins'] - $mom['losses'];
						if($pp <= 0) { $pp = 0; }
						
						$mom['mhealth'] += $pp;
						$mom['strength'] += $pp;
						$mom['defence'] += $pp;
						$mom['agility'] += $pp;
						
						$mom['health'] = $mom['mhealth']; 
					}
				}
				
				if ($op > 0) {
					$dad = $vbulletin->db->query_first("SELECT id, color, mhealth, strength, defence, agility, level, wins, losses FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND ownerid='".$vbulletin->userinfo['userid']."' AND id='".$op."' AND gender='Male' AND type!='egg'");
					$pcv = explode("-", $dad['color']);
					$dad['Rpcv'] = $pcv[0];
					$dad['Gpcv'] = $pcv[1];
					$dad['Bpcv'] = $pcv[2];
					
					if ($dad['level'] > 1)
					{
						for($i = $dad['level']-1;$i > 1;$i--)
						{
							$d = floor(($vbulletin->options['petz_levelup']/10));
						
							$ph = floor(($dad['mhealth']/100)*($vbulletin->options['petz_levelup'] - $d));
							$dad['mhealth'] -= $ph;
							$sh = floor(($dad['strength']/100)*($vbulletin->options['petz_levelup'] - $d));
							$dad['strength'] -= $sh;
							$dh = floor(($dad['defence']/100)*($vbulletin->options['petz_levelup'] - $d));
							$dad['defence'] -= $dh;
							$ah = floor(($dad['agility']/100)*($vbulletin->options['petz_levelup'] - $d));
							$dad['agility'] -= $ah;
						}
						
						$pp = $dad['wins'] - $dad['losses'];
						if($pp <= 0) { $pp = 0; }
						
						$dad['mhealth'] += $pp;
						$dad['strength'] += $pp;
						$dad['defence'] += $pp;
						$dad['agility'] += $pp;
						
						$dad['health'] = $dad['mhealth'];
					}
					else 
					{ 
						$pp = $dad['wins'] - $dad['losses'];
						if($pp <= 0) { $pp = 0; }
						
						$dad['mhealth'] += $pp;
						$dad['strength'] += $pp;
						$dad['defence'] += $pp;
						$dad['agility'] += $pp;
						
						$dad['health'] = $dad['mhealth']; 
					}
				}
				
				if ($mom['id'] < 1) {
					$id = "x";
				}
				if ($dad['id'] < 1) {
					$op = "x";
				}
				
				if($id == "x")
				{
					$q = $vbulletin->db->query_read("SELECT count(*) FROM " . TABLE_PREFIX ."petz_default WHERE type!='egg'");
					$c = $vbulletin->db->fetch_row($q);
					$momq = $vbulletin->db->query_read("SELECT id, health, strength, defence, agility FROM " . TABLE_PREFIX . "petz_default WHERE type!='egg' LIMIT " . mt_rand(0,$c[0]) . ",1");
					$momi = $vbulletin->db->num_rows($momq);
					while($momt = $vbulletin->db->fetch_array($momq))
					{
						$mom['id'] = 0;
						$mom['health'] = $momt['health'];
						$mom['strength'] = $momt['strength'];
						$mom['defence'] = $momt['defence'];
						$mom['agility'] = $momt['agility'];
						$mom['Rpcv'] = mt_rand(0,255);
						$mom['Gpcv'] = mt_rand(0,255);
						$mom['Bpcv'] = mt_rand(0,255);
					}
				}

				if($op == "x")
				{
					$q = $vbulletin->db->query_read("SELECT count(*) FROM " . TABLE_PREFIX ."petz_default WHERE type!='egg'");
					$c = $vbulletin->db->fetch_row($q);
					$dadq = $vbulletin->db->query_read("SELECT id, health, strength, defence, agility FROM " . TABLE_PREFIX . "petz_default WHERE type!='egg' LIMIT " . mt_rand(0,$c[0]) . ",1");
					$dadi = $vbulletin->db->num_rows($dadq);
					while($dadt = $vbulletin->db->fetch_array($dadq))
					{
						$dad['id'] = 0;
						$dad['health'] = $dadt['health'];
						$dad['strength'] = $dadt['strength'];
						$dad['defence'] = $dadt['defence'];
						$dad['agility'] = $dadt['agility'];
						$dad['Rpcv'] = mt_rand(0,255);
						$dad['Gpcv'] = mt_rand(0,255);
						$dad['Bpcv'] = mt_rand(0,255);
					}
				}
				
				
				$npet['mother'] 	= $mom['id'];
				$npet['father'] 	= $dad['id'];
				$npet['health'] 	= round(($mom['health'] + $dad['health']) / 2,0);
				$npet['strength'] 	= round(($mom['strength'] + $dad['strength']) / 2,0);
				$npet['defence'] 	= round(($mom['defence'] + $dad['defence']) / 2,0);
				$npet['agility'] 	= round(($mom['agility'] + $dad['agility']) / 2,0);
				$npet['Rpcv'] 		= round(($mom['Rpcv'] + $dad['Rpcv']) / 2,0);
				$npet['Gpcv'] 		= round(($mom['Gpcv'] + $dad['Gpcv']) / 2,0);
				$npet['Bpcv'] 		= round(($mom['Bpcv'] + $dad['Bpcv']) / 2,0);
				$npet['color'] 		= $npet['Rpcv']."-".$npet['Gpcv']."-".$npet['Bpcv'];
				
				// Gender generate
				if (rand(0,1) == 0) {
					$npet['gender'] = "Female";
				} else {
					$npet['gender'] = "Male";
				}
				
				$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET ".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$pet[cost]." WHERE userid='".$vbulletin->userinfo['userid']."'");
				$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_petz (id, type, name, ownerid, gender, color, moral, dob, guildreq, guildid, wins, losses, mother, father, hunger, health, mhealth, strength, defence, agility, experience, level, battle, care, dead) VALUES('','egg','".$npet['name']."','".$vbulletin->userinfo['userid']."','".$npet['gender']."','".$npet['color']."','0','". TIMENOW ."','0','0','0','0','".$npet['mother']."','".$npet['father']."','0','".$npet['health']."','".$npet['health']."','".$npet['strength']."','".$npet['defence']."','".$npet['agility']."','0','1','0','0','0')");
				if ($vbulletin->options['petz_log'] == 1) {
					petz_log($op,$id,$do,$extra);
				}
				$vbulletin->url = 'petz.php';
				eval(print_standard_redirect('petz_redirect'));
				exit;
			}
		}
	}
}
// Cleanup
unset($petq, $epet, $pet, $npet, $mom, $momq, $momt, $dad, $dadq, $dadt);
?>