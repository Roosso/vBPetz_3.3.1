<?php
/*******************************************\
*	P3tz [vb]		  	File: cron.php		*
*	Version: 3.3.1	   	Licensed			*
********************************************/

error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db)) {
	exit;
}
if ($vbulletin->options['petz_on']==1) {
	/* Best Before Dates */
	$bbdate = TIMENOW - ($vbulletin->options['petz_bbdate'] + 60);
	$batxdate = TIMENOW - ($vbulletin->options['petz_bexpire'] + 60);
	/* The Rest */
	
		// Evolve the Mature Eggs
		$eggs = $vbulletin->db->query_read("SELECT pet.id AS id, mom.type AS momtype, dad.type AS dadtype
		FROM " . TABLE_PREFIX . "petz_petz AS pet
		LEFT JOIN ".TABLE_PREFIX."petz_petz AS mom ON (pet.mother = mom.id)
		LEFT JOIN ".TABLE_PREFIX."petz_petz AS dad ON (pet.father = dad.id)
		WHERE pet.dead=0 AND pet.type='egg' AND pet.dob<$bbdate");
		while ($egg=$vbulletin->db->fetch_array($eggs)) {
			if ($egg['momtype'] != "") {
				$pet['type'] = $egg['momtype'];
			} elseif ($egg['dadtype']!="") {
				$pet['type'] = $egg['dadtype'];
			}
			if ($pet['type']!="") {
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET type='".$pet['type']."', dob=".TIMENOW."
				WHERE id=".$egg[id]."");
			} else { // shouldn't happen.
				if (rand(0,1) == 0) {
					$psex = "Female";
				} else {
					$psex = "Male";
				}
				$defq = $vbulletin->db->query_read("SELECT
				type, health, strength, defence, agility FROM " . TABLE_PREFIX . "petz_default WHERE type!='egg'");
				$defi = $vbulletin->db->num_rows($defq);
				$score = rand(0,$defi);
				$arraycount = 0;
				while ($deft = $vbulletin->db->fetch_array($defq)) {
					if($arraycount == $score){
						$def['type'] = $deft['type'];
						$def['health'] = $deft['health'];
						$def['strength'] = $deft['strength'];
						$def['defence'] = $deft['defence'];
						$def['agility'] = $deft['agility'];
					}
					$arraycount++;
				}
				$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET type='".$def['type']."', gender='".$psex."',
				dob=".TIMENOW.", health=".$def['health'].", mhealth=".$def['health'].", strength=".$def['strength'].",
				defence=".$def['defence'].", agility=".$def['agility']." WHERE id=".$egg['id']."");
			}
		}
		// Angel of Death
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET dead=1, health=0 WHERE health=0 AND care=0 AND dead!=1");
		// Hunger
		$vbulletin->db->query_write("UPDATE " .
		TABLE_PREFIX . "petz_petz SET hunger=hunger+".mt_rand(0,$vbulletin->options['petz_hunger'])."
		WHERE type!='egg' AND care=0 AND dead=0 AND hunger!=100");
		// Malnurition
		$vbulletin->db->query_write("UPDATE " .
		TABLE_PREFIX . "petz_petz SET health=health-".mt_rand(0,$vbulletin->options['petz_malnutrition'])."
		WHERE type!='egg' AND care=0 AND dead=0 AND hunger=100");
		// Limit Fix
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET hunger=100 WHERE hunger>100");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET health=0 WHERE health<0");
		// Restock
		if ($vbulletin->options['petz_stock'] == 1) {
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_items SET stock=restock WHERE stock=0 AND restock>0");
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_heal SET stock=restock WHERE stock=0 AND restock>0");
		}
		
		// Clear Lobby
		$arenas = $vbulletin->db->query_read("SELECT id, type FROM " . TABLE_PREFIX . "petz_battle WHERE round=0 AND created<$batxdate");
		while ($arena = $vbulletin->db->fetch_array($arenas)) {
			if($arena['type'] == 3){
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_guildwar WHERE id=$arena[id]");
			}
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battle WHERE id=$arena[id]");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_gamble WHERE bid=$arena[id]");
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET battle=0 WHERE battle=$arena[id]");
		}
		
		//#################
		//#	Clear trash
		//#################
		
		// var $trashtime = expire time for battle + 1 week;
		$trashtime = $batxdate - 604800;
		
		// clear old logs battle chat and battle log
		$bchats = $vbulletin->db->query_write("SELECT id FROM " . TABLE_PREFIX . "petz_battle WHERE created<'{$trashtime}' AND type!='1'");
		while($row = $vbulletin->db->fetch_array($bchats))
		{
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battlechat WHERE battleid='".$row['id']."'");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battlelog WHERE battleid='".$row['id']."'");
			
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET battle='0' WHERE battle='".$row['id']."'");
		}
		
		// clear old battle
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battle WHERE created<'{$trashtime}' AND type!='1'");
		
		// Log
		log_cron_action('Обслуживание питомцев', $nextitem);

}
?>