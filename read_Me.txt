-----------------------------
vBP3tz 3.3.1 edition from alex Roosso v 1.3b
By: vbP3tz and alex Roosso

for questions:
	mail: alex.roosso@gmail.com
	icq: 3405729
	http://www.vbsupport.org/forum/
	http://www.black-web.ru
-----------------------------

Вообщем это русский перевод питомцев + модификации для более увлекательного процесса игры.
Перевод не претендует на идеал.

Пользовательская часть кончно переведена более вдумчиво, а вот над админской частью (вернее той частью перевода в админке который был выполнен)
я не сильно трудился. Админ человек более грамотный нежели простой пользователь, поэтому кое что я перевел фактически "по смыслу"

Работа над переводом и выявлением ошибок не окончена.
Перевод будет корректироваться и дополнятся.

   Все найденные ошибки и замечания присылайте на почту alex.roosso@gmail.com

=============================

Результат перевода:

Пользовательская часть: 99%
Помошь по питомцам: 1%
Админская часть: 20%

=============================

История редакции P3tz от alex Roosso:

v 1.3b
[+] В магазине появилась возможность продавать товары влияющие на здоровье.
[+] Владельцам гильдий добавлена опция отказа в списке претендентов на членство.
[+] В Cron добавлена чистка базы от "хлама" боевых действий.
[*] Изменен расчет генетики потомственных яиц.
[*] В расчете экспы внесен учет уровней противников и калькуляция выносливости.
[*] Поправлены шаблоны для удобства процесса игры.
[*] Исправлены мелкие ошибки в работе скриптов.

v 1.2b
[+] Изменен и усложнен алгоритм выдачи экспы. Переход от уровня к уровню усложнился.
[+] Персонаж получает экспиренс только при победе в бою.
[*] Был поправлен алгоритм урона наносимого ботом игроку в тренировке.

v 1.1b
[+] Получаемый опыт стал учитывать разницу уровней нападающего и жертвы.
[+] Изменен расчет урона в бою, который выводит бой из тенденции "кто первый начал тот и победил"
[*] Исправлены мелкие ошибки в переводе.
[*] Исправлены ошибки в административной части.


#########
#
#  Обновление P3tz с версии разработчиков (Инструкция)
#  При установке скрипта с нуля, эта инструкция теряет всякий смысл и может не рассматриваться.
#
#########

version 1.3b aR
================
Выполните следующие SQL Запросы
ALTER TABLE pfx_petz_items ADD health INT NOT NULL AFTER moral ;
ALTER TABLE pfx_petz_battle ADD cost INT NOT NULL AFTER maxpetz ;
________________
 *pfx_ надо заменить на префикс который Вы используйте в своей БД.
  Узнать его можно в файле includes/config.php в переменной 
  $config['Database']['tableprefix'] = 'pfx_';


version 1.2b aR
================
Выполните следующие SQL Запрос
ALTER TABLE pfx_petz_petz ADD fexperience SMALLINT( 3 ) DEFAULT '0' NOT NULL AFTER experience ;
________________
 *pfx_ надо заменить на префикс который Вы используйте в своей БД.
  Узнать его можно в файле includes/config.php в переменной 
  $config['Database']['tableprefix'] = 'pfx_';
  

Все версии
================
Обновите оригинальные файлы файлами из папки upload что содержится в архиве, сохраняя иерархию каталога.


#########
#
#  Установка P3tz 3.3.1 ed v1.3b aR
#
#########

1. Скопировать все файлы из архива папки upload в корень Вашего форума сохраняя заданную архивом иерархию.
2. Зайти в админку вашей воблы (булки) в Управление продуктами выбрать "Добавить/Импортировать продукт".
3. Выберите файл product-petz.xml из этого архива и импортируйте его.

4. Стили и шаблоны => Управление стилями => Редактировать шаблоны

	В шаблоне MEMBERINFO
	надийте
		<if condition="$show['signature']">
	
	добавьте выше
		<if condition="$userinfo[petz]">
		<table cellpadding="0" cellspacing="0" border="0" class="tborder" width="$stylevar[tablewidth]" align="center"> 
		  <tr> 
		    <td>
		        <table cellpadding="$stylevar[cellpadding]" cellspacing="$stylevar[cellspacing]" border="0" width="100%"> 
		            <tr align="center"> 
		              <td class="tcat">Питомцы</td>
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
		              <td class="tcat" colspan="5"><a style="float:$stylevar[right]" href="#top" onclick="return toggle_collapse('inventory')"><img id="collapseimg_inventory" src="$stylevar[imgdir_button]/collapse_tcat{$collapseimg_inventory}.gif" alt="" border="0" /></a>Инвентарь</td>
		            </tr>
		</thead>
		<tbody id="collapseobj_inventory" style="{$collapseobj_inventory}">
		            <tr align="center"> 
		              <td class="thead" width="2%">Предмет</td>
		              <td class="thead" width="15%">Название</td>
		              <td class="thead" width="*">Описание</td>

		              <td class="thead" width="15%">Эффект</td>
		              <td class="thead" width="2%">украсть</td>
		            </tr> 
		              $petz_inventory_bit
		</tbody>
		<tfoot>
		            <tr align="center"> 
		              <td class="thead" colspan="5" align="left">
		                 <span style="float:right">Всего предметов: $totalitems</span>
		              </td> 
		            </tr>
		</tfoot>
		        </table>
		    </td> 
		  </tr> 
		</table>
		

	В шаблоне postbit
	надийте
		<td width="100%">&nbsp;</td>
	
	замените на
		<td width="100%" align="right">&nbsp;$post[petz]</td>
		
		
	В шаблоне postbit_legacy 
	надийте
		<div>$post[icqicon] $post[aimicon] $post[msnicon] $post[yahooicon] $post[skypeicon]</div>
	
	добавьте ниже или выше
		<if condition="$post['petz']">
		 <span id="petz_$post[postid]" class="smallfont"><a href="#$post[postid]"><b>Питомцы</b></a><script type="text/javascript"> vbmenu_register("petz_$post[postid]"); </script></span>
			<div class="vbmenu_popup" id="petz_$post[postid]_menu" style="display:none">
			 <table cellpadding="4" cellspacing="1" border="0">
			  <tr><td class="thead"><b>Питомцы</b></td></tr>
			  <tr><td class="alt2"><nobr>$post[petz]</nobr></td></tr>
			 </table>
			</div>
		</if>

		
		
	В шаблоне headinclude 
	надийте
		<!-- / CSS Stylesheet -->
	
	добавьте ниже
		<script type="text/javascript" src="clientscript/ac_run.js"></script>
		<script type="text/javascript">
		<!---
			function confirmaction(message, url){
			if(confirm(message)) location.href = url;
		}
		// --->
		</script>
		
		
	В шаблоне navbar 
	надийте
		<td id="navbar_search" class="vbmenu_control"><a href="search.php$session[sessionurl_q]" accesskey="4" rel="nofollow">$vbphrase[search]</a> <script type="text/javascript"> vbmenu_register("navbar_search"); </script></td>

		            </if>
					
	Добавьте после
		<td id="petz" class="vbmenu_control"><a href="petz.php$session[sessionurl_q]">Питомцы</a><script type="text/javascript"> vbmenu_register("petz"); </script></td>

	Найдите в самом низу шаблона
		</if>
		
	Добавьте после
		<div class="vbmenu_popup" id="petz_menu" style="display:none">
	        <table cellpadding="4" cellspacing="1" border="0">
	        <tr><td class="thead"><a href="petz.php?$session[sessionurl]do=home">Мой Дом</a></td></tr>        
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=market">Рынок</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=vet">Ветеринар</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=arena">Арена</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=gamble">Пари</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=training">Тренинг</a>&nbsp;</td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=spells">Магия</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=adoption">Магазин питомцев</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=kennels">Приют</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=graveyard">Кладбище</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=toplist">Топ Лист</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=guilds">Гильдии</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=auction">Аукцион</a></td></tr>
	        <tr><td class="vbmenu_option"><a href="petz.php?$session[sessionurl]do=search">Поиск</a></td></tr>
	        </table>
	    </div>
		
5. Зайдите в настройки P3tz и сконфигурируйте их под свои нужды.
	Для работы Питомцев необходимо установить хак какой либо финансовой системы.
	Система тамогочки может работать с несколькими финансовыми системами для vBulletin - vbPlaza, uCash, iPoints. 
	Для интеграции с одной из систем укажите в настройках в "Поле для интеграции..." следующие значние: 
	Для vbPlaza - vbbux 
	Для uCash - ucash 
	Для iPoints - ipoints

Ваши питомцы установленны и готовы к работе.
