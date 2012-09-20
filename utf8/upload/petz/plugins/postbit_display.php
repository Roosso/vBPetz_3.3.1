<?php
/*******************************************\
*	P3tz [vb]		  	PLUGIN: postbit_dis	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

global $petzcache, $vbulletin;
if (!is_object($vbulletin->db)) {
	exit;
}
if ($vbulletin->options['petz_on'] == 1) {
	if (isset($petzcache)) {
		foreach ((array)$petzcache as $userid => $petz) {
			if ($userid == $this->post['userid']) {
				foreach ((array)$petz as $key => $pet)
				{
					eval('$this->post[\'petz\'] .= "' . fetch_template('petz_postbit') . '";');
				}
				continue;
			}
		}
	} elseif ($this->post['userid'] > 0) {
		$petz = $vbulletin->db->query_read("SELECT id,type,name,ownerid,gender,color,moral,dob,hunger,health,mhealth,level
		FROM " . TABLE_PREFIX . "petz_petz WHERE dead=0 AND ownerid='".$this->post['userid']."'");
		while ($pet = $vbulletin->db->fetch_array($petz)){
			$pet['name'] = stripslashes($pet['name']);
			$pet['age'] = intval((TIMENOW-$pet['dob']) / 86400);
			$pcv = explode("-", $pet['color']);
			$pet['Rpcv'] = $pcv[0];
			$pet['Gpcv'] = $pcv[1];
			$pet['Bpcv'] = $pcv[2];
			if ($pet['moral'] < -50) {
				$pet['moral'] = "Злой";
			} elseif ($pet['moral'] > 50) {
				$pet['moral'] = "Хороший";
			} else {
				$pet['moral'] = "Найтральный";
			}
			if ($pet['health']!=0) {
				$health=($pet['health']/$pet['mhealth'])*100;
				$pet['health']=($health/100)*65;
				$pet['health']=round($pet['health'], 0);
			}
			if ($pet['health']<1) {
			 	$pet['health']=0;
			}
			if ($health<20) {
				$pet['status'] = "Ранен";
			} elseif ($health<50) {
				$pet['status'] = "Болен";
			} elseif ($pet['hunger']>50) {
				$pet['status'] = "Голодный";
			} else {
				$pet['status'] = "Ok";
			}
			if ($pet['hunger'] == 0) {
				$pet['hunger'] = 0;
			} else {
				$pet['hunger'] = 65/(100/$pet['hunger']);
				$pet['hunger'] = round($pet['hunger'], 0);
			}
			eval('$this->post[\'petz\'] .= "' . fetch_template('petz_postbit') . '";');
		}
	}
}
?>