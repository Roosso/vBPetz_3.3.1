<?php
/*******************************************\
*	P3tz [vb]		  	File: petzadmin.php	*
*	Version: 3.3.1	   	Licensed			*
*********************************************
*	Created by Steve	For use with VB 3.6	*
*********************************************
*		 Online Virtual Petz Hack			*
*		 To be used under License			*
*********************************************
*	Website:			http://www.P3tz.com	*
\*******************************************/

/* Defaults */
error_reporting(E_ALL & ~E_NOTICE);
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'petzadmin');
require_once('./global.php');
log_admin_action();
if (!isset($_REQUEST['do']) || empty($_REQUEST['do'])) {
	$_REQUEST['do'] = "menu";
}

/******************************************************************************************\
|**************************************** P3TZ ADMIN **************************************|
\******************************************************************************************/

/* Admin P3tz */
if ($_REQUEST['do'] == "petz") {
	print_cp_header("P3tz");
	print_form_header('petzadmin', 'upetzedit');
	print_table_header("Питомцы");
	$navnew = '
		<span style="float:right">' .
		construct_link_code($vbphrase['new'], "petzadmin.php?$session[sessionurl]do=upetzadd") .
		'</span>';
	print_description_row("$navnew Пожалуйста укажите id или кличку питомца который Вы хотели бы отредактировать.");
	print_input_row("P3tz id: <dfn>Можете найти его в адресной строке браузера (..&id=XX)</dfn>", 'petid', '');
	print_input_row("P3tz Name: <dfn>Укажите кличку питомца.</dfn>", 'petname', '');
	print_submit_row($vbphrase['edit'], 0);
	print_form_header('petzadmin', 'petzadd');
	print_table_header("Породы питомцев");
	$result_petz = $vbulletin->db->query_read("SELECT id, type, description, cost FROM " . TABLE_PREFIX . "petz_default");
	while ($petz = $vbulletin->db->fetch_array($result_petz)) {
		$petz[type]=stripslashes($petz[type]);
		$petz[description]=stripslashes($petz[description]);
		$petzname="<b>$petz[type]</b> $petz[description] ($petz[cost])";
		$nav = '
		<span style="float:right">' .
		construct_link_code($vbphrase['edit'], "petzadmin.php?$session[sessionurl]do=petzedit&amp;id=$petz[id]") .
		'</span>';
		print_description_row("$nav $petzname");
	}
	print_submit_row('Добавить новую породу питомцев', 0);
	print_cp_footer();
	exit;
}

/* Add / Edit P3tz */
if ($_REQUEST['do'] == "petzedit" OR $_REQUEST['do'] == "petzadd") {
	print_cp_header("P3tz Typez");
	if ($_REQUEST['do'] == "petzadd") {
		print_form_header('petzadmin', 'addpetz');
		print_table_header("Добавить породу");
	} else {
		$typeid=$_GET['id'];
		$type = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_default WHERE id='$typeid'");
		print_form_header('petzadmin', 'editpetz');
		print_table_header("Редактировать породу");
	}
	// START TYPE ROW
	$pets = opendir("./petz/images/petz/");
	while ($icon = readdir($pets)) {
		if(preg_match("/(.swf)/",$icon)) {
			if($icon != '.' || $icon  != '..') {
				$icon=preg_replace("/.swf/", "", $icon);
				if ($icon==$type[type]) {
					$selected="selected";
				} else {
					$selected="";
				}
				$selecticon .= "<option value='".$icon."' ".$selected.">".$icon." ";
			}
		}
	}
	if($type[type]!="") {
		$disabled="disabled";
	}
	print_description_row("
	Порода питомца:
	<select name='type[type]' $disabled>
		$selecticon
	</select>
	<dfn>Это поле должно быть уникальным</dfn>
	<input type='hidden' name='type[id]' value='$type[id]'>
	");
	if($disabled=="disabled"){ 
		print_description_row("<input type='hidden' name='type[type]' value='$type[type]'>");
	}
	// END TYPE ROW
	print_input_row("Описание породы: <dfn>Расскажите немного об особенностях породы питомцев.</dfn>", 'type[description]', "$type[description]");
	print_input_row("Жизнь: <dfn>Укажите начальное значение жизни.</dfn>", 'type[health]', "$type[health]");
	print_input_row("Сила: <dfn>Значение силы на нулевом уровне.</dfn>", 'type[strength]', "$type[strength]");
	print_input_row("Защита: <dfn>Значение ащиты на нулевом уровне.</dfn>", 'type[defence]', "$type[defence]");
	print_input_row("Ловкость: <dfn>Значение ловкости на нулевом уровне.</dfn>", 'type[agility]', "$type[agility]");
	print_input_row("Цена: <dfn>Укажите стоимость породы.</dfn>", 'type[cost]', "$type[cost]");
	if ($_REQUEST['do'] == "petzadd") {
		print_submit_row('Создать новую породу питомцев', 0);
	} else {
		print_input_row("Удаление: <dfn>Для того что бы удалить наберите в строке <b>DELETECONFIRM</b>. Имейте ввиду что данная операция необратима</dfn>", 'type[delete]', '');
		print_submit_row('Сохранить изменения', 0);
	}
	print_cp_footer();
	exit;
}
/* Add / Edit The Pet To DB*/
if ($_POST['do'] == "addpetz" OR $_POST['do'] == "editpetz") {
	// sort vars
	$type = &$_POST['type'];
	$type[health]=eregi_replace("[^0-9]", null, $type[health]);
	$type[strength]=eregi_replace("[^0-9]", null, $type[strength]);
	$type[defence]=eregi_replace("[^0-9]", null, $type[defence]);
	$type[agility]=eregi_replace("[^0-9]", null, $type[agility]);
	$type[description]=$vbulletin->db->escape_string($type[description]);
	// Write to DB
	if ($_POST['do'] == "addpetz") {
		$unique = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "petz_default WHERE type='".$type[type]."'");
		if ($unique[id]<1) {
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_default
			(id,type,description,health,strength,defence,agility,cost)
			VALUES ('','".$type[type]."','".$type[description]."','".$type[health]."','".$type[strength]."','".$type[defence]."','".$type[agility]."','".$type[cost]."')");
		}
	} elseif ($type[delete]=="DELETECONFIRM") {
		if ($type[id]>1) {
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_default WHERE id='".$type[id]."'");
		}
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_default SET type='".$type[type]."', description='".$type[description]."', health='".$type[health]."', strength='".$type[strength]."', defence='".$type[defence]."', agility='".$type[agility]."', cost='".$type[cost]."' WHERE id='".$type[id]."'");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=petz');
	print_stop_message('petz_acp_redirect');
}

/* Add / Edit User P3tz */
if ($_REQUEST['do'] == "upetzedit" OR $_REQUEST['do'] == "upetzadd") {
	print_cp_header("User P3tz");
	if ($_REQUEST['do'] == "upetzadd") {
		print_form_header('petzadmin', 'addupetz');
		print_table_header("Добавить питомца");
	} else {
		$petid=$_POST['petid'];
		if ($petid<1){
			$petid=$_POST['petsid'];
		}
		$petname=$_POST['petname'];
		if ($petid>0) {
			$pet = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_petz WHERE id='$petid'");
		} elseif ($petname!="") {
			$pet = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_petz
			WHERE name LIKE '".$vbulletin->db->escape_string($petname)."'");
		} else {
			// redirect
			define('CP_REDIRECT', 'petzadmin.php?do=petz');
			print_stop_message('petz_acp_redirect');
		}
		if ($pet[id]<1) {
			// redirect
			define('CP_REDIRECT', 'petzadmin.php?do=petz');
			print_stop_message('petz_acp_redirect');
		}
		print_form_header('petzadmin', 'editupetz');
		print_table_header("Редактировать питомца");
	}
	// START TYPE ROW
	$pets = opendir("./petz/images/petz/");
	while ($icon = readdir($pets)) {
		if(preg_match("/(.swf)/",$icon)) {
			if($icon != '.' || $icon  != '..') {
				$icon=preg_replace("/.swf/", "", $icon);
				if ($icon==$pet[type]) {
					$selected="selected";
				} else {
					$selected="";
				}
				$selecticon .= "<option value='".$icon."' ".$selected.">".$icon." ";
			}
		}
	}
	print_description_row("
	Порода питомца:
	<select name='pet[type]'>
		$selecticon
	</select>
	<input type='hidden' name='pet[id]' value='$pet[id]'>
	");
	// END TYPE ROW
	print_input_row("Кличка: <dfn>Имя питомца.</dfn>", 'pet[name]', "$pet[name]");
	print_input_row("ID Владельца: <dfn>Укажите ID владельца питомца.</dfn>", 'pet[ownerid]', "$pet[ownerid]");
	if ($pet[gender]=="Male") { $sexsel0="selected"; } else { $sexsel1="selected"; }
	print_description_row("Пол:
	<select name='pet[gender]'>
		<option value='Male' $sexsel0>Самец
		<option value='Female' $sexsel1>Самочка
	</select>
	");
	print_input_row("Цвет: <dfn>Укажите цвет в палитре RGB (в диапазоне 0-255). Формат: RRR-GGG-BBB (пример: 255-255-255 OR 0-0-0)</dfn>", 'pet[color]', "$pet[color]");
	print_input_row("Характер: <dfn>Значение от -100 до 100.</dfn>", 'pet[moral]', "$pet[moral]");
	print_time_row("Дата Рождения: <dfn>Или по другому дата создания питомца!</dfn>", 'pet[dob]', "$pet[dob]");
	$result_guild = $vbulletin->db->query_read("SELECT id,name FROM " . TABLE_PREFIX . "petz_guild");
	$selectguildreq = "<option value=''>None </option>";
	$selectguildid = "<option value=''>None </option>";
	while ($guild = $vbulletin->db->fetch_array($result_guild)) {
		$guild[name]=stripslashes($guild[name]);
		if ($pet[guildreq]==$guild[id]) {
			$reqselected="selected";
		} else {
			$reqselected="";
		}
		if ($pet[guildid]==$guild[id]) {
			$idselected="selected";
		} else {
			$idselected="";
		}
		$selectguildreq .= "<option value='".$guild[id]."' ".$reqselected.">".$guild[name]."</option>";
		$selectguildid .= "<option value='".$guild[id]."' ".$idselected.">".$guild[name]."</option>";
	}
	print_description_row("Запросы в гильдии:
	<select name='pet[guildreq]'>
		$selectguildreq
	</select>
	<dfn>Укажите Гильдию куда подал заявку на вступление питомец.</dfn>
	");
	print_description_row("Гильдия питомца:
	<select name='pet[guildid]'>
		$selectguildid
	</select>
	<dfn>Здесь указывается в какой гильдии питомец состоит.</dfn>
	");
	print_input_row("Мама: <dfn>Укажите id питомца который является мамой этого питомца.</dfn>", 'pet[mother]', "$pet[mother]");
	print_input_row("Папа: <dfn>Укажите id питомца который является папой этого питомца.</dfn>", 'pet[father]', "$pet[father]");
	print_input_row("Голод: <dfn>Текущее состояние голода (0-100).</dfn>", 'pet[hunger]', "$pet[hunger]");
	print_input_row("Жизнь: <dfn>Текущее состояние жизни.</dfn>", 'pet[health]', "$pet[health]");
	print_input_row("Максимальная жизнь: <dfn>Это параметр жизни на текущем уровне.</dfn>", 'pet[mhealth]', "$pet[mhealth]");
	print_input_row("Сила: <dfn>Это параметр силы на текущем уровне.</dfn>", 'pet[strength]', "$pet[strength]");
	print_input_row("Защита: <dfn>Это параметр защиты на текущем уровне.</dfn>", 'pet[defence]', "$pet[defence]");
	print_input_row("Ловкость: <dfn>Это параметр ловкости на текущем уровне.</dfn>", 'pet[agility]', "$pet[agility]");
	print_input_row("Экспа: <dfn>Боевой опыт для получения уровней. Укажите 100 что бы поднять уровень питомца. Значние указывает от 0 до 100.</dfn>", 'pet[experience]', "$pet[experience]");
	print_input_row("Побед: <dfn>Общее кол-во побед в боях.</dfn>", 'pet[wins]', "$pet[wins]");
	print_input_row("Поражений: <dfn>Общее кол-во поражений в боях.</dfn>", 'pet[losses]', "$pet[losses]");
	print_input_row("Бой: <dfn>Укажите id боя в котором питомец учавствует в данный момент.</dfn>", 'pet[battle]', "$pet[battle]");
	print_time_row("Приют: <dfn>Укажите дату помещения питомца в приют.</dfn>", 'pet[care]', "$pet[care]");
	print_yes_no_row("Смерть: <dfn>Данный питомец мертв?</dfn>", 'pet[dead]', "$pet[dead]");
	print_input_row("Уровень: <dfn>Текущий уровень питомца от 1 до 100</dfn>", 'pet[level]', "$pet[level]");
	print_yes_no_row("Auto Level: <dfn>Automatically adjust stats to match the level?</dfn>", 'pet[autolevel]', "0");
	if ($_REQUEST['do'] == "upetzadd") {
		print_submit_row('Создать питомца', 0);
	} else {
		print_input_row("Удаление: <dfn>Для того что бы удалить наберите в строке <b>DELETECONFIRM</b>. Имейте ввиду что данная операция необратима</dfn>", 'pet[delete]', '');
		print_submit_row('Сохранить изменения', 0);
	}
	print_cp_footer();
	exit;
}
/* Add / Edit The User Pet To DB*/
if ($_POST['do'] == "addupetz" OR $_POST['do'] == "editupetz") {
	// sort vars
	$pet = &$_POST['pet'];
	$pet[ownerid]=eregi_replace("[^0-9]", null, $pet[ownerid]);
	$pcv=explode("-", $pet[color]);
	$pcv[0]=eregi_replace("[^0-9]", null, $pcv[0]);
	$pcv[1]=eregi_replace("[^0-9]", null, $pcv[1]);
	$pcv[2]=eregi_replace("[^0-9]", null, $pcv[2]);
	$pet[color]="$pcv[0]-$pcv[1]-$pcv[2]";
	if ($pet[moral]<0){
		$pet[moral]=eregi_replace("[^0-9]", null, $pet[moral]);
		$pet[moral]=0-$pet[moral];
	} else {
		$pet[moral]=eregi_replace("[^0-9]", null, $pet[moral]);
	}
	$pet[dob]=mktime($pet[dob][hour],$pet[dob][minute],0,$pet[dob][month],$pet[dob][day],$pet[dob][year]);
	if($pet[dob]<1){
		$pet[dob]=TIMENOW;
	}
	if($pet[care][month]==0){
		$pet[care]=0;
	} else {
		$pet[care]=mktime($pet[care][hour],$pet[care][minute],0,$pet[care][month],$pet[care][day],$pet[care][year]);
	}
	$pet[guildreq]=eregi_replace("[^0-9]", null, $pet[guildreq]);
	$pet[guildid]=eregi_replace("[^0-9]", null, $pet[guildid]);
	$pet[mother]=eregi_replace("[^0-9]", null, $pet[mother]);
	$pet[father]=eregi_replace("[^0-9]", null, $pet[father]);
	$pet[hunger]=eregi_replace("[^0-9]", null, $pet[hunger]);
	$pet[health]=eregi_replace("[^0-9]", null, $pet[health]);
	$pet[mhealth]=eregi_replace("[^0-9]", null, $pet[mhealth]);
	$pet[strength]=eregi_replace("[^0-9]", null, $pet[strength]);
	$pet[defence]=eregi_replace("[^0-9]", null, $pet[defence]);
	$pet[agility]=eregi_replace("[^0-9]", null, $pet[agility]);
	$pet[experience]=eregi_replace("[^0-9]", null, $pet[experience]);
	$pet[level]=eregi_replace("[^0-9]", null, $pet[level]);
	$pet[wins]=eregi_replace("[^0-9]", null, $pet[wins]);
	$pet[losses]=eregi_replace("[^0-9]", null, $pet[losses]);
	$pet[battle]=eregi_replace("[^0-9]", null, $pet[battle]);
	$pet[care]=eregi_replace("[^0-9]", null, $pet[care]);
	$pet[dead]=eregi_replace("[^0-9]", null, $pet[dead]);
	$pet[name]=$vbulletin->db->escape_string($pet[name]);
	// levels
	if (($pet[type]!="egg") AND ($pet[autolevel]==1)) {
		$def=$vbulletin->db->query_first("SELECT strength, defence, agility, health FROM " . TABLE_PREFIX . "petz_default
		WHERE type='".$pet[type]."'");
		if(($pet[level]>1) AND ($vbulletin->options['petz_levelup']>0)){
			$pet[strength]=round($def[strength]+($def[strength]/100*$vbulletin->options['petz_levelup']*($pet[level]-1)),0);
			$pet[defence]=round($def[defence]+($def[defence]/100*$vbulletin->options['petz_levelup']*($pet[level]-1)),0);
			$pet[agility]=round($def[agility]+($def[agility]/100*$vbulletin->options['petz_levelup']*($pet[level]-1)),0);
			$pet[mhealth]=round($def[health]+($def[health]/100*$vbulletin->options['petz_levelup']*($pet[level]-1)),0);
		} else {
			$pet[strength]=$def[strength];
			$pet[defence]=$def[defence];
			$pet[agility]=$def[agility];
			$pet[mhealth]=$def[health];
		}
	}
	// Write to DB
	if ($_POST['do'] == "addupetz") {
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_petz
		(id, type, name, ownerid, gender, color, moral, dob, guildreq, guildid, mother, father, hunger, health, mhealth, strength, defence, agility, experience, level, wins, losses, battle, care, dead) VALUES
		('','".$pet[type]."','".$pet[name]."','".$pet[ownerid]."','".$pet[gender]."','".$pet[color]."','".$pet[moral]."','".$pet[dob]."','".$pet[guildreq]."','".$pet[guildid]."','".$pet[mother]."','".$pet[father]."','".$pet[hunger]."','".$pet[health]."','".$pet[mhealth]."','".$pet[strength]."','".$pet[defence]."','".$pet[agility]."','".$pet[experience]."','".$pet[level]."','".$pet[wins]."','".$pet[losses]."','".$pet[battle]."','".$pet[care]."','".$pet[dead]."')");
	} elseif ($pet[delete]=="DELETECONFIRM") {
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_petz WHERE id='".$pet[id]."'");
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET type='".$pet[type]."', name='".$pet[name]."', ownerid='".$pet[ownerid]."', gender='".$pet[gender]."', color='".$pet[color]."', moral='".$pet[moral]."', dob='".$pet[dob]."', guildreq='".$pet[guildreq]."', guildid='".$pet[guildid]."', mother='".$pet[mother]."', father='".$pet[father]."', hunger='".$pet[hunger]."', health='".$pet[health]."', mhealth='".$pet[mhealth]."', strength='".$pet[strength]."', defence='".$pet[defence]."', agility='".$pet[agility]."', experience='".$pet[experience]."', level='".$pet[level]."', wins='".$pet[wins]."', losses='".$pet[losses]."', battle='".$pet[battle]."', care='".$pet[care]."', dead='".$pet[dead]."' WHERE id='".$pet[id]."'");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=petz');
	print_stop_message('petz_acp_redirect');
}

/******************************************************************************************\
|*************************************** ITEMZ ADMIN **************************************|
\******************************************************************************************/

/* Admin Items */
if ($_GET['do'] == "items") {
	print_cp_header("P3tz Itemz");
	print_form_header('petzadmin', 'itemsadd');
	print_table_header("Предметы");
	$result_item = $vbulletin->db->query_read("SELECT id, name, image, description, cost FROM " . TABLE_PREFIX . "petz_items");
	while ($item = $vbulletin->db->fetch_array($result_item)) {
		$item['name']=stripslashes($item['name']);
		$item['description']=stripslashes($item['description']);
		$itemname="<img src=\"../petz/images/items/$item[image]\" alt=\"$item[description]\" align=\"left\"><b>".$item['name']."</b> ($item[cost])";
		$nav = '
		<span style="float:right">' .
		construct_link_code($vbphrase['edit'], "petzadmin.php?$session[sessionurl]do=itemsedit&amp;id=$item[id]") .
		construct_link_code($vbphrase['delete'], "petzadmin.php?$session[sessionurl]do=itemsdelete&amp;id=$item[id]") .
		'</span>';
		print_description_row("$nav $itemname");
	}
	print_submit_row('Добавить новый предмет', 0);
	print_cp_footer();
	exit;
}

/* Add / Edit Item */
if ($_REQUEST['do'] == "itemsedit" OR $_REQUEST['do'] == "itemsadd") {
	print_cp_header("P3tz Itemz");
	if ($_REQUEST['do'] == "itemsadd") {
		print_form_header('petzadmin', 'additem');
		print_table_header("Добавить предмет");
	} else {
		$itemid=$_GET['id'];
		$item = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_items WHERE id='{$itemid}'");
		print_form_header('petzadmin', 'edititem');
		print_table_header("Редактировать предмет");
	}
	print_input_row("Название товара / предмета: <dfn>Ценник</dfn>", 'item[name]', "$item[name]");
	// START ICON ROW
	if($item[image]==""){
		$item[image]="cake.gif";
	}
	$images = opendir("./petz/images/items/");
	while ($icon = readdir($images)) {
		if(preg_match("/(.gif)/",$icon)) {
			if($icon != '.' || $icon  != '..') {
				if ($icon==$item['image']) {
					$selected="selected";
				} else {
					$selected="";
				}
				$selecticon .= "<option value='".$icon."' ".$selected.">".$icon." ";
			}
		}
	}
	print_description_row("
	<script>
	function show_icon() {
		document.images['showicon'].src = '../petz/images/items/' + document.cpform.itemimage.value;
	}
	</script>
	<img src='../petz/images/items/{$item[image]}' name='showicon' align='right'>
	Иконка товара / предмета:
	<select name='itemimage' onChange='show_icon()'>
		$selecticon
	</select>
	");
	// END ICON ROW
	print_input_row("Описание товара:", 'item[description]', "{$item[description]}");
	// START PET TYPE ROW
	$result_pet = $vbulletin->db->query_read("SELECT type FROM " . TABLE_PREFIX . "petz_default");
	$selecttype = "<option value=''>Все породы ";
	while ($pet = $vbulletin->db->fetch_array($result_pet)) {
		$pet['type']=stripslashes($pet['type']);
		$item['pettype']=stripslashes($item['pettype']);
		if ($pet['type']==$item['pettype']) {
			$selected="selected";
		} else {
			$selected="";
		}
		$selecttype .= "<option value='".$pet['type']."' ".$selected.">".$pet['type']."s only ";
	}
	print_description_row("Укажите породы которым подоходит данный продукт:
	<select name='item[pettype]'>
		$selecttype
	</select>
	<input type='hidden' name='item[id]' value='".$item['id']."'>
	");
	// END PET TYPE ROW
	print_input_row("В наличии: <dfn>Укажите имеющиеся кол-во товара на складе для продажи.</dfn>", 'item[stock]', "$item[stock]");
	print_input_row("Завоз : <dfn>Укажите кол-во товара которое будет довозится по расписанию. Укажите 0 что бы контролировать привоз товаров в ручную.</dfn>", 'item[restock]', "$item[restock]");
	print_input_row("Изменение характера: <dfn>Укажите воздействие на характер питомца. Значение может быть со знаком минус.</dfn>", 'item[moral]', "$item[moral]");
	print_input_row("Изменение голода: <dfn>Укажите значние изменения голода питомца. Значение может быть со знаком минус.</dfn>", 'item[hunger]', "$item[hunger]");
	print_input_row("Изменение здоровья: <dfn>Укажите значние изменения здоровья питомца. Значение может быть со знаком минус.</dfn>", 'item[health]', "$item[health]");
	// START SPECIAL ROW
	if ($item[special]==0) { $sesel0="selected"; }
	if ($item[special]==1) { $sesel1="selected"; }
	if ($item[special]==2) { $sesel2="selected"; }
	if ($item[special]==3) { $sesel3="selected"; }
	print_description_row("Специальный эффект
	<select name='item[special]'>
		<option value='0' $sesel0>Нет эффекта
		<option value='1' $sesel1>Изменить имя питомца 
		<option value='2' $sesel2>Изменить цвет питомца
		<option value='3' $sesel3>Убить питомца
	</select>
	");
	// END SPECIAL ROW
	print_input_row("Цена: <dfn>Оставьте поле пустым для автоматического опрееления цены</dfn>", 'item[cost]', "$item[cost]");
	print_input_row("Срок годности: <dfn>Укажите срок годности продукта в течении которого он может быть использован.</dfn>", 'item[sold]', "$item[sold]");
	print_input_row("Срок возврата: <dfn>Укажите срок в течении которого товар можно вернуть в магазин.</dfn>", 'item[bought]', "$item[bought]");
	if ($_REQUEST['do'] == "itemsadd") {
		print_submit_row('Добавить новый предмет', 0);
	} else {
		print_submit_row('Редактировать предмет', 0);
	}
	print_cp_footer();
	exit;
}
/* Add / Edit The Item To DB */
if ($_POST['do'] == "additem" OR $_POST['do'] == "edititem") {
	// sort vars
	$item = &$_POST['item'];
	$item[image]=$_POST['itemimage'];
	if ($item[moral]<0){
		$item[moral]=eregi_replace("[^0-9]", null, $item[moral]);
		$item[moral]=0-$item[moral];
	} else {
		$item[moral]=eregi_replace("[^0-9]", null, $item[moral]);
	}
	if ($item[hunger]<0){
		$item[hunger]=eregi_replace("[^0-9]", null, $item[hunger]);
		$item[hunger]=0-$item[hunger];
	} else {
		$item[hunger]=eregi_replace("[^0-9]", null, $item[hunger]);
	}
	$item[sold]=eregi_replace("[^0-9]", null, $item[sold]);
	$item[bought]=eregi_replace("[^0-9]", null, $item[bought]);
	$item[stock]=eregi_replace("[^0-9]", null, $item[stock]);
	$item[restock]=eregi_replace("[^0-9]", null, $item[restock]);
	if($item[cost]<1){
		if ($item[moral]<0) { $addm=$item[moral]*-1; } else { $addm=$item[moral]; }
		if ($item[hunger]<0) { $addh=$item[hunger]*-1; } else { $addh=$item[hunger]; }
		$item[cost]=($addm*3) + ($addh*2) + ($item[special]*50);
		if($item[pettype]!="") {
			$item[cost]=$item[cost]-round(($item[cost]/100)*9, 0);
		}
	}
	$item[name]=$vbulletin->db->escape_string($item[name]);
	$item[description]=$vbulletin->db->escape_string($item[description]);
	// Write to DB
	if ($_POST['do'] == "additem") {
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_items (id,name,description,image,hunger,moral,health,special,stock,restock,cost,sold,bought,pettype)
		VALUES ('','".$item['name']."','".$item['description']."','".$item['image']."','".$item['hunger']."','".$item['moral']."','".$item['health']."','".$item['special']."','".$item['stock']."','".$item['restock']."','".$item['cost']."','".$item['sold']."','".$item['bought']."','".$item['pettype']."')");
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_items SET name='".$item['name']."', description='".$item['description']."', image='".$item['image']."', hunger='".$item['hunger']."', moral='".$item['moral']."', health='".$item['health']."', special='".$item['special']."', stock='".$item['stock']."', restock='".$item['restock']."', cost='".$item['cost']."', sold='".$item['sold']."', bought='".$item['bought']."', pettype='".$item['pettype']."' WHERE id='".$item['id']."'");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=items');
	print_stop_message('petz_acp_redirect');
}

/* Delete The Item */
if ($_GET['do'] == "itemsdelete") {
	// sort vars
	$itemid=$_GET['id'];
	// Delete from DB
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_items WHERE id='$itemid'");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_inventory WHERE itemid='$itemid'");
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=items');
	print_stop_message('petz_acp_redirect');
}

/******************************************************************************************\
|************************************** vITEMZ ADMIN **************************************|
\******************************************************************************************/

/* Admin Vet Items */
if ($_GET['do'] == "heal") {
	print_cp_header("P3tz Vet Itemz");
	print_form_header('petzadmin', 'vitemsadd');
	print_table_header("Вет продукты");
	$result_item = $vbulletin->db->query_read("SELECT id, name, image, description, cost FROM " . TABLE_PREFIX . "petz_heal");
	while ($item = $vbulletin->db->fetch_array($result_item)) {
		$item[name]=stripslashes($item[name]);
		$item[description]=stripslashes($item[description]);
		$itemname="<img src=\"../petz/images/items/$item[image]\" alt=\"$item[description]\" align=\"left\"><b>$item[name]</b> ($item[cost])";
		$nav = '
		<span style="float:right">' .
		construct_link_code($vbphrase['edit'], "petzadmin.php?$session[sessionurl]do=vitemsedit&amp;id=$item[id]") .
		construct_link_code($vbphrase['delete'], "petzadmin.php?$session[sessionurl]do=vitemsdelete&amp;id=$item[id]") .
		'</span>';
		print_description_row("$nav $itemname");
	}
	print_submit_row('Добавить новый вет предмет', 0);
	print_cp_footer();
	exit;
}

/* Add / Edit Vet Item */
if ($_REQUEST['do'] == "vitemsedit" OR $_REQUEST['do'] == "vitemsadd") {
	print_cp_header("P3tz Vet Itemz");
	if ($_REQUEST['do'] == "vitemsadd") {
		print_form_header('petzadmin', 'vadditem');
		print_table_header("Добавить вет продукты");
	} else {
		$itemid=$_GET['id'];
		$item = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_heal WHERE id='$itemid'");
		print_form_header('petzadmin', 'vedititem');
		print_table_header("Редактировать вет продукты");
	}
	print_input_row("Item Name: <dfn>The name of this item</dfn>", 'item[name]', "$item[name]");
	// START ICON ROW
	if($item[image]==""){
		$item[image]="injection.gif";
	}
	$images = opendir("./petz/images/items/");
	while ($icon = readdir($images)) {
		if(preg_match("/(.gif)/",$icon)) {
			if($icon != '.' || $icon  != '..') {
				if ($icon==$item[image]) {
					$selected="selected";
				} else {
					$selected="";
				}
				$selecticon .= "<option value='".$icon."' ".$selected.">".$icon." ";
			}
		}
	}
	print_description_row("
	<script>
	function show_icon() {
		document.images['showicon'].src = '../petz/images/items/' + document.cpform.itemimage.value;
	}
	</script>
	<img src='../petz/images/items/$item[image]' name='showicon' align='right'>
	Item Icon:
	<select name='itemimage' onChange='show_icon()'>
		$selecticon
	</select>
	<input type='hidden' name='item[id]' value='$item[id]'>
	");
	// END ICON ROW
	print_input_row("Item Description: <dfn>The description for the item</dfn>", 'item[description]', "$item[description]");
	print_input_row("Stock: <dfn>If stock is on in the settings this sets the stock</dfn>", 'item[stock]', "$item[stock]");
	print_input_row("Re-stock: <dfn>When stock levels reach 0 this is the amount to restock (cron). Leave 0 for no restock.</dfn>", 'item[restock]', "$item[restock]");
	print_input_row("Change to Moral: <dfn>Leave blank for no effect (negative or posative)</dfn>", 'item[moral]', "$item[moral]");
	print_input_row("Change to Health: <dfn>Leave blank for no effect (usually positive)</dfn>", 'item[health]', "$item[health]");
	print_input_row("Price: <dfn>Leave blank to auto calculate</dfn>", 'item[cost]', "$item[cost]");
	if ($_REQUEST['do'] == "vitemsadd") {
		print_submit_row('Добавить новый вет предмет', 0);
	} else {
		print_submit_row('Редактировать вет предмет', 0);
	}
	print_cp_footer();
	exit;
}
/* Add / Edit The Item To DB*/
if ($_POST['do'] == "vadditem" OR $_POST['do'] == "vedititem") {
	// sort vars
	$item = &$_POST['item'];
	$item[image]=$_POST['itemimage'];
	if ($item[moral]<0){
		$item[moral]=eregi_replace("[^0-9]", null, $item[moral]);
		$item[moral]=0-$item[moral];
	} else {
		$item[moral]=eregi_replace("[^0-9]", null, $item[moral]);
	}
	if ($item[health]<0){
		$item[health]=eregi_replace("[^0-9]", null, $item[health]);
		$item[health]=0-$item[health];
	} else {
		$item[health]=eregi_replace("[^0-9]", null, $item[health]);
	}
	$item[stock]=eregi_replace("[^0-9]", null, $item[stock]);
	$item[restock]=eregi_replace("[^0-9]", null, $item[restock]);
	if($item[cost]<1){
		if ($item[moral]<0) { $addm=$item[moral]*-1; } else { $addm=$item[moral]; }
		if ($item[health]<0) { $addh=$item[health]*-1; } else { $addh=$item[health]; }
		$item[cost]=($addm*2) + ($addh*2);
	}
	$item[name]=$vbulletin->db->escape_string($item[name]);
	$item[description]=$vbulletin->db->escape_string($item[description]);
	// Write to DB
	if ($_POST['do'] == "vadditem") {
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_heal (id,name,description,image,health,moral,stock,restock,cost)
		VALUES ('','".$item[name]."','".$item[description]."','".$item[image]."','".$item[health]."','".$item[moral]."','".$item[stock]."','".$item[restock]."','".$item[cost]."')");
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_heal SET name='".$item[name]."', description='".$item[description]."', image='".$item[image]."', health='".$item[health]."', moral='".$item[moral]."', stock='".$item[stock]."', restock='".$item[restock]."', cost='".$item[cost]."' WHERE id='".$item[id]."'");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=heal');
	print_stop_message('petz_acp_redirect');
}

/* Delete The Item */
if ($_GET['do'] == "vitemsdelete") {
	// sort vars
	$itemid=$_GET['id'];
	// Delete from DB
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_heal WHERE id='$itemid'");
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=heal');
	print_stop_message('petz_acp_redirect');
}

/******************************************************************************************\
|*************************************** LOGZ ADMIN ***************************************|
\******************************************************************************************/

/* Logs Admin */
if ($_GET['do'] == "logs") {
	print_cp_header("P3tz Logz");
	print_form_header('petzadmin', 'viewlogs');
	print_table_header("Логи Питомцев");
	print_description_row("Выберите лог, который хотите посмотреть.");
	$files = opendir("./petz/core/");
	while ($file = readdir($files)) {
		if(preg_match("/(.php)/",$file)) {
			if($file != '.' || $file  != '..') {
				$file=preg_replace("/.php/", "", $file);
				$selectdo .= "<option value='".$file."'>".$file." </option>";
			}
		}
	}
	print_description_row("
	Log Area:
	<select name='view'>
		<option value=''>All </option>
		$selectdo
	</select>
	");
	print_submit_row($vbphrase['view'], 0);
	print_form_header('petzadmin', 'clearlogs');
	print_table_header("УДАЛИТЬ ВСЕ ЛОГИ");
	print_description_row("Эта опция удаляет все логи из базы данных.");
	print_submit_row("Очистить логи", 0);
	print_cp_footer();
	exit;
}

/* View Logs */
if ($_POST['do'] == "viewlogs") {
	print_cp_header("Petz Logs");
	print_form_header('petzadmin', 'deletelogs');
	print_table_header("Логи");
	// sort vars
	if($_POST['view']!="") {
		$lcon="WHERE pdo='".$_POST['view']."'";
	}
	// get logs
	$result_logs = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "petz_log $lcon");
	$results = $vbulletin->db->num_rows($result_logs);
	if ($results!=0) {
		while ($log = $vbulletin->db->fetch_array($result_logs)) {
			$log[pdo]=stripslashes($log[pdo]);
			$log[pid]=stripslashes($log[pid]);
			$log[pop]=stripslashes($log[pop]);
			$log[extra]=stripslashes($log[extra]);
			$log[when]=vbdate($vbulletin->options['logdateformat'],$log[logtime]);
			print_description_row("<span style=\"float:right\">Delete: <input type=\"radio\" name=\"logid\" value=\"$log[id]\"></span> <b>$log[pdo]</b>: (id: $log[pid])(op: $log[pop]) (Userid: $log[userid])<br />When: $log[when] <dfn>Extra: $log[extra]</dfn>");
		}
		print_submit_row("Удалить лог", 0);
	} else {
	print_description_row("There are no ".$_POST['view']." logs");
	}
	print_table_footer();
	print_cp_footer();
	exit;
}

/* Delete Logs */
if ($_POST['do'] == "deletelogs") {
	// sort vars
	$logid=$_POST['logid'];
	// do
	if($logid>0){
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_log WHERE id=$logid ");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=logs');
	print_stop_message('petz_acp_redirect');
}

/* Clear Logs */
if ($_POST['do'] == "clearlogs") {
	//do
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_log ");
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=logs');
	print_stop_message('petz_acp_redirect');
}

/******************************************************************************************\
|************************************ ORPHANZ ADMIN ***************************************|
\******************************************************************************************/

/* Adoption Center Admin */
if ($_GET['do'] == "adopt") {
	print_cp_header("P3tz Orphanz");
	print_form_header('petzadmin', 'editadopt');
	print_table_header("Магазин питомцев");
	$pets = $vbulletin->db->query_read("SELECT pet.id,pet.name FROM " . TABLE_PREFIX . "petz_adopt AS adopt
	LEFT JOIN ".TABLE_PREFIX."petz_petz AS pet ON (pet.id = adopt.petid)
	");
	$totalorphanz = $vbulletin->db->num_rows($pets);
	print_description_row("There are <b>$totalorphanz</b> P3tz in the Orphanage. <dfn>Either enter a pet id or select a pet.</dfn>");
	if ($totalorphanz>0) {
		print_input_row("Edit Orphan Ad: <dfn>Enter the pet id of the orphan you would like to change.<br /> The pet id can be found in the Address Bar of a pet profile (..&id=XX)</dfn>", 'petid', '');
		$select = "<option value=''>Select a Pet </option>";
		while ($pet = $vbulletin->db->fetch_array($pets)) {
			$pet[name]=stripslashes($pet[name]);
			$select .= "<option value='".$pet[id]."' ".$selected.">".$pet[name]."</option>";
		}
		print_description_row("Select Orphan:
		<select name='petsid'>
			$select
		</select>
		");
		print_submit_row($vbphrase['view'], 0);
	} else {
		print_table_footer();
	}
	print_cp_footer();
	exit;
}
/* Edit Orphan */
if ($_POST['do'] == "editadopt") {
	print_cp_header("P3tz Orphanz");
	$petid=$_POST['petid'];
	if ($petid<1) {
		$petid=$_POST['petsid'];
	}
	if ($petid>0) {
		$adopt = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_adopt WHERE petid='$petid'");
	} else {
		// redirect
		define('CP_REDIRECT', 'petzadmin.php?do=adopt');
		print_stop_message('petz_acp_redirect');
	}
	if ($adopt[id]<1) {
		// redirect
		define('CP_REDIRECT', 'petzadmin.php?do=adopt');
		print_stop_message('petz_acp_redirect');
	}
	print_form_header('petzadmin', 'adoptedit');
	print_table_header("Редактировать приют");
	print_input_row("User: <dfn>Should be the same as the pet owner id. (Don't Change)</dfn>", 'adopt[userid]', "$adopt[userid]");
	print_input_row("Pet: <dfn>The pet id. (Don't Change)</dfn>", 'adopt[petid]', "$adopt[petid]");
	print_input_row("Cost: <dfn>How many points this pet is being sold for. (Tax is set in settings)</dfn>", 'adopt[cost]', "$adopt[cost]");
	print_input_row("Description: <dfn>The short sale description that goes with the orphanage advert.</dfn>", 'adopt[description]', "$adopt[description]");
	print_time_row("Start Date: <dfn>The date this ad was created.</dfn>", 'adopt[upfor]', "$adopt[upfor]");
	print_input_row("Remove: <dfn>To delete this orphan advert type <b>DELETECONFIRM</b>. No further confirmation screens will display.</dfn>", 'adopt[delete]', '');
	print_description_row("<input type='hidden' name='adopt[id]' value='$adopt[id]'>");
	print_submit_row($vbphrase['edit'], 0);
	print_cp_footer();
	exit;
}
/* Edit Orphan DB */
if ($_POST['do'] == "adoptedit") {
	$adopt = &$_POST['adopt'];
	$adopt[userid]=eregi_replace("[^0-9]", null, $adopt[userid]);
	$adopt[petid]=eregi_replace("[^0-9]", null, $adopt[petid]);
	$adopt[upfor]=mktime($adopt[upfor][hour],$adopt[upfor][minute],0,$adopt[upfor][month],$adopt[upfor][day],$adopt[upfor][year]);
	if($adopt[upfor]<1){
		$adopt[upfor]=TIMENOW;
	}
	$adopt[description]=$vbulletin->db->escape_string($adopt[description]);
	if($adopt[delete]=="DELETECONFIRM"){
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_adopt WHERE id='".$adopt[id]."' ");
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_adopt SET userid='".$adopt[userid]."', petid='".$adopt[petid]."', cost='".$adopt[cost]."', description='".$adopt[description]."', upfor='".$adopt[upfor]."' WHERE id='".$adopt[id]."'");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=adopt');
	print_stop_message('petz_acp_redirect');
}

/******************************************************************************************\
|************************************ KENNELZ ADMIN ***************************************|
\******************************************************************************************/

/* Day Care Admin */
if ($_GET['do'] == "care") {
	print_cp_header("P3tz Kennelz");
	print_form_header('petzadmin', 'upetzedit');
	print_table_header("Сиротки");
	$pets = $vbulletin->db->query_read("SELECT id,name FROM " . TABLE_PREFIX . "petz_petz WHERE care!=0");
	$totalpetz = $vbulletin->db->num_rows($pets);
	print_description_row("There are <b>$totalpetz</b> P3tz in Kennels. <dfn>Either enter a pet id or select a pet.</dfn>");
	if ($totalpetz>0){
		print_input_row("Edit Pet: <dfn>Enter the pet id of the pet you would like to change.<br /> The pet id can be found in the Address Bar of a pet profile (..&id=XX)</dfn>", 'petid', '');
		$select = "<option value=''>Select a Pet </option>";
		while ($pet = $vbulletin->db->fetch_array($pets)) {
			$pet[name]=stripslashes($pet[name]);
			$select .= "<option value='".$pet[id]."' ".$selected.">".$pet[name]."</option>";
		}
		print_description_row("Select Pet:
		<select name='petsid'>
			$select
		</select>
		");
		print_submit_row($vbphrase['view'], 0);
	} else {
		print_table_footer();
	}
	print_cp_footer();
	exit;
}

/******************************************************************************************\
|************************************* GRAVEZ ADMIN ***************************************|
\******************************************************************************************/

/* Graveyard Admin */
if ($_GET['do'] == "graves") {
	print_cp_header("P3tz Gravez");
	print_form_header('petzadmin', 'editgrave');
	print_table_header("Кладбище");
	$pets = $vbulletin->db->query_read("SELECT pet.id,pet.name FROM " . TABLE_PREFIX . "petz_grave AS grave
	LEFT JOIN ".TABLE_PREFIX."petz_petz AS pet ON (pet.id = grave.petid)
	");
	$totalgravez = $vbulletin->db->num_rows($pets);
	print_description_row("There are <b>$totalgravez</b> P3tz Gravez in the Graveyard. <dfn>Either enter a pet id or select a pet.</dfn>");
	if ($totalgravez>0) {
		print_input_row("Edit Grave: <dfn>Enter the pet id of the grave you would like to change.<br /> The pet id can be found in the Address Bar of a pet profile (..&id=XX)</dfn>", 'petid', '');
		$select = "<option value=''>Select a Pet </option>";
		while ($pet = $vbulletin->db->fetch_array($pets)) {
			$pet[name]=stripslashes($pet[name]);
			$select .= "<option value='".$pet[id]."' ".$selected.">".$pet[name]."</option>";
		}
		print_description_row("Select Pet:
		<select name='petsid'>
			$select
		</select>
		");
		print_submit_row($vbphrase['view'], 0);
	} else {
		print_table_footer();
	}
	print_cp_footer();
	exit;
}
/* Edit Grave */
if ($_POST['do'] == "editgrave") {
	print_cp_header("P3tz Gravez");
	$petid=$_POST['petid'];
	if ($petid<1) {
		$petid=$_POST['petsid'];
	}
	if ($petid>0) {
		$grave = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_grave WHERE petid='$petid'");
	} else {
		// redirect
		define('CP_REDIRECT', 'petzadmin.php?do=graves');
		print_stop_message('petz_acp_redirect');
	}
	if ($grave[id]<1) {
		// redirect
		define('CP_REDIRECT', 'petzadmin.php?do=graves');
		print_stop_message('petz_acp_redirect');
	}
	print_form_header('petzadmin', 'graveedit');
	print_table_header("Редактировать могилку");
	print_input_row("User: <dfn>Should be the same as the pet owner id. (Don't Change)</dfn>", 'grave[userid]', "$grave[userid]");
	print_input_row("Pet: <dfn>The pet id. (Don't Change)</dfn>", 'grave[petid]', "$grave[petid]");
	// START ICON ROW
	$images = opendir("./petz/images/gravestones/");
	while ($icon = readdir($images)) {
		if(preg_match("/(.gif)/",$icon)) {
			if($icon != '.' || $icon  != '..') {
				$icon=preg_replace("/.gif/", "", $icon);
				if ($icon==$grave[stone]) {
					$selected="selected";
				} else {
					$selected="";
				}
				$selecticon .= "<option value='".$icon."' ".$selected.">".$icon." ";
			}
		}
	}
	print_description_row("
	<script>
	function show_icon() {
		document.images['showicon'].src = '../petz/images/gravestones/' + document.cpform.gravestone.value + '.gif';
	}
	</script>
	<img src='../petz/images/gravestones/$grave[stone].gif' name='showicon' align='right'>
	Grave Stone:
	<select name='gravestone' onChange='show_icon()'>
		$selecticon
	</select>
	<input type='hidden' name='grave[id]' value='$grave[id]'>
	");
	// END ICON ROW
	print_input_row("Memorial: <dfn>The message that accompanies the grave.</dfn>", 'grave[memorial]', "$grave[memorial]");
	print_time_row("Burried: <dfn>The date this pet was burried.</dfn>", 'grave[burried]', "$grave[burried]");
	print_input_row("Remove: <dfn>To delete this grave type <b>DELETECONFIRM</b>. No further confirmation screens will display.</dfn>", 'grave[delete]', '');
	print_submit_row($vbphrase['edit'], 0);
	print_cp_footer();
	exit;
}
/* Edit Grave DB */
if ($_POST['do'] == "graveedit") {
	$grave = &$_POST['grave'];
	$gravestone=$_POST['gravestone'];
	$grave[userid]=eregi_replace("[^0-9]", null, $grave[userid]);
	$grave[petid]=eregi_replace("[^0-9]", null, $grave[petid]);
	$grave[burried]=mktime($grave[burried][hour],$grave[burried][minute],0,$grave[burried][month],$grave[burried][day],$grave[burried][year]);
	if($grave[burried]<1){
		$grave[burried]=TIMENOW;
	}
	$grave[memorial]=$vbulletin->db->escape_string($grave[memorial]);
	if($grave[delete]=="DELETECONFIRM"){
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_grave WHERE id='".$grave[id]."' ");
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_grave SET userid='".$grave[userid]."', petid='".$grave[petid]."', stone='".$gravestone."', memorial='".$grave[memorial]."', burried='".$grave[burried]."' WHERE id='".$grave[id]."'");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=graves');
	print_stop_message('petz_acp_redirect');
}

/******************************************************************************************\
|************************************** GUILDZ ADMIN **************************************|
\******************************************************************************************/

/* Admin Guilds */
if ($_GET['do'] == "guilds") {
	print_cp_header("P3tz Guildz");
	print_form_header('petzadmin', 'guildedit');
	print_table_header("Гильдии");
	$guilds = $vbulletin->db->query_read("SELECT id, name, image, description FROM " . TABLE_PREFIX . "petz_guild");
	while ($guild = $vbulletin->db->fetch_array($guilds)) {
		$guild[name]=stripslashes($guild[name]);
		$guild[description]=stripslashes($guild[description]);
		$itemname="<img src=\"../petz/images/guilds/$guild[image].gif\" alt=\"$guild[description]\" align=\"left\"><b>$guild[name]</b>";
		print_description_row("<span style=\"float:right\">Edit: <input type=\"radio\" name=\"guildid\" value=\"$guild[id]\"></span> $itemname");
	}
	print_submit_row($vbphrase['edit'], 0);
	print_cp_footer();
	exit;
}

/* Edit Guild */
if ($_REQUEST['do'] == "guildedit") {
	print_cp_header("P3tz Guildz");
	$guildid=$_POST['guildid'];
	$guild = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_guild WHERE id='$guildid'");
	if ($guild[id]<1) {
		// redirect
		define('CP_REDIRECT', 'petzadmin.php?do=guilds');
		print_stop_message('petz_acp_redirect');
	}
	print_form_header('petzadmin', 'editguild');
	print_table_header("Редактировать гильдию");
	print_input_row("Guild Name: <dfn>The name of this guild.</dfn>", 'guild[name]', "$guild[name]");
	print_input_row("Owner: <dfn>The user id of the guild owner.</dfn>", 'guild[userid]', "$guild[userid]");
	// START ICON ROW
	$images = opendir("./petz/images/guilds/");
	while ($icon = readdir($images)) {
		if(preg_match("/(.gif)/",$icon)) {
			if($icon != '.' || $icon  != '..') {
				$icon=preg_replace("/.gif/", "", $icon);
				if ($icon==$guild[image]) {
					$selected="selected";
				} else {
					$selected="";
				}
				$selecticon .= "<option value='".$icon."' ".$selected.">".$icon." ";
			}
		}
	}
	print_description_row("
	<script>
	function show_icon() {
		document.images['showicon'].src = '../petz/images/guilds/' + document.cpform.guildimage.value + '.gif';
	}
	</script>
	<img src='../petz/images/guilds/$guild[image].gif' name='showicon' align='right'>
	Guild Logo:
	<select name='guildimage' onChange='show_icon()'>
		$selecticon
	</select>
	<input type='hidden' name='guild[id]' value='$guild[id]'>
	");
	// END ICON ROW
	print_input_row("Guild Description: <dfn>A brief description of this guild.</dfn>", 'guild[description]', "$guild[description]");
	print_input_row("Battle Wins: <dfn>Total number of battle wins.</dfn>", 'guild[wins]', "$guild[wins]");
	print_input_row("Battles: <dfn>Total number of battles.</dfn>", 'guild[battles]', "$guild[battles]");
	print_input_row("Remove: <dfn>To delete this guild type <b>DELETECONFIRM</b>. No further confirmation screens will display.<br>This will also remove all p3tz from this guild.</dfn>", 'guild[delete]', '');
	print_submit_row($vbphrase['edit'], 0);
	print_cp_footer();
	exit;
}
/* Edit Guild DB */
if ($_POST['do'] == "editguild") {
	$guild = &$_POST['guild'];
	$guildimage=$_POST['guildimage'];
	$guild[userid]=eregi_replace("[^0-9]", null, $guild[userid]);
	$guild[wins]=eregi_replace("[^0-9]", null, $guild[wins]);
	$guild[losses]=eregi_replace("[^0-9]", null, $guild[losses]);
	$guild[name]=$vbulletin->db->escape_string($guild[name]);
	$guild[description]=$vbulletin->db->escape_string($guild[description]);
	if($guild[delete]=="DELETECONFIRM"){
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_guild WHERE id='".$guild[id]."' ");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET guildid=0 WHERE guildid=".$guild[id]."");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET guildreq=0 WHERE guildreq=".$guild[id]."");
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_guild SET name='".$guild[name]."', description='".$guild[description]."', image='".$guildimage."', userid='".$guild[userid]."', wins='".$guild[wins]."', battles='".$guild[battles]."' WHERE id='".$guild[id]."'");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=guilds');
	print_stop_message('petz_acp_redirect');
}

/******************************************************************************************\
|************************************** SPELLZ ADMIN **************************************|
\******************************************************************************************/

/* Admin Spells */
if ($_GET['do'] == "spells") {
	print_cp_header("P3tz Spellz");
	print_form_header('petzadmin', 'spelladd');
	print_table_header("Магия");
	$spells = $vbulletin->db->query_read("SELECT id,name,description,element,damage,moral,cost,chance
	FROM " . TABLE_PREFIX . "petz_spells");
	while ($spell = $vbulletin->db->fetch_array($spells)) {
		$spell[name]=stripslashes($spell[name]);
		$spell[description]=stripslashes($spell[description]);
		$spellname="<img src=\"../petz/images/spells/$spell[element].gif\" alt=\"".ucfirst($spell[element])."\" align=\"left\" />
		<b>$spell[name]</b>-<i>$spell[description]</i> <dfn>Damage: $spell[damage]% | Moral: $spell[moral] | Chance: $spell[chance]% |
		Cost: $spell[cost]</dfn>";
		$nav = '
		<span style="float:right">' .
		construct_link_code($vbphrase['edit'], "petzadmin.php?$session[sessionurl]do=spelledit&amp;id=$spell[id]") .
		'</span>';
		print_description_row("$nav $spellname");
	}
	print_submit_row("Добавить заклинание", 0);
	print_cp_footer();
	exit;
}

/* Add / Edit Spell */
if ($_REQUEST['do'] == "spelledit" OR $_REQUEST['do'] == "spelladd") {
	print_cp_header("P3tz Spellz");
	if ($_REQUEST['do'] == "spelladd") {
		print_form_header('petzadmin', 'addspell');
		print_table_header("Дабавить заклинание");
	} else {
		$sid=$_GET['id'];
		$spell = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_spells WHERE id='$sid'");
		print_form_header('petzadmin', 'editspell');
		print_table_header("Редактировать заклинание");
	}
	// START ICON ROW
	$images = opendir("./petz/images/spells/");
	while ($icon = readdir($images)) {
		if(preg_match("/(.gif)/",$icon)) {
			if($icon != '.' || $icon  != '..') {
				$icon=preg_replace("/.gif/", "", $icon);
				if ($icon==$spell[element]) {
					$selected="selected";
				} else {
					$selected="";
				}
				$selecticon .= "<option value='".$icon."' ".$selected.">".$icon." ";
			}
		}
	}
	print_description_row("
	<script>
	function show_icon() {
		document.images['showicon'].src = '../petz/images/spells/' + document.cpform.spellelement.value + '.gif';
	}
	</script>
	<img src='../petz/images/spells/$spell[element].gif' name='showicon' align='right'>
	Spell Element:
	<select name='spellelement' onChange='show_icon()'>
		$selecticon
	</select>
	<input type='hidden' name='spell[id]' value='$spell[id]'>
	");
	// END ICON ROW
	print_input_row("Name: <dfn>The name of this spell.</dfn>", 'spell[name]', "$spell[name]");
	print_input_row("Description: <dfn>The description of this spell.</dfn>", 'spell[description]', "$spell[description]");
	print_input_row("Damage: <dfn>The <b>maximum</b> percent of an opponents health that this spell will reduce.</dfn>", 'spell[damage]', "$spell[damage]");
	print_input_row("Chance: <dfn>The <b>success</b> rate percent of this spell casting in battle.</dfn>", 'spell[chance]', "$spell[chance]");
	print_input_row("Cost: <dfn>The amount of points it costs to level up this spell.</dfn>", 'spell[cost]', "$spell[cost]");
	if($spell[moral]=="Evil"){
		$evilselect="selected";
	} elseif($spell[moral]=="Good"){
		$goodselect="selected";
	} else {
		$neutralselect="selected";
	}
	print_description_row("
	Spell Moral: <dfn>This spell can only be used in battles if the moral matches the p3tz moral. Any pet may learn the spell.</dfn>
	<br />
	<select name='spell[moral]'>
	<option value='Good' $goodselect>Good </option>
	<option value='Neutral' $neutralselect>Neutral </option>
	<option value='Evil' $evilselect>Evil </option>
	</select>
	<input type='hidden' name='spell[id]' value='$spell[id]'>
	");
	if ($_REQUEST['do'] == "spelladd") {
		print_submit_row('Создать новое заклинание', 0);
	} else {
		print_input_row("Remove: <dfn>To delete this spell type <b>DELETECONFIRM</b>. No further confirmation screens will display.</dfn>", 'spell[delete]', '');
		print_submit_row('Редактировать заклинание', 0);
	}
	print_cp_footer();
	exit;
}
/* Add / Edit / Delete Spell DB */
if ($_POST['do'] == "editspell" OR $_POST['do'] == "addspell") {
	$spell = &$_POST['spell'];
	$spellelement=$_POST['spellelement'];
	$spell[damage]=eregi_replace("[^0-9]", null, $spell[damage]);
	$spell[chance]=eregi_replace("[^0-9]", null, $spell[chance]);
	$spell[name]=$vbulletin->db->escape_string($spell[name]);
	$spell[description]=$vbulletin->db->escape_string($spell[description]);
	if ($_REQUEST['do'] == "addspell") {
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_spells
		(id,name,description,element,damage,moral,cost,chance)
		VALUES ('','".$spell[name]."','".$spell[description]."','".$spellelement."','".$spell[damage]."',
		'".$spell[moral]."','".$spell[cost]."','".$spell[chance]."')");
	} else {
		if($spell[delete]=="DELETECONFIRM"){
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_spells WHERE id='".$spell[id]."' ");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_magic WHERE sid='".$spell[id]."' ");	
		} else {
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_spells
			SET name='".$spell[name]."', description='".$spell[description]."', element='".$spellelement."', damage='".$spell[damage]."',
			moral='".$spell[moral]."', cost='".$spell[cost]."', chance='".$spell[chance]."' WHERE id='".$spell[id]."'");
		}
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=spells');
	print_stop_message('petz_acp_redirect');
}

/******************************************************************************************\
|**************************************** P3tz Statz **************************************|
\******************************************************************************************/

if ($_REQUEST['do'] == "menu") {
	print_cp_header("P3tz");
	print_form_header('petzadmin', 'petz');
	print_table_header("Статистика");
	print_description_row("Contained below are a few statistics about the P3tz system on your forum.");
	$petz = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz");
	$totalpets = $vbulletin->db->num_rows($petz);
	unset($petz);
	$petz = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_battle");
	$totalarenas = $vbulletin->db->num_rows($petz);
	unset($petz);
	$petz = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_inventory");
	$totalitems = $vbulletin->db->num_rows($petz);
	unset($petz);
	$petz = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_magic");
	$totalspells = $vbulletin->db->num_rows($petz);
	unset($petz);
	$petz = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_training");
	$totalbots = $vbulletin->db->num_rows($petz);
	unset($petz);
	$petz = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_gamble");
	$totalbets = $vbulletin->db->num_rows($petz);
	unset($petz);
	$petz = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_pms");
	$totalpms = $vbulletin->db->num_rows($petz);
	unset($petz);
	$petz = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_auction");
	$totalauctions = $vbulletin->db->num_rows($petz);
	unset($petz);
	print_description_row("<span style=\"float:right\">".$vbulletin->options['petz_version']."</span><b>P3tz Version</b>:");
	print_description_row("<span style=\"float:right\">$totalpets</span><b>Total User P3tz</b>:");
	print_description_row("<span style=\"float:right\">$totalarenas</span><b>Active Battles</b>:");
	print_description_row("<span style=\"float:right\">$totalitems</span><b>Total Inventory Items</b>:");
	print_description_row("<span style=\"float:right\">$totalspells</span><b>Total Learnt Spells</b>:");
	print_description_row("<span style=\"float:right\">$totalauctions</span><b>Total Auctions</b>:");
	print_description_row("<span style=\"float:right\">$totalpms</span><b>Total Unread P3tz Messages</b>:");
	print_description_row("<span style=\"float:right\">$totalbots</span><b>Active Bots</b>:");
	print_description_row("<span style=\"float:right\">$totalbets</span><b>Current Gambleing Tickets</b>:");
	print_table_footer();
	print_cp_footer();
	exit;
}

/******************************************************************************************\
|************************************** P3tz Restock **************************************|
\******************************************************************************************/

if ($_REQUEST['do'] == "restock") {
	if ($vbulletin->options['petz_stock']==1) {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_items SET stock=restock WHERE stock=0 AND restock>0");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_heal SET stock=restock WHERE stock=0 AND restock>0");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=menu');
	print_stop_message('petz_acp_redirect');
	exit;
}

/******************************************************************************************\
|************************************ AUCTION ADMIN ***************************************|
\******************************************************************************************/

/* Auction Admin */
if ($_GET['do'] == "auctions") {
	print_cp_header("P3tz Auctions");
	print_form_header('petzadmin', 'editauction');
	print_table_header("Аукцион");
	$auctions = $vbulletin->db->query_read("SELECT auction.id AS id, auction.days AS days, auction.started AS started,
	auction.type AS type, user.username AS username
	FROM " . TABLE_PREFIX . "petz_auction AS auction
	LEFT JOIN ".TABLE_PREFIX."user AS user ON (auction.userid = user.userid)
	");
	while ($auction = $vbulletin->db->fetch_array($auctions)) {
		$started=vbdate($vbulletin->options['logdateformat'],$auction[started]);
		if ($auction[type]==1) {
			$type="Pet";
		} else {
			$type="Item";
		}
		$itemname="<b>$auction[username]</b> - $type - <i>$started</i> - $auction[days] day listing.";
		print_description_row("<span style=\"float:right\">Edit: <input type=\"radio\" name=\"aid\" value=\"$auction[id]\"></span> $itemname");
	}
	print_submit_row($vbphrase['view'], 0);
	print_cp_footer();
	exit;
}
/* Edit Auction */
if ($_POST['do'] == "editauction") {
	print_cp_header("P3tz Auctions");
	$aid=$_POST['aid'];
	if ($aid>0) {
		$auction = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_auction WHERE id='$aid'");
	} else {
		// redirect
		define('CP_REDIRECT', 'petzadmin.php?do=auctions');
		print_stop_message('petz_acp_redirect');
	}
	if ($auction[id]<1) {
		// redirect
		define('CP_REDIRECT', 'petzadmin.php?do=auctions');
		print_stop_message('petz_acp_redirect');
	}
	print_form_header('petzadmin', 'auctionedit');
	print_table_header("Редактировать аукцион");
	print_input_row("User: <dfn>Should be the same as the article user id. (Don't Change)</dfn>", 'auction[userid]', "$auction[userid]");
	print_input_row("Article: <dfn>The article id. (Don't Change)</dfn>", 'auction[article]', "$auction[article]");
	// START TYPE ROW
	if ($auction[type]==1) { $selected1="selected"; }
	if ($auction[type]==2) { $selected2="selected"; }
	$selectt .= "<option value='1' ".$selected1.">Pet Auction";
	$selectt .= "<option value='2' ".$selected2.">Item Auction";
	print_description_row("
	Auction Type: (Don't Change)
	<select name='auction[type]'>
		$selectt
	</select>
	<input type='hidden' name='auction[id]' value='$auction[id]'>
	");
	// END TYPE ROW
	print_input_row("Message: <dfn>The message that accompanies the listing.</dfn>", 'auction[message]', "$auction[message]");
	print_input_row("Reserve: <dfn>The listing's reserve. 0 to disable.</dfn>", 'auction[reserve]', "$auction[reserve]");
	print_input_row("Instant: <dfn>The listing's instant buy price. 0 to disable.</dfn>", 'auction[instant]', "$auction[instant]");
	print_input_row("Highest Bid: <dfn>The current highest bid.</dfn>", 'auction[winbid]', "$auction[winbid]");
	print_input_row("Highest Bidder: <dfn>The current highest bidder user id.</dfn>", 'auction[bidder]', "$auction[bidder]");
	print_time_row("Started Date: <dfn>The date this auction started.</dfn>", 'auction[started]', "$auction[started]");
	print_input_row("Duration: <dfn>The listing's duration in days.</dfn>", 'auction[days]', "$auction[days]");
	print_input_row("Remove: <dfn>To delete this auction type <b>DELETECONFIRM</b>. No further confirmation screens will display.<br><i>This will not end the auction it will delete it.</i></dfn>", 'auction[delete]', '');
	print_submit_row($vbphrase['edit'], 0);
	print_cp_footer();
	exit;
}
/* Edit Auction DB */
if ($_POST['do'] == "auctionedit") {
	$auction = &$_POST['auction'];
	$auction[started]=mktime($auction[started][hour],$auction[started][minute],0,$auction[started][month],$auction[started][day],$auction[started][year]);
	if($auction[started]<1){
		$auction[started]=TIMENOW;
	}
	$auction[days]=eregi_replace("[^0-9]", null, $auction[days]);
	$auction[bidder]=eregi_replace("[^0-9]", null, $auction[bidder]);
	$auction[article]=eregi_replace("[^0-9]", null, $auction[article]);
	$auction[userid]=eregi_replace("[^0-9]", null, $auction[userid]);
	$auction[reserve]=$vbulletin->db->escape_string($auction[reserve]);
	$auction[instant]=$vbulletin->db->escape_string($auction[instant]);
	$auction[winbid]=$vbulletin->db->escape_string($auction[winbid]);
	$auction[message]=$vbulletin->db->escape_string($auction[message]);
	if($auction[delete]=="DELETECONFIRM"){
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_auction WHERE id='".$auction[id]."' ");
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_auction SET
		userid='".$auction[userid]."', type='".$auction[type]."', article='".$auction[article]."', message='".$auction[message]."',
		instant='".$auction[instant]."', reserve='".$auction[reserve]."', days='".$auction[days]."', started='".$auction[started]."',
		winbid='".$auction[winbid]."', bidder='".$auction[bidder]."' WHERE id='".$auction[id]."'");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=auctions');
	print_stop_message('petz_acp_redirect');
}

/******************************************************************************************\
|************************************* ARENAS ADMIN ***************************************|
\******************************************************************************************/

/* Arena Admin */
if ($_GET['do'] == "arenas") {
	print_cp_header("P3tz Arenas");
	print_form_header('petzadmin', 'editarena');
	print_table_header("Бои");
	$arenas = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "petz_battle");
	while ($arena = $vbulletin->db->fetch_array($arenas)) {
		$started=vbdate($vbulletin->options['logdateformat'],$arena[created]);
		if ($arena[type]==1) {
			$type="One On One";
		} elseif ($arena[type]==2) {
			$type="Free For All";
		} elseif ($arena[type]==3) {
			$type="Guild War";
		} else {
			$type="Training";
		}
		$itemname="<b>$arena[title]</b> - $type - <i>$started</i> (Round: $arena[round])";
		print_description_row("<span style=\"float:right\">Edit: <input type=\"radio\" name=\"bid\" value=\"$arena[id]\"></span> $itemname");
	}
	print_submit_row($vbphrase['view'], 0);
	print_cp_footer();
	exit;
}
/* Edit Arena */
if ($_POST['do'] == "editarena") {
	print_cp_header("P3tz Arena");
	$bid=$_POST['bid'];
	if ($bid>0) {
		$arena = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_battle WHERE id='$bid'");
	} else {
		// redirect
		define('CP_REDIRECT', 'petzadmin.php?do=arenas');
		print_stop_message('petz_acp_redirect');
	}
	if ($arena[id]<1) {
		// redirect
		define('CP_REDIRECT', 'petzadmin.php?do=arenas');
		print_stop_message('petz_acp_redirect');
	}
	print_form_header('petzadmin', 'arenaedit');
	print_table_header("Редактировать бой");
	print_input_row("Title: <dfn>Battle Name</dfn>", 'arena[title]', "$arena[title]");
	print_input_row("Max Pets: <dfn>The Maximum number of pets allowed in this brawl.</dfn>", 'arena[maxpetz]', "$arena[maxpetz]");
	// START TYPE ROW
	if ($arena[type]==1) { $selected1="selected"; }
	if ($arena[type]==2) { $selected2="selected"; }
	if ($arena[type]==3) { $selected3="selected"; }
	if ($arena[type]==4) { $selected4="selected"; }
	$selectt .= "<option value='1' ".$selected1.">One On One";
	$selectt .= "<option value='2' ".$selected2.">Free For All";
	$selectt .= "<option value='3' ".$selected3.">Guild War";
	$selectt .= "<option value='4' ".$selected4.">Training";
	print_description_row("
	Arena Type: (Don't Change)
	<select name='arena[type]'>
		$selectt
	</select>
	<input type='hidden' name='arena[id]' value='$arena[id]'>
	");
	// END TYPE ROW
	// START ICON ROW
	$images = opendir("./petz/images/battlegrounds/");
	while ($icon = readdir($images)) {
		if(preg_match("/(.jpg)/",$icon)) {
			if($icon != '.' || $icon  != '..') {
				$icon=preg_replace("/.jpg/", "", $icon);
				if ($icon==$arena[background]) {
					$selected="selected";
				} else {
					$selected="";
				}
				$selecticon .= "<option value='".$icon."' ".$selected.">".$icon." ";
			}
		}
	}
	print_description_row("
	Battle Ground:
	<select name='arena[background]'>
		$selecticon
	</select>
	");
	// END ICON ROW
	print_input_row("Round: <dfn>The current battle round. (Don't Change)</dfn>", 'arena[round]', "$arena[round]");
	print_time_row("Started Date: <dfn>The date this arena was created.</dfn>", 'arena[created]', "$arena[created]");
	print_input_row("Remove: <dfn>To delete this arena type <b>DELETECONFIRM</b>. No further confirmation screens will display.<br><i>This will not end the battle it will delete it.</i></dfn>", 'arena[delete]', '');
	print_submit_row($vbphrase['edit'], 0);
	print_cp_footer();
	exit;
}
/* Edit Arena DB */
if ($_POST['do'] == "arenaedit") {
	$arena = &$_POST['arena'];
	$arena[created]=mktime($arena[created][hour],$arena[created][minute],0,$arena[created][month],$arena[created][day],$arena[created][year]);
	if($arena[created]<1){
		$arena[created]=TIMENOW;
	}
	$arena[maxpetz]=eregi_replace("[^0-9]", null, $arena[maxpetz]);
	$arena[round]=eregi_replace("[^0-9]", null, $arena[round]);
	$arena[title]=$vbulletin->db->escape_string($arena[title]);
	$arena[background]=$vbulletin->db->escape_string($arena[background]);
	if ($arena[delete]=="DELETECONFIRM") {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET battle=0, round=0 WHERE battle='".$arena[id]."' ");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_gamble SET won=2 WHERE bid='".$arena[id]."' ");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battle WHERE id='".$arena[id]."' ");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battlelog WHERE battleid='".$arena[id]."' ");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_battlechat WHERE battleid='".$arena[id]."' ");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_training WHERE battle='".$arena[id]."' ");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_guildwar WHERE id='".$arena[id]."' ");
	} else {
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_battle SET
		title='".$arena[title]."', background='".$arena[background]."', type='".$arena[type]."', maxpetz='".$arena[maxpetz]."',
		round='".$arena[round]."', created='".$arena[created]."' WHERE id='".$arena[id]."' ");
	}
	// redirect
	define('CP_REDIRECT', 'petzadmin.php?do=arenas');
	print_stop_message('petz_acp_redirect');
}

?>