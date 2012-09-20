<?php
/*******************************************\
*	P3tz [vb]		  	File: petz.php		*
*	Version: 3.3.1	   	Licensed			*
*********************************************
*	Created by Steve	For use with VB 3.6	*
*********************************************
*		 Online Virtual Petz Hack			*
*		 To be used under License			*
*********************************************
*	Website:			http://www.P3tz.com	*
\*******************************************/

/* Define Stuff */
error_reporting(E_ALL & ~E_NOTICE);
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'petz');
$phrasegroups = array();
$specialtemplates = array();
$globaltemplates=array(
	'petz_main'
);
$actiontemplates = array(
	'home' => array(
		'petz_home',
		'petz_item',
		'petz_view'
	),
	'market' => array(
		'petz_market',
		'petz_item'
	),
	'vet' => array(
		'petz_vet',
		'petz_item',
		'petz_create'
	),
	'egg' => array(
		'petz_egg'
	),
	'use' => array(
		'petz_inventory_use'
	),
	'adoption' => array(
		'petz_adopt',
		'petz_adopt_bit'
	),
	'kennels' => array(
		'petz_kennel',
		'petz_pet_bit'
	),
	'graveyard' => array(
		'petz_graveyard',
		'petz_grave_bit'
	),
	'toplist' => array(
		'petz_toplist',
		'petz_pet_bit'
	),
	'guilds' => array(
		'petz_guild',
		'petz_guild_bit',
		'petz_pet_bit'
	),
	'search' => array(
		'petz_search',
		'petz_pet_bit'
	),
	'view' => array(
		'petz_profile',
		'petz_view',
		'petz_item',
		'petz_spell_bit'
	),
	'arena' => array(
		'petz_arena',
		'petz_arena_bit',
		'petz_view'
	),
	'battle' => array(
		'petz_battle',
		'petz_battle_contender',
		'petz_battle_view'
	),
	'spells' => array(
		'petz_spell',
		'petz_spell_bit'
	),
	'gamble' => array(
		'petz_gamble',
		'petz_gamble_ticket'
	),
	'training' => array(
		'petz_training',
		'petz_arena_bit'
	),
	'guildwar' => array(
		'petz_guildwar'
	),
	'auction' => array(
		'petz_auction',
		'petz_auction_bit'
	)
);
/* The Bits */
require_once('./global.php');
require_once('./includes/functions_user.php');
require_once('./petz/core/functions.php');
require_once('./petz/language.php');
/* Important */
if ((!isset($vbulletin->options['petz_ptable'])) OR (empty($vbulletin->options['petz_pfield']))) {
	show_alert($vbphrase['petz_not_installed']);
}
if ($vbulletin->options['petz_on']!=1) {
	show_alert($vbphrase['petz_not_enabled']);
}
/* Group Permissions */
$petgroup['create']=0;
$petgroup['own']=0;
$petgroup['view']=0;
$groupvid=explode(",", $vbulletin->options['petz_groupview']);
$groupoid=explode(",", $vbulletin->options['petz_groupown']);
$groupcid=explode(",", $vbulletin->options['petz_groupcreate']);
foreach ($groupvid as $groupv) {
	if (is_member_of($vbulletin->userinfo, $groupv)) {
		$petgroup['view']=1;
	}
}
foreach ($groupoid as $groupo) {
	if (is_member_of($vbulletin->userinfo, $groupo)) {
		$petgroup['own']=1;
	}
}
foreach ($groupcid as $groupc) {
	if (is_member_of($vbulletin->userinfo, $groupc)) {
		$petgroup['create']=1;
	}
}
if ($petgroup['view']==0) {
	print_no_permission();
	exit;
}
/* Stuffs */
$do=petz_ui("do","r","TYPE_NOHTML");
$id=petz_ui("id","r","TYPE_NOHTML");
$op=petz_ui("op","r","TYPE_NOHTML");
if ($do=="cron") { unset($do); }
/* Get Points */
$userpoints=number_format($vbulletin->userinfo[$vbulletin->options['petz_pfield']],2);
/* Load The Bits */
if ((file_exists("./petz/core/$do.php"))==1) {
	require_once("./petz/core/$do.php"); // Load Code
} else {
	if($id!=1) {
		$vbulletin->url = "petz.php?do=home&id=1";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	} else {
		eval('$petzbody = "' . $vbphrase['petz_catclysmic_rorer'] . '";');
	}
}
/* Display The Templates */
$navbits = array("petz.php" => $vbphrase['petz_petz']);
$navbits[""] = $vbphrase['petz_'.$do.''];
$navbits = construct_navbits($navbits);
eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('petz_main') . '");');
?>