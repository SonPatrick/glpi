<?php
/*
 * @version $Id: entity.function.php 5254 2007-07-20 00:47:39Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2007 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

function showProfileConfig($target,$ID,$prof){
	global $LANG,$CFG_GLPI;
	
	$onfocus="";
		if (!empty($ID)&&$ID){
			$prof->getFromDB($ID);
		} else {
			$prof->getEmpty();
			$onfocus="onfocus=\"this.value=''\"";
		}
	
	if (empty($prof->fields["interface"])) $prof->fields["interface"]="helpdesk";
	if (empty($prof->fields["name"])) $prof->fields["name"]=$LANG["common"][0];
	
	echo "<form name='form' method='post' action=\"$target\">";
	echo "<div class='center'>";
	echo "<table class='tab_cadre_fixe'><tr>";
	echo "<th>".$LANG["common"][16]." :&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type='text' name='name' value=\"".$prof->fields["name"]."\" $onfocus></th>";
	echo "<th>".$LANG["profiles"][2]." :&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<select name='interface' id='profile_interface'>";
	echo "<option value='helpdesk' ".($prof->fields["interface"]=="helpdesk"?"selected":"").">".$LANG["Menu"][31]."</option>";
	echo "<option value='central' ".($prof->fields["interface"]=="central"?"selected":"").">".$LANG["title"][0]."</option>";
	echo "</select></th>";
	echo "</tr></table>";

	$params=array('interface'=>'__VALUE__','ID'=>$ID,);
	
	ajaxUpdateItemOnSelectEvent("profile_interface","profile_form",$CFG_GLPI["root_doc"]."/ajax/profiles.php",$params,false);
	ajaxUpdateItem("profile_form",$CFG_GLPI["root_doc"]."/ajax/profiles.php",$params,false,'profile_interface');

	echo "<div align='center' id='profile_form'>";
	echo "</div>";
	echo "</div>";
	showLegend();
	echo "</form>";
}

	
function showProfileEntityUser($target,$ID,$prof){	
 	
 	global $DB,$LANG;
 	
 	if (!empty($ID)&&$ID)
		$prof->getFromDB($ID);
	else
		$prof->getEmpty();
	 	
 	echo "<div class='center'>";
	echo "<table class='tab_cadre_fixe'><tr>";
	echo "<th>".$LANG["profiles"][22]." :&nbsp;&nbsp;&nbsp;&nbsp;".$prof->fields["name"]."</th>";
 	echo "</tr></table>";
	echo "</div>";
  	
  	$query="SELECT glpi_users.*, glpi_users_profiles.FK_entities AS entity 
 		FROM glpi_users_profiles 
 		LEFT JOIN glpi_entities ON (glpi_entities.ID=glpi_users_profiles.FK_entities)
 		LEFT JOIN glpi_users ON (glpi_users.ID=glpi_users_profiles.FK_users)
 		WHERE glpi_users_profiles.FK_profiles=".$ID." ".getEntitiesRestrictRequest("AND","glpi_users_profiles")." 
 		ORDER BY glpi_entities.completename";
 	
 	echo "<div class='center'>";
	echo "<table class='tab_cadre_fixe'>";
 	
	$i=0;
	$nb_per_line=3;
	
 	if ($result = $DB->query($query))
 	{ 
 		if ($DB->numrows($result)!=0)
	 	{
	 		
	 		$temp=-1;
	 		while ($data=$DB->fetch_array($result)) 
			{	
	 			if($data["entity"]!=$temp)
				{
					while ($i%$nb_per_line!=0)
					{
						echo "<td class='tab_bg_1'>&nbsp;</td>\n";
						$i++;
					}

					if ($i!=0) echo "</table></div></td></tr>\n";
					
					echo "	<tr class='tab_bg_2'>";
					echo "  	<td align='left'>"; 
					echo "			<a  href=\"javascript:showHideDiv('entity$temp','imgcat$temp','".GLPI_ROOT."/pics/folder.png','".GLPI_ROOT."/pics/folder-open.png');\">";
					echo "				<img alt='' name='imgcat$temp' src=\"".GLPI_ROOT."/pics/folder.png\">&nbsp;<strong>".getDropdownName('glpi_entities',$data["entity"])."</strong>";
					echo "			</a>"; 
					echo "		</td>"; 
					echo "	</tr>"; 
					echo "<tr class='tab_bg_2'>";
					echo "		<td>
								     <div align='center' id='entity$temp' style=\"display:none;\">\n"; 
					echo"			<table class='tab_cadre'>\n";
		 			$i=0;
		 			$temp=$data["entity"];		
				}

		 		if ($i%$nb_per_line==0) {
					if ($i!=0) echo "</tr>\n";
						echo "<tr class='tab_bg_1'>\n";
					$i=0;	
				}

				echo "<td class='tab_bg_1'>".formatUserName($data["ID"],$data["name"],$data["realname"],$data["firstname"],1)."</td>\n";
				$i++;
 					
			}

			if ($i%$nb_per_line!=0)
			{
				while ($i%$nb_per_line!=0)
				{
					echo "<td class='tab_bg_1'>&nbsp;</td>";
					$i++;
				}
				
			}
			if ($i!=0) echo "</table></div></td></tr>\n";	
		}
 		else
 			echo "<tr><td class='tab_bg_1' align=center>".$LANG["profiles"][33]."</td></tr>";
 		}
 
 	echo "</table>";
	echo "</div>";

  }

function showLegend(){
	global $LANG;
	
	echo "<table class='tab_cadre_fixe'>";
	echo "<tr class='tab_bg_2'><td width='70' style='text-decoration:underline'><strong>".$LANG["profiles"][34]." : </strong></td><td class='tab_bg_4' width='15' style='border:1px solid black'></td><td><strong>".$LANG["profiles"][0]."</strong></td></tr>";
	echo "<tr class='tab_bg_2'><td></td><td class='tab_bg_2' width='15' style='border:1px solid black'></td><td><strong>".$LANG["profiles"][1]."</strong></td></tr>";
	echo "</table>";
}

?>
