<?php
/*******************************************\
*	P3tz [vb]		  	File: view.php		*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($id<1) {
	show_alert($vbphrase['petz_not_pet']);
} else {
	$pet = $vbulletin->db->query_first("SELECT petz.*, mother.name AS mothern, father.name AS fathern, adopt.id AS adopt, guild.name AS guild, owner.username AS owner
		FROM " . TABLE_PREFIX . "petz_petz AS petz
		LEFT JOIN ".TABLE_PREFIX."petz_petz AS mother ON (petz.mother = mother.id)
		LEFT JOIN ".TABLE_PREFIX."petz_petz AS father ON (petz.father = father.id)
		LEFT JOIN ".TABLE_PREFIX."petz_adopt AS adopt ON (petz.id = adopt.petid)
		LEFT JOIN ".TABLE_PREFIX."petz_guild AS guild ON (petz.guildid = guild.id)
		LEFT JOIN ".TABLE_PREFIX."user AS owner ON (petz.ownerid = owner.userid)
		WHERE petz.id='".$id."'
	");
	if ($pet['id']<1) {
		show_alert($vbphrase['petz_not_pet']);
	} else {
		$pet['name'] = stripslashes($pet['name']);
		$pet['owner'] = stripslashes($pet['owner']);
		$pet['mothern'] = stripslashes($pet['mothern']);
		$pet['fathern'] = stripslashes($pet['fathern']);
		$pet['guild'] = stripslashes($pet['guild']);
		$pcv=explode("-", $pet['color']);
		$pet['Rpcv'] = $pcv[0];
		$pet['Gpcv'] = $pcv[1];
		$pet['Bpcv'] = $pcv[2];
		$pet['healthp'] = 0;
		$pet['thealth'] = $pet['health'];
		$pet['tmoral'] = $pet['moral'];
		$pet['moralb'] = $pet['moral']*$pet['moral'];
		$pet['moralp'] = round(sqrt($pet['moralb']), 0);
		$pet['morala'] = 100-$pet['moralp'];
		$pet['hungerp'] = $pet['hunger'];
		$pet['hungera'] = 100-$pet['hungerp'];
		$pet['experiencea'] = 100-$pet['experience'];
		$pet['levela'] = 100-$pet['level'];
		if ($pet['dead'] == 1) {
			$pet['age']="Dead";
		} else {
			$pet['age']=intval((TIMENOW-$pet['dob']) / 86400);
		}
		$pet['totalbattles']=$pet['wins']+$pet['losses'];
		if ($pet['totalbattles']!=0) {
			$winper=($pet['wins']/$pet['totalbattles'])*100;
			$pet['winsp']=round($winper, 0);
		} else {
			$pet['winsp']=0;
		}
		$pet['lossesp']=100-$pet['winsp'];
		if ($pet['moral'] < -50) {
			$pet['moral'] = "Evil";
		} elseif ($pet['moral'] > 50) {
			$pet['moral'] = "Good";
		} else {
			$pet['moral'] = "Neutral";
		}
		if ($pet[health]!=0) {
			$health=($pet['health']/$pet['mhealth'])*100;
			$pet['healthp']=round($health, 0);
			$pet['health']=($health/100)*65;
			$pet['health']=round($pet['health'], 0);
		}
		if ($pet['health']<1) {
		 	$pet['health']=0;
		} else {
			$pet['health']=$pet['health'];
		}
		$pet['healtha']=100-$pet['healthp'];
		if ($health<20) {
			$pet['status'] = "Injured";
		} elseif ($health<50) {
			$pet['status'] = "Ill";
		} elseif ($pet['hunger']>50) {
			$pet['status'] = "Hungry";
		} else {
			$pet['status'] = "Ok";
		}
		if ($pet['hunger']<1) {
			$pet['hunger']=0;
		} else {
			$pet['hunger']=65/(100/$pet['hunger']);
			$pet['hunger']=round($pet['hunger'], 0);
		}
		$pet['steal']=0;
		if (($vbulletin->userinfo['userid']!=$pet['ownerid']) AND ($petgroup['own']==1) AND ($vbulletin->options['petz_stealpets']>0)) {
			$pet['steal']=1;
		}
		$pet['resurrect']=0;
		if (($vbulletin->userinfo['userid']==$pet['ownerid']) AND ($pet['dead']==1) AND ($vbulletin->options['petz_resurrect']!=0)) {
			$pet['resurrect']=1;
		}
		// Magic Spells!
		$spells = $vbulletin->db->query_read("SELECT spell.id AS id, spell.name AS name, spell.element AS element, spell.moral AS moral,
		spell.description AS description, spell.damage AS damage, spell.chance AS chance, magic.sid AS sid, magic.level AS level
		FROM ".TABLE_PREFIX."petz_magic AS magic
		LEFT JOIN ".TABLE_PREFIX."petz_spells AS spell ON (magic.sid = spell.id)
		WHERE pid = ".$pet['id']."
		ORDER BY magic.level ASC
		");
		$totalspells=$vbulletin->db->num_rows($spells);
		if ($totalspells!=0) {
			while ($spell=$vbulletin->db->fetch_array($spells)) {
				$spell['name']=stripslashes($spell['name']);
				$spell['description']=stripslashes($spell['description']);
				$spell['image']=$spell['element'];
				$spell['element']=$vbphrase['petz_'.$spell['element'].''];
				if ((!isset($bgcolor)) OR ($bgcolor=="alt2")) {
					$bgcolor = "alt1";
				} else {
					$bgcolor = "alt2";
				}
				$spell['canuse']=0;
				$spell['cost']=$spell['level'];
				eval('$petz_spell_bit .= "' . fetch_template('petz_spell_bit') . '";');
			}
		}
	}
}
if ($pet['dead']!=1) {
	eval('$petpic = "' . fetch_template('petz_view') . '";');
}
// Inventory
$items = $vbulletin->db->query_read("
		SELECT inventory.*,inventory.id AS iid, inventory.itemid AS itemid, inventory.userid AS userid, item.id AS id, item.name As name, item.description AS description, item.cost AS cost, item.image AS image, item.moral AS moral, item.hunger AS hunger, item.health AS health, item.special AS special
		FROM ".TABLE_PREFIX."petz_inventory AS inventory
		LEFT JOIN ".TABLE_PREFIX."petz_items AS item ON (inventory.itemid = item.id)
		WHERE userid = '".$vbulletin->userinfo['userid']."'
		ORDER BY inventory.id ASC");
$totalitems=$vbulletin->db->num_rows($items);
if ($totalitems!=0) {
	$noitems=0;
	while ($item=$vbulletin->db->fetch_array($items)) {
		if ($item['hunger']!=0) { $item['effect'].="".$vbphrase['petz_hunger'].": ".$item['hunger']."\n"; }
		if (($item['effect']!="") AND ($item['health']!=0)) { $item['effect'].="<br>\n"; }
		if ($item['health']!=0) { $item['effect'].="".$vbphrase['petz_health'].": ".$item['health']."\n"; }
		if (($item['effect']!="") AND ($item['moral']!=0)) { $item['effect'].="<br>\n"; }
		if ($item['moral']!=0) { $item['effect'].="".$vbphrase['petz_moral'].": ".$item['moral']; }
		if (($item['effect']!="") AND ($item['special']!=0)) { $item['effect'].="<br>\n"; }
		if ($item['special']==1) { $item['effect'].=$vbphrase['petz_change_name']; }
		if ($item['special']==2) { $item['effect'].=$vbphrase['petz_change_color']; }
		if ($item['special']==3) { $item['effect'].=$vbphrase['petz_kill']; }
		$item['name']=stripslashes($item['name']);
		$item['description']=stripslashes($item['description']);
		$item['hasstock']=0;
		$item['canuse']=1;
		$item['canbuy']=0;
		$item['cantbuy']=0;
		$item['cansell']=0;
		$item['cansteal']=0;
		$item['vetuse']=0;
		if ((!isset($bgcolor)) OR ($bgcolor=="alt2")) {
			$bgcolor = "alt1";
		} else {
			$bgcolor = "alt2";
		}
		eval('$petz_inventory_bit .= "' . fetch_template('petz_item') . '";');
	}
} else {
	$noitems=1;
	eval('$petz_inventory_bit .= "' . fetch_template('petz_item') . '";');
}
eval('$petzbody = "' . fetch_template('petz_profile') . '";');
// Cleanup
unset($item, $items, $pet);
?>