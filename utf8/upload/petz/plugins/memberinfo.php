<?php
/*******************************************\
*	iPeeps [vb]		  	PLUGIN: memberinfo	*
*	Version: 1.0.0	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if ($vbulletin->options['petz_on']==1) {
	$petz = $vbulletin->db->query_read("SELECT id,type,name,ownerid,gender,color,moral,dob,hunger,health,mhealth,level FROM " . TABLE_PREFIX . "petz_petz WHERE dead=0 AND ownerid=".$userinfo['userid']."");
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
		if ($pet['health']!=0) {
			$health=($pet['health']/$pet['mhealth'])*100;
			$pet['health']=($health/100)*65;
			$pet['health']=round($pet['health'], 0);
		}
		if ($pet['health']<1) {
		 	$pet['health']=0;
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
		eval('$userinfo[\'petz\'] .= "' . fetch_template('petz_view') . '";');
	}

	$items = $vbulletin->db->query_read("
		SELECT inventory.*,inventory.id AS iid, inventory.itemid AS itemid, inventory.userid AS userid, item.id AS id, item.name As name, item.description AS description, item.cost AS cost, item.image AS image, item.moral AS moral, item.hunger AS hunger, item.health AS health, item.special AS special
		FROM ".TABLE_PREFIX."petz_inventory AS inventory
		LEFT JOIN ".TABLE_PREFIX."petz_items AS item ON (inventory.itemid = item.id)
		WHERE userid = '".$userinfo['userid']."'
		ORDER BY inventory.id ASC
		LIMIT ".$vbulletin->options['petz_maxitems']."");
	$totalitems=$db->num_rows($items);
	if ($totalitems!=0) {
		$noitems=0;
		while ($item=$vbulletin->db->fetch_array($items)) {
			if ($item['hunger']!=0) { $item['effect'].=" Голод: ".$item['hunger']."\n"; }
			if (($item['effect']!="") AND ($item['health']!=0)) { $item['effect'].="<br>\n"; }
			if ($item['health']!=0) { $item['effect'].=" Здоровье: ".$item['health']."\n"; }
			if (($item['effect']!="") AND ($item['moral']!=0)) { $item['effect'].="<br>\n"; }
			if ($item['moral']!=0) { $item['effect'].=" Характер: ".$item['moral']; }
			if (($item['effect']!="") AND ($item['special']!=0)) { $item['effect'].="<br>\n"; }
			if ($item['special']==1) { $item['effect'] .= "Изменяет имя <br />\n"; }
			if ($item['special']==2) { $item['effect'] .= "Изменяет цвет <br />\n"; }
			if ($item['special']==3) { $item['effect'] .= "Смертельно <br />\n";; }
			$item['value']=round($item['cost']*$vbulletin->options['petz_taxsell']/100, 0);
			$item['value']=$item['cost']-$item['value'];
			$item['name']=stripslashes($item['name']);
			$item['description']=stripslashes($item['description']);
			$item['hasstock']=0;
			$item['canuse']=0;
			$item['canbuy']=0;
			$item['cantbuy']=0;
			$item['cansell']=0;
			$item['cansteal']=1;
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
}
?>