-----------------------------
vBP3tz 3.3.1 edition from alex Roosso v 1.3b
By: vbP3tz and alex Roosso

for questions:
	mail: alex.roosso@gmail.com
	icq: 3405729
	http://www.vbsupport.org/forum/
	http://www.black-web.ru
-----------------------------

������� ��� ������� ������� �������� + ����������� ��� ����� �������������� �������� ����.
������� �� ���������� �� �����.

���������������� ����� ������ ���������� ����� ��������, � ��� ��� ��������� ������ (������ ��� ������ �������� � ������� ������� ��� ��������)
� �� ������ ��������. ����� ������� ����� ��������� ������ ������� ������������, ������� ��� ��� � ������� ���������� "�� ������"

������ ��� ��������� � ���������� ������ �� ��������.
������� ����� ���������������� � ����������.

   ��� ��������� ������ � ��������� ���������� �� ����� alex.roosso@gmail.com

=============================

��������� ��������:

���������������� �����: 99%
������ �� ��������: 1%
��������� �����: 20%

=============================

������� �������� P3tz �� alex Roosso:

v 1.3b
[+] � �������� ��������� ����������� ��������� ������ �������� �� ��������.
[+] ���������� ������� ��������� ����� ������ � ������ ������������ �� ��������.
[+] � Cron ��������� ������ ���� �� "�����" ������ ��������.
[*] ������� ������ �������� ������������� ���.
[*] � ������� ����� ������ ���� ������� ����������� � ����������� ������������.
[*] ���������� ������� ��� �������� �������� ����.
[*] ���������� ������ ������ � ������ ��������.

v 1.2b
[+] ������� � �������� �������� ������ �����. ������� �� ������ � ������ ����������.
[+] �������� �������� ��������� ������ ��� ������ � ���.
[*] ��� ��������� �������� ����� ���������� ����� ������ � ����������.

v 1.1b
[+] ���������� ���� ���� ��������� ������� ������� ����������� � ������.
[+] ������� ������ ����� � ���, ������� ������� ��� �� ��������� "��� ������ ����� ��� � �������"
[*] ���������� ������ ������ � ��������.
[*] ���������� ������ � ���������������� �����.


#########
#
#  ���������� P3tz � ������ ������������� (����������)
#  ��� ��������� ������� � ����, ��� ���������� ������ ������ ����� � ����� �� ���������������.
#
#########

version 1.3b aR
================
��������� ��������� SQL �������
ALTER TABLE pfx_petz_items ADD health INT NOT NULL AFTER moral ;
ALTER TABLE pfx_petz_battle ADD cost INT NOT NULL AFTER maxpetz ;
________________
 *pfx_ ���� �������� �� ������� ������� �� ����������� � ����� ��.
  ������ ��� ����� � ����� includes/config.php � ���������� 
  $config['Database']['tableprefix'] = 'pfx_';


version 1.2b aR
================
��������� ��������� SQL ������
ALTER TABLE pfx_petz_petz ADD fexperience SMALLINT( 3 ) DEFAULT '0' NOT NULL AFTER experience ;
________________
 *pfx_ ���� �������� �� ������� ������� �� ����������� � ����� ��.
  ������ ��� ����� � ����� includes/config.php � ���������� 
  $config['Database']['tableprefix'] = 'pfx_';
  

��� ������
================
�������� ������������ ����� ������� �� ����� upload ��� ���������� � ������, �������� �������� ��������.


#########
#
#  ��������� P3tz 3.3.1 ed v1.3b aR
#
#########

1. ����������� ��� ����� �� ������ ����� upload � ������ ������ ������ �������� �������� ������� ��������.
2. ����� � ������� ����� ����� (�����) � ���������� ���������� ������� "��������/������������� �������".
3. �������� ���� product-petz.xml �� ����� ������ � ������������ ���.

4. ����� � ������� => ���������� ������� => ������������� �������

	� ������� MEMBERINFO
	�������
		<if condition="$show['signature']">
	
	�������� ����
		<if condition="$userinfo[petz]">
		<table cellpadding="0" cellspacing="0" border="0" class="tborder" width="$stylevar[tablewidth]" align="center"> 
		  <tr> 
		    <td>
		        <table cellpadding="$stylevar[cellpadding]" cellspacing="$stylevar[cellspacing]" border="0" width="100%"> 
		            <tr align="center"> 
		              <td class="tcat">�������</td>
		            </tr>
		            <tr align="center"> 
		              <td class="alt1">$userinfo[petz]</td>
		        </table>
		    </td> 
		  </tr> 
		</table>
		<br /><br />
		</if>
		<table cellpadding="0" cellspacing="0" border="0" class="tborder" width="$stylevar[tablewidth]" align="center"> 
		  <tr> 
		    <td>
		        <table cellpadding="$stylevar[cellpadding]" cellspacing="$stylevar[cellspacing]" border="0" width="100%"> 
		<thead>
		            <tr align="center"> 
		              <td class="tcat" colspan="5"><a style="float:$stylevar[right]" href="#top" onclick="return toggle_collapse('inventory')"><img id="collapseimg_inventory" src="$stylevar[imgdir_button]/collapse_tcat{$collapseimg_inventory}.gif" alt="" border="0" /></a>���������</td>
		            </tr>
		</thead>
		<tbody id="collapseobj_inventory" style="{$collapseobj_inventory}">
		            <tr align="center"> 
		              <td class="thead" width="2%">�������</td>
		              <td class="thead" width="15%">��������</td>
		              <td class="thead" width="*">��������</td>

		              <td class="thead" width="15%">������</td>
		              <td class="thead" width="2%">�������</td>
		            </tr> 
		              $petz_inventory_bit
		</tbody>
		<tfoot>
		            <tr align="center"> 
		              <td class="thead" colspan="5" align="left">
		                 <span style="float:right">����� ���������: $totalitems</span>
		              </td> 
		            </tr>
		</tfoot>
		        </table>
		    </td> 
		  </tr> 
		</table>
		

	� ������� postbit
	�������
		<td width="100%">&nbsp;</td>
	
	�������� ��
		<td width="100%" align="right">&nbsp;$post[petz]</td>
		
		
	� ������� postbit_legacy 
	�������
		<div>$post[icqicon] $post[aimicon] $post[msnicon] $post[yahooicon] $post[skypeicon]</div>
	
	�������� ���� ��� ����
		<if condition="$post['petz']">
		 <span id="petz_$post[postid]" class="smallfont"><a href="#$post[postid]"><b>�������</b></a><script type="text/javascript"> vbmenu_register("petz_$post[postid]"); </script></span>
			<div class="vbmenu_popup" id="petz_$post[postid]_menu" style="display:none">
			 <table cellpadding="4" cellspacing="1" border="0">
			  <tr><td class="thead"><b>�������</b></td></tr>
			  <tr><td class="alt2"><nobr>$post[petz]</nobr></td></tr>
			 </table>
			</div>
		</if>

		
		
	� ������� headinclude 
	�������
		<!-- / CSS Stylesheet -->
	
	�������� ����
		<script type="text/javascript" src="clientscript/ac_run.js"></script>
		<script type="text/javascript">
		<!---
			function confirmaction(message, url){
			if(confirm(message)) location.href = url;
		}
		// --->
		</script>
		
		
	� ������� navbar 
	�������
		<td id="navbar_search" class="vbmenu_control"><a href="search.php$session[sessionurl_q]" accesskey="4" rel="nofollow">$vbphrase[search]</a> <script type="text/javascript"> vbmenu_register("navbar_search"); </script></td>

		            </if>
					
	�������� �����
		<td id="petz" class="vbmenu_control"><a href="petz.php$session[sessionurl_q]">�������</a><script type="text/javascript"> vbmenu_register("petz"); </script></td>

	������� � ����� ���� �������
		</if>
		
	�������� �����
		<div class="vbmenu_popup" id="petz_menu" style="display:none">
	        <table cellpadding="4" cellspacing="1" border="0">
	        <tr><td class="thead"><a href="petz.php?$session[sessionurl]do=home">��� ���</a></td></tr>        
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=market">�����</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=vet">���������</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=arena">�����</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=gamble">����</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=training">�������</a>&nbsp;</td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=spells">�����</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=adoption">������� ��������</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=kennels">�����</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=graveyard">��������</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=toplist">��� ����</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=guilds">�������</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=auction">�������</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=search">�����</a></td></tr>
	        </table>
	    </div>
		
5. ������� � ��������� P3tz � ��������������� �� ��� ���� �����.
	��� ������ �������� ���������� ���������� ��� ����� ���� ���������� �������.
	������� ��������� ����� �������� � ����������� ����������� ��������� ��� vBulletin - vbPlaza, uCash, iPoints. 
	��� ���������� � ����� �� ������ ������� � ���������� � "���� ��� ����������..." ��������� �������: 
	��� vbPlaza - vbbux 
	��� uCash - ucash 
	��� iPoints - ipoints

���� ������� ������������ � ������ � ������.
