<?php
/*******************************************\
*	P3tz [vb]		  	File: auction.php	*
*	Version: 3.3.1	   	Licensed			*
********************************************/

if (!is_object($vbulletin->db)) {
	exit;
}
if (($op=="sell") AND ($id!=1)) {
	$petq=$vbulletin->db->query_read("SELECT id, name FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND
	ownerid='".$vbulletin->userinfo[userid]."'");
	while ($pet=$vbulletin->db->fetch_array($petq)) {
		$pet[name]=stripslashes($pet[name]);
		$petoptions.="<option value=\"$pet[id]\">$pet[name]</option>\n";
	}
	$itemq=$vbulletin->db->query_read("
	SELECT inventory.id AS iid, item.name As name
	FROM ".TABLE_PREFIX."petz_inventory AS inventory
	LEFT JOIN ".TABLE_PREFIX."petz_items AS item ON (inventory.itemid = item.id)
	WHERE userid = '".$vbulletin->userinfo['userid']."'
	ORDER BY item.cost ASC");
	while ($item=$vbulletin->db->fetch_array($itemq)) {
		$item[name]=stripslashes($item[name]);
		$itemoptions.="<option value=\"$item[iid]\">$item[name]</option>\n";
	}
	if (($itemoptions=="") AND ($petoptions=="")) {
		show_alert($vbphrase['petz_auction_nosell']);
	} else {
		$i=0;
		while ($vbulletin->options['petz_auctiondays']>$i) {
			$i++;
			$dayoptions.="<option value=\"$i\">$i</option>\n";
		}
	}
} elseif ($op=="sell") {
	$pid=petz_ui("pid","p","TYPE_INT");
	$iid=petz_ui("iid","p","TYPE_INT");
	$reserve=petz_ui("reserve","p","TYPE_INT");
	$days=petz_ui("days","p","TYPE_INT");
	$instant=petz_ui("instant","p","TYPE_INT");
	$message=petz_ui("message","p","TYPE_NOHTML");
	if (($pid<1) AND ($iid<1)){
		show_alert($vbphrase['petz_auction_nosell']);
	} elseif ($reserve<0) {
		show_alert($vbphrase['petz_auction_nosell']);
	} elseif ($instant<0) {
		show_alert($vbphrase['petz_auction_nosell']);
	} elseif ($days<1) {
		show_alert($vbphrase['petz_auction_noday']);
	} elseif ($pid>0) {
		$pet=$vbulletin->db->query_first("SELECT petz.id as id, petz.ownerid as ownerid, adopt.id as adopt, auction.id as auction,
		auction.type as atype
		FROM " . TABLE_PREFIX . "petz_petz AS petz
		LEFT JOIN ".TABLE_PREFIX."petz_adopt AS adopt ON (petz.id = adopt.petid)
		LEFT JOIN ".TABLE_PREFIX."petz_auction AS auction ON (petz.id = auction.article)
		WHERE petz.id='".$pid."'");
		if ($pet[id]<1) {
			show_alert($vbphrase['petz_not_pet']);
		} elseif ($pet[adopt]>0) {
			show_alert($vbphrase['petz_being_sold']);
		} elseif (($pet[auction]>0) AND ($pet[atype]==1)) {
			show_alert($vbphrase['petz_auction_exists']);
		} elseif ($vbulletin->userinfo['userid']!=$pet[ownerid]) {
			show_alert($vbphrase['petz_not_your_pet']);
		} else {
			$reserve=$vbulletin->db->escape_string($reserve);
			$instant=$vbulletin->db->escape_string($instant);
			$message=$vbulletin->db->escape_string($message);
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_auction 
			(id,userid,type,article,message,instant,reserve,days,started,winbid,bidder)
			VALUES('',".$vbulletin->userinfo['userid'].",1,".$pet[id].",'".$message."','".$instant."','".$reserve."','".$days."',
			'".TIMENOW."',0,0)");
			if ($vbulletin->options['petz_log']==1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=auction";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	} else {
		$item=$vbulletin->db->query_first("SELECT item.id as id, item.userid as userid, auction.id as auction, auction.type as atype
		FROM " . TABLE_PREFIX . "petz_inventory as item
		LEFT JOIN ".TABLE_PREFIX."petz_auction AS auction ON (item.id = auction.article)
		WHERE item.id='".$iid."'");
		if ($item[id]<1) {
			show_alert($vbphrase['petz_item_none']);
		} elseif (($item[auction]>0) AND ($item[atype]==2)) {
			show_alert($vbphrase['petz_auction_exists']);
		} elseif ($vbulletin->userinfo['userid']!=$item[userid]) {
			show_alert($vbphrase['petz_you_are_a_spoon']);
		} else {
			$reserve=$vbulletin->db->escape_string($reserve);
			$instant=$vbulletin->db->escape_string($instant);
			$message=$vbulletin->db->escape_string($message);
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "petz_auction 
			(id,userid,type,article,message,instant,reserve,days,started,winbid,bidder)
			VALUES('',".$vbulletin->userinfo['userid'].",2,".$item[id].",'".$message."','".$instant."','".$reserve."','".$days."',
			'".TIMENOW."',0,0)");
			if ($vbulletin->options['petz_log']==1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=auction";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	}
} elseif ($op=="end" AND ($id>0)) {
	$auction=$vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_auction WHERE id='".$id."'");
	if ($vbulletin->userinfo['userid']!=$auction['userid']) {
		show_alert($vbphrase['petz_auction_not_yours']);
	} elseif (TIMENOW<$auction['started']+($auction['days']*86400)) {
		show_alert($vbphrase['petz_auction_not_ended']);
	} elseif (($auction['reserve']>0) AND ($auction['winbid']<$auction['reserve'])){
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
		".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+".$auction[winbid]." WHERE
		userid='".$auction[bidder]."'");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_auction WHERE id='".$auction[id]."'");
		if ($vbulletin->options['petz_log']==1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=auction";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	} elseif ($auction[winbid]<1) {
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_auction WHERE id='".$auction[id]."'");
		if ($vbulletin->options['petz_log']==1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=auction";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	} elseif ($auction[type]==1) {
		$petq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND
		ownerid='".$auction[bidder]."'");
		$peti=$vbulletin->db->num_rows($petq);
		if ($peti>=$vbulletin->options['petz_maxpetz']) {
			show_alert($vbphrase['petz_auction_much_pets']);
		} else {
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET ownerid='".$auction['bidder']."'
			WHERE id='".$auction['article']."'");
			$payment=round($auction[winbid]-($auction[winbid]/100*$vbulletin->options['petz_taxauction']),0);
			if($payment<1){ $payment=1; }
			$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
			".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+$payment WHERE
			userid='".$vbulletin->userinfo['userid']."'");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_auction WHERE id='".$auction[id]."'");
			petz_message($auction[bidder],9);
			if ($vbulletin->options['petz_log']==1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=auction";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}	
	} elseif ($auction[type]==2) {
		$inventoryq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_inventory WHERE
		userid='".$auction[bidder]."' LIMIT ".$vbulletin->options['petz_maxitems']."");
		$inventory = $vbulletin->db->num_rows($inventoryq);
		if ($inventory>=$vbulletin->options['petz_maxitems']) {
			show_alert($vbphrase['petz_auction_much_items']);
		} else {
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_inventory SET userid='".$auction['bidder']."'
			WHERE id='".$auction['article']."'");
			$payment=round($auction[winbid]-($auction[winbid]/100*$vbulletin->options['petz_taxauction']),0);
			if($payment<1){ $payment=1; }
			$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
			".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+$payment WHERE
			userid='".$vbulletin->userinfo['userid']."'");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_auction WHERE id='".$auction[id]."'");
			petz_message($auction[bidder],9);
			if ($vbulletin->options['petz_log']==1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=auction";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	} else {
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=auction";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} elseif ($op=="bid" AND ($id>0)) {
	$newbid=petz_ui("bid","p","TYPE_INT");
	if ($newbid>0) {
		$auction=$vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_auction WHERE id='".$id."'");
		if ($newbid<$auction[winbid]+1) {
			show_alert($vbphrase['petz_auction_bidlow']);
		} elseif ($petgroup['own']==0) {
			print_no_permission();
			exit;
		} elseif ($vbulletin->userinfo['userid']==$auction[userid]) {
			show_alert($vbphrase['petz_auction_yours']);
		} elseif (TIMENOW>$auction['started']+($auction['days']*86400)) {
			show_alert($vbphrase['petz_auction_ended']);
		} elseif ($newbid>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
			show_alert($vbphrase['petz_not_enough_points']);
		} else {
			$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
			".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$newbid." WHERE
			userid='".$vbulletin->userinfo['userid']."'");
			$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
			".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+".$auction[winbid]." WHERE
			userid='".$auction[bidder]."'");
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_auction SET winbid=".$newbid.",
			bidder='".$vbulletin->userinfo['userid']."' WHERE id='".$auction[id]."'");
			if ($vbulletin->options['petz_log']==1) {
				petz_log($op,$id,$do,$extra);
			}
			$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=auction";
			eval(print_standard_redirect('petz_redirect'));
			exit;
		}
	}
} elseif ($op=="buy" AND ($id>0)) {
	$auction=$vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "petz_auction WHERE id='".$id."'");
	if ($petgroup['own']==0) {
		print_no_permission();
		exit;
	} elseif ($vbulletin->userinfo['userid']==$auction[userid]) {
		show_alert($vbphrase['petz_auction_yours']);
	} elseif (TIMENOW>$auction['started']+($auction['days']*86400)) {
		show_alert($vbphrase['petz_auction_ended']);
	} elseif ($auction[instant]<1) {
		show_alert($vbphrase['petz_auction_no_instant']);
	} elseif ($auction[instant]>$vbulletin->userinfo[$vbulletin->options['petz_pfield']]) {
		show_alert($vbphrase['petz_not_enough_points']);
	} else {
		if ($auction[type]==1) {
			$petq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_petz WHERE dead='0' AND
			ownerid='".$vbulletin->userinfo['userid']."'");
			$peti=$vbulletin->db->num_rows($petq);
			if ($peti>=$vbulletin->options['petz_maxpetz']) {
				show_alert($vbphrase['petz_pet_toomany']);
			}
		} else {
			$itemq = $vbulletin->db->query_read("SELECT id FROM " . TABLE_PREFIX . "petz_inventory WHERE
			userid='".$vbulletin->userinfo['userid']."'");
			$itemi=$vbulletin->db->num_rows($itemq);
			if ($itemi>=$vbulletin->options['petz_maxitems']) {
				show_alert($vbphrase['petz_item_toomany']);
			}
		}
		if ($auction[type]==1) {
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_petz SET ownerid='".$vbulletin->userinfo['userid']."'
			WHERE id='".$auction['article']."'");
		} else {
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "petz_inventory SET userid='".$vbulletin->userinfo['userid']."'
			WHERE id='".$auction['article']."'");
		}
		$payment=round($auction[instant]-($auction[instant]/100*$vbulletin->options['petz_taxauction']),0);
		if($payment<1){ $payment=1; }
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
		".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+$payment WHERE
		userid='".$auction[userid]."'");
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
		".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."-".$auction[instant]." WHERE
		userid='".$vbulletin->userinfo['userid']."'");
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX . $vbulletin->options['petz_ptable']." SET
		".$vbulletin->options['petz_pfield']."=".$vbulletin->options['petz_pfield']."+".$auction[winbid]." WHERE
		userid='".$auction[bidder]."'");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "petz_auction WHERE id='".$auction[id]."'");
		petz_message($auction[userid],10);
		if ($vbulletin->options['petz_log']==1) {
			petz_log($op,$id,$do,$extra);
		}
		$vbulletin->url = 'petz.php?' . $vbulletin->session->vars['sessionurl'] . "do=auction";
		eval(print_standard_redirect('petz_redirect'));
		exit;
	}
} else {
	if ($id>0) {
		$xcon="AND auction.article='$id'";
	}
	if ($id=="mine") {
		$xcon="AND auction.userid='".$vbulletin->userinfo['userid']."'";
	}
	if ($id=="winning") {
		$xcon="AND auction.bidder='".$vbulletin->userinfo['userid']."'";
	}
	if ($op==1) {
		$auctions = $vbulletin->db->query_read("SELECT auction.*, petz.name AS name, seller.username AS username,
		seller.usergroupid AS usergroupid, ubidder.username AS winner
		FROM " . TABLE_PREFIX . "petz_auction AS auction
		LEFT JOIN ".TABLE_PREFIX."user AS seller ON (auction.userid = seller.userid)
		LEFT JOIN ".TABLE_PREFIX."user AS ubidder ON (auction.bidder = ubidder.userid)
		LEFT JOIN ".TABLE_PREFIX."petz_petz AS petz ON (petz.id = auction.article)
		WHERE auction.type=1
		$xcon
		");
	} elseif ($op==2) {
		$auctions = $vbulletin->db->query_read("SELECT auction.*, item.name AS name, item.image AS image, seller.username AS username,
		seller.usergroupid AS usergroupid, ubidder.username AS winner
		FROM " . TABLE_PREFIX . "petz_auction AS auction
		LEFT JOIN ".TABLE_PREFIX."user AS seller ON (auction.userid = seller.userid)
		LEFT JOIN ".TABLE_PREFIX."user AS ubidder ON (auction.bidder = ubidder.userid)
		LEFT JOIN ".TABLE_PREFIX."petz_inventory AS inventory ON (inventory.id = auction.article)
		LEFT JOIN ".TABLE_PREFIX."petz_items AS item ON (item.id = inventory.itemid)
		WHERE auction.type=2
		$xcon
		");
	}
	if ($op > 0) {
		$auctionresults = $vbulletin->db->num_rows($auctions);
		$perpage = 10;
		$pagenumber = petz_ui("page","r","TYPE_INT");
		if (empty($pagenumber)) { $pagenumber = 1; }
		$startingfrom = ($pagenumber*$perpage) - $perpage;
		$upperlimit = $startingfrom + $perpage;
		$i=0;
		if ($auctionresults!=0) {
			while ($auction = $vbulletin->db->fetch_array($auctions)) {
				$auction['name'] = stripslashes($auction['name']);
				$auction['message'] = stripslashes($auction['message']);
				$auction['ending'] = vbdate($vbulletin->options['dateformat'], $auction['started']+($auction['days']*86400));
				$auction['ending'] .= " - ";
				$auction['ending'] .= vbdate($vbulletin->options['timeformat'], $auction['started']+($auction['days']*86400));
				if($auction['type'] == 1){
					$auction['name'] = "<a href=\"petz.php?do=view&amp;id=".$auction['article']."\">".$auction['name']."</a>
							<br /> ";
				} else {
					$auction['name'] = "<img src=\"./petz/images/items/".$auction['image']."\" alt=\"".$auction['name']."\" />";
				}
				$auction['seller'] = "<a href=\"member.php?u=".$auction['userid']."\">".fetch_musername($auction, 'usergroupid')."</a>";
				if ($auction['bidder'] > 0) {
					$auction['winner'] = "<a href=\"member.php?u=".$auction['bidder']."\">".stripslashes($auction['winner'])."</a>";
				} else {
					$auction['winner'] = $vbphrase['petz_not_any'];
				}
				if ($auction['reserve'] < 1) {
					$auction['reserve'] = $vbphrase['petz_not_any'];
				}
				$canend = 0;
				$canbuy = 0;
				$canbid = 0;
				if (TIMENOW < $auction['started']+($auction['days']*86400)) {
					if (($vbulletin->userinfo['userid']>0) AND ($vbulletin->userinfo['userid']!=$auction['userid'])) {
						if ($vbulletin->userinfo[$vbulletin->options['petz_pfield']]>$auction['winbid']) {
							$canbid = 1;
						}
						if (($vbulletin->userinfo[$vbulletin->options['petz_pfield']]>=$auction['instant']) AND ($auction['instant']>0)) {
							$canbuy = 1;
						}
					}
				} else {
					if ($vbulletin->userinfo['userid']==$auction[userid]) {
						$canend = 1;
					}
				}
				if ((!isset($bgcolor)) OR ($bgcolor=="alt1")) {
					$bgcolor = "alt2";
				} else {
					$bgcolor = "alt1";
				}
				if (($i >= $startingfrom) AND ($i<$upperlimit)) {
					eval('$petz_auction_bit .= "' . fetch_template('petz_auction_bit') . '";');
				}
				$i++;
			}
			$i++;
			$pagenav = construct_page_nav($pagenumber,$perpage,$i, "petz.php?".
			$vbulletin->session->vars['sessionurl'] ."do=auction&amp;op=$op&amp;id=$id");
		}
	}
}
eval('$petzbody = "' . fetch_template('petz_auction') . '";');
// Cleanup
unset($auction, $auctions);
?>