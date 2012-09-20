<?php
/*******************************************\
*	P3tz [vb]		  	File: toplist.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($vbulletin->options['petz_toplist_age']==1) {
	$pets = $vbulletin->db->query_read("SELECT petz.id AS id, petz.type AS type, petz.name AS name, petz.ownerid AS ownerid,
	petz.dob AS dob, petz.level AS level, owner.username AS owner
		FROM " . TABLE_PREFIX . "petz_petz AS petz
		LEFT JOIN ".TABLE_PREFIX."user AS owner ON (petz.ownerid = owner.userid)
		WHERE petz.dead=0
		ORDER BY petz.dob ASC
		LIMIT 10
	");
	$ageresults=$vbulletin->db->num_rows($pets);
	if ($ageresults!=0) {
		while ($pet=$vbulletin->db->fetch_array($pets)) {
			$pet['name']=stripslashes($pet['name']);
			$pet['owner']=stripslashes($pet['owner']);
			$pet['age']=intval((TIMENOW-$pet['dob']) / 86400);
			$pet['type']=ucfirst($pet['type']);
			if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
				$bgcolor = "alt2";
			} else {
				$bgcolor = "alt1";
			}
			$nonefound=0;
			eval('$petz_topage_bit .= "' . fetch_template('petz_pet_bit') . '";');
		}
	} else {
		$nonefound=1;
		eval('$petz_topage_bit = "' . fetch_template('petz_pet_bit') . '";');
	}	
	unset($pets, $pet);
}
if ($vbulletin->options['petz_toplist_wins']==1) {
	$pets = $vbulletin->db->query_read("SELECT petz.id AS id, petz.type AS type, petz.name AS name, petz.ownerid AS ownerid,
	petz.dob AS dob, petz.level AS level, petz.wins AS wins, petz.losses AS losses, owner.username AS owner
		FROM " . TABLE_PREFIX . "petz_petz AS petz
		LEFT JOIN ".TABLE_PREFIX."user AS owner ON (petz.ownerid = owner.userid)
		WHERE petz.dead=0
		ORDER BY petz.wins DESC
		LIMIT 10
	");
	$winresults=$vbulletin->db->num_rows($pets);
	if ($winresults!=0) {
		while ($pet=$vbulletin->db->fetch_array($pets)) {
			$pet['name']=stripslashes($pet['name']);
			$pet['owner']=stripslashes($pet['owner']);
			$pet['age']=intval((TIMENOW-$pet['dob']) / 86400);
			if ($pet[wins]<1) { $pet[wins]=$vbphrase['petz_not_any']; }
			if ($pet[losses]<1) { $pet[losses]=$vbphrase['petz_not_any']; }
			$pet['type']=ucfirst($pet['type']);
			if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
				$bgcolor = "alt2";
			} else {
				$bgcolor = "alt1";
			}
			$nonefound=0;
			eval('$petz_topwin_bit .= "' . fetch_template('petz_pet_bit') . '";');
		}
	} else {
		$nonefound=1;
		eval('$petz_topwin_bit = "' . fetch_template('petz_pet_bit') . '";');
	}
	unset($pets, $pet);
}
if ($vbulletin->options['petz_toplist_losses']==1) {
	$pets = $vbulletin->db->query_read("SELECT petz.id AS id, petz.type AS type, petz.name AS name, petz.ownerid AS ownerid,
	petz.dob AS dob, petz.level AS level, petz.wins AS wins, petz.losses AS losses, owner.username AS owner
		FROM " . TABLE_PREFIX . "petz_petz AS petz
		LEFT JOIN ".TABLE_PREFIX."user AS owner ON (petz.ownerid = owner.userid)
		WHERE petz.dead=0
		ORDER BY petz.losses DESC
		LIMIT 10
	");
	$winresults=$vbulletin->db->num_rows($pets);
	if ($winresults!=0) {
		while ($pet=$vbulletin->db->fetch_array($pets)) {
			$pet['name']=stripslashes($pet['name']);
			$pet['owner']=stripslashes($pet['owner']);
			$pet['age']=intval((TIMENOW-$pet['dob']) / 86400);
			if ($pet[wins]<1) { $pet[wins]=$vbphrase['petz_not_any']; }
			if ($pet[losses]<1) { $pet[losses]=$vbphrase['petz_not_any']; }
			$pet['type']=ucfirst($pet['type']);
			if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
				$bgcolor = "alt2";
			} else {
				$bgcolor = "alt1";
			}
			$nonefound=0;
			eval('$petz_toploss_bit .= "' . fetch_template('petz_pet_bit') . '";');
		}
	} else {
		$nonefound=1;
		eval('$petz_toploss_bit = "' . fetch_template('petz_pet_bit') . '";');
	}
	unset($pets, $pet);
}
if ($vbulletin->options['petz_toplist_stats']==1) {
	$pets = $vbulletin->db->query_read("SELECT petz.id AS id, petz.type AS type, petz.name AS name, petz.ownerid AS ownerid,
	petz.dob AS dob, petz.level AS level, petz.wins AS wins, petz.losses AS losses, owner.username AS owner
		FROM " . TABLE_PREFIX . "petz_petz AS petz
		LEFT JOIN ".TABLE_PREFIX."user AS owner ON (petz.ownerid = owner.userid)
		WHERE petz.dead=0
		ORDER BY petz.level DESC, petz.strength DESC, petz.defence DESC, petz.agility DESC
		LIMIT 10
	");
	$winresults=$vbulletin->db->num_rows($pets);
	if ($winresults!=0) {
		while ($pet=$vbulletin->db->fetch_array($pets)) {
			$pet['name']=stripslashes($pet['name']);
			$pet['owner']=stripslashes($pet['owner']);
			$pet['age']=intval((TIMENOW-$pet['dob']) / 86400);
			if ($pet[wins]<1) { $pet[wins]=$vbphrase['petz_not_any']; }
			if ($pet[losses]<1) { $pet[losses]=$vbphrase['petz_not_any']; }
			$pet['type']=ucfirst($pet['type']);
			if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
				$bgcolor = "alt2";
			} else {
				$bgcolor = "alt1";
			}
			$nonefound=0;
			eval('$petz_topstats_bit .= "' . fetch_template('petz_pet_bit') . '";');
		}
	} else {
		$nonefound=1;
		eval('$petz_topstats_bit = "' . fetch_template('petz_pet_bit') . '";');
	}
	unset($pets, $pet);
}
eval('$petzbody = "' . fetch_template('petz_toplist') . '";');
?>