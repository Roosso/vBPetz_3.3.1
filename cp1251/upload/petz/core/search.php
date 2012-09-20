<?php
/*******************************************\
*	P3tz [vb]		  	File: search.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($op!="") {
	if ($id=="nd") {
		$xcon="petz.name DESC";
	} elseif ($id=="ta") {
		$xcon="petz.type ASC";
	} elseif ($id=="td") {
		$xcon="petz.type DESC";
	} elseif ($id=="aa") {
		$xcon="petz.dob DESC";
	} elseif ($id=="ad") {
		$xcon="petz.dob ASC";
	} elseif ($id=="la") {
		$xcon="petz.level ASC";
	} elseif ($id=="ld") {
		$xcon="petz.level DESC";
	} elseif ($id=="oa") {
		$xcon="owner.username ASC";
	} elseif ($id=="od") {
		$xcon="owner.username DESC";
	} else {
		$xcon="petz.name ASC";
	}		
	$pets = $vbulletin->db->query_read("SELECT petz.id AS id, petz.type AS type, petz.name AS name, petz.ownerid AS ownerid, petz.dob AS dob, petz.level AS level, petz.dead AS dead, owner.username AS owner
		FROM " . TABLE_PREFIX . "petz_petz AS petz
		LEFT JOIN ".TABLE_PREFIX."user AS owner ON (petz.ownerid = owner.userid)
		WHERE petz.name LIKE '".$vbulletin->db->escape_string($op)."'
		ORDER BY $xcon
	");
	$searchresults=$vbulletin->db->num_rows($pets);
	if ($searchresults!=0) {
		while ($pet=$vbulletin->db->fetch_array($pets)) {
			$pet['name']=stripslashes($pet['name']);
			$pet['owner']=stripslashes($pet['owner']);
			if ($pet['dead']==1) {
				$pet['age']=$vbphrase['petz_dead'];
			} else {
				$pet['age']=intval((TIMENOW-$pet['dob']) / 86400);
			}
			$pet['type']=ucfirst($pet['type']);
			if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
				$bgcolor = "alt2";
			} else {
				$bgcolor = "alt1";
			}
			$nonefound=0;
			eval('$petz_results_bit .= "' . fetch_template('petz_pet_bit') . '";');
		}
	} else {
		$nonefound=1;
		eval('$petz_results_bit = "' . fetch_template('petz_pet_bit') . '";');
	}	
}
eval('$petzbody = "' . fetch_template('petz_search') . '";');
// Cleanup
unset($pets, $pet);
?>