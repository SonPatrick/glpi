<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.

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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

// CLASSES Monitors


class Monitor extends CommonDBTM {


	function Monitor () {
		$this->table="glpi_monitors";
		$this->type=MONITOR_TYPE;
		$this->dohistory=true;
	}	

	function defineOnglets($withtemplate){
		global $LANG,$CFG_GLPI;

		$ong=array();
		if (haveRight("computer","r"))
			$ong[1]=$LANG["title"][26];
		if (haveRight("contract_infocom","r"))
			$ong[4]=$LANG["Menu"][26];
		if (haveRight("document","r"))
			$ong[5]=$LANG["title"][25];

		if(empty($withtemplate)){
			if (haveRight("show_ticket","1"))
				$ong[6]=$LANG["title"][28];
			if (haveRight("link","r"))
				$ong[7]=$LANG["title"][34];
			if (haveRight("notes","r"))
				$ong[10]=$LANG["title"][37];
			if (haveRight("reservation_central","r"))
				$ong[11]=$LANG["title"][35];
				
			$ong[12]=$LANG["title"][38];

		}	
		return $ong;
	}

	function prepareInputForUpdate($input) {
		// set new date.
		$input["date_mod"] = date("Y-m-d H:i:s");

		return $input;
	}

	function post_updateItem($input,$updates,$history=1) {

		if(isset($input["state"])){
			if (isset($input["is_template"])&&$input["is_template"]==1){
				updateState(MONITOR_TYPE,$input["ID"],$input["state"],1,0);
			}else {
				updateState(MONITOR_TYPE,$input["ID"],$input["state"],0,$history);
			}
		}
	}

	function prepareInputForAdd($input) {
		// set new date.
		$input["date_mod"] = date("Y-m-d H:i:s");

		// dump status
		$input["_oldID"]=$input["ID"];
		unset($input['withtemplate']);
		unset($input['ID']);

		// Manage state
		$input["_state"]=-1;
		if (isset($input["state"])){
			$input["_state"]=$input["state"];
			unset($input["state"]);
		}

		return $input;
	}

	function postAddItem($newID,$input) {
		global $DB;
		// Add state
		if ($input["_state"]>0){
			if (isset($input["is_template"])&&$input["is_template"]==1)
				updateState(MONITOR_TYPE,$newID,$input["_state"],1,0);
			else updateState(MONITOR_TYPE,$newID,$input["_state"],0,0);
		}

		// ADD Infocoms
		$ic= new Infocom();
		if ($ic->getFromDBforDevice(MONITOR_TYPE,$input["_oldID"])){
			$ic->fields["FK_device"]=$newID;
			unset ($ic->fields["ID"]);
			if (isset($ic->fields["num_immo"])) {
				$ic->fields["num_immo"] = autoName($ic->fields["num_immo"], "num_immo", 1, INFOCOM_TYPE);
			}

			$ic->addToDB();
		}

		// ADD Contract				
		$query="SELECT FK_contract from glpi_contract_device WHERE FK_device='".$input["_oldID"]."' AND device_type='".MONITOR_TYPE."';";
		$result=$DB->query($query);
		if ($DB->numrows($result)>0){

			while ($data=$DB->fetch_array($result))
				addDeviceContract($data["FK_contract"],MONITOR_TYPE,$newID);
		}

		// ADD Documents			
		$query="SELECT FK_doc from glpi_doc_device WHERE FK_device='".$input["_oldID"]."' AND device_type='".MONITOR_TYPE."';";
		$result=$DB->query($query);
		if ($DB->numrows($result)>0){

			while ($data=$DB->fetch_array($result))
				addDeviceDocument($data["FK_doc"],MONITOR_TYPE,$newID);
		}

	}

	function cleanDBonPurge($ID) {

		global $DB,$CFG_GLPI;

		$job =new Job();
		$query = "SELECT * FROM glpi_tracking WHERE (computer = '$ID'  AND device_type='".MONITOR_TYPE."')";
		$result = $DB->query($query);

		if ($DB->numrows($result))
			while ($data=$DB->fetch_array($result)) {
				if ($CFG_GLPI["keep_tracking_on_delete"]==1){
					$query = "UPDATE glpi_tracking SET computer = '0', device_type='0' WHERE ID='".$data["ID"]."';";
					$DB->query($query);
				} else $job->delete(array("ID"=>$data["ID"]));
			}

		$query = "DELETE FROM glpi_infocoms WHERE (FK_device = '$ID' AND device_type='".MONITOR_TYPE."')";
		$DB->query($query);

		$query="select * from glpi_reservation_item where (device_type='".MONITOR_TYPE."' and id_device='$ID')";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result)>0) {
				$rr=new ReservationItem();
				$rr->delete(array("ID"=>$DB->result($result,0,"ID")));
			}
		}

		$query = "DELETE FROM glpi_state_item WHERE (id_device = '$ID' AND device_type='".MONITOR_TYPE."')";
		$DB->query($query);

		$query2 = "DELETE from glpi_connect_wire WHERE (end1 = '$ID' AND type = '".MONITOR_TYPE."')";
		$DB->query($query2);

		$query = "DELETE FROM glpi_contract_device WHERE (FK_device = '$ID' AND device_type='".MONITOR_TYPE."')";
		$DB->query($query);
	}

	function title(){
		global  $LANG,$CFG_GLPI;

		$buttons=array();
		$title=$LANG["Menu"][3];
		if (haveRight("monitor","w")){
			$buttons["setup.templates.php?type=".MONITOR_TYPE."&amp;add=1"]=$LANG["monitors"][0];
			$buttons["setup.templates.php?type=".MONITOR_TYPE."&amp;add=0"]=$LANG["common"][8];
			$title="";
		}
		displayTitle($CFG_GLPI["root_doc"]."/pics/ecran.png",$LANG["Menu"][3],$title,$buttons);
	}


	function showForm ($target,$ID,$withtemplate='') {

		global $CFG_GLPI, $LANG;

		if (!haveRight("monitor","r")) return false;


		$mon_spotted = false;
		$unknown_device=false;	
		if((empty($ID) && $withtemplate == 1)||$ID==-1) {
			if($this->getEmpty()) $mon_spotted = true;
			 $unknown_device=true;

		} else {
			if($this->getfromDB($ID)&&haveAccessToEntity($this->fields["FK_entities"])) $mon_spotted = true;
		}

		if($mon_spotted) {
			$this->showOnglets($ID, $withtemplate,$_SESSION['glpi_onglet']);
	
			if(!empty($withtemplate) && $withtemplate == 2) {
				$template = "newcomp";
				$datestring = $LANG["computers"][14].": ";
				$date = convDateTime(date("Y-m-d H:i:s"));
			} elseif(!empty($withtemplate) && $withtemplate == 1) { 
				$template = "newtemplate";
				$datestring = $LANG["computers"][14].": ";
				$date = convDateTime(date("Y-m-d H:i:s"));
			} else {
				$datestring = $LANG["common"][26]." : ";
				$date = convDateTime($this->fields["date_mod"]);
				$template = false;
			}

			echo "<div align='center'><form method='post' name=form action=\"$target\">";
			if(strcmp($template,"newtemplate") === 0) {
				echo "<input type=\"hidden\" name=\"is_template\" value=\"1\" />";
			}

			if ($unknown_device){
				echo "<input type='hidden' name='FK_entities' value='".$_SESSION["glpiactive_entity"]."'>";
			}

			echo "<table  class='tab_cadre_fixe' cellpadding='2'>";

			echo "<tr><th align='center' >";
			if(!$template) {
				echo $LANG["monitors"][29].": ".$this->fields["ID"];
			}elseif (strcmp($template,"newcomp") === 0) {
				echo $LANG["monitors"][30].": ".$this->fields["tplname"];
				echo "<input type='hidden' name='tplname' value='".$this->fields["tplname"]."'>";
			}elseif (strcmp($template,"newtemplate") === 0) {
				echo $LANG["common"][6]."&nbsp;: ";
				autocompletionTextField("tplname","glpi_monitors","tplname",$this->fields["tplname"],20);	
			}

			echo "</th><th  align='center'>".$datestring.$date;
			if (!$template&&!empty($this->fields['tplname']))
				echo "&nbsp;&nbsp;&nbsp;(".$LANG["common"][13].": ".$this->fields['tplname'].")";
			echo "</th></tr>";

			if ($ID&&!($CFG_GLPI["cache"]->start($ID."_".$_SESSION["glpilanguage"],"GLPI_".$this->type))) {
				echo "<tr><td class='tab_bg_1' valign='top'>";
	
				echo "<table cellpadding='1' cellspacing='0' border='0'>\n";
	
				echo "<tr><td>".$LANG["common"][16]."*:	</td>";
				echo "<td>";
				$objectName = autoName($this->fields["name"], "name", ($template === "newcomp"), MONITOR_TYPE);
				autocompletionTextField("name","glpi_monitors","name",$objectName,20);
	
				//autocompletionTextField("name","glpi_monitors","name",$this->fields["name"],20);	
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["common"][15].": 	</td><td>";
				dropdownValue("glpi_dropdown_locations", "location", $this->fields["location"]);
				echo "</td></tr>";
	
				echo "<tr class='tab_bg_1'><td>".$LANG["common"][10].": 	</td><td colspan='2'>";
				dropdownUsersID("tech_num", $this->fields["tech_num"],"interface");
				echo "</td></tr>";
	
				echo "<tr class='tab_bg_1'><td>".$LANG["common"][5].": 	</td><td colspan='2'>";
				dropdownValue("glpi_enterprises","FK_glpi_enterprise",$this->fields["FK_glpi_enterprise"]);
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["common"][21].":	</td>";
				echo "<td>";
				autocompletionTextField("contact_num","glpi_monitors","contact_num",$this->fields["contact_num"],20);	
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["common"][18].":	</td><td>";
				autocompletionTextField("contact","glpi_monitors","contact",$this->fields["contact"],20);	
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["common"][34].": 	</td><td>";
				dropdownAllUsers("FK_users", $this->fields["FK_users"]);
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["common"][35].": 	</td><td>";
				dropdownValue("glpi_groups", "FK_groups", $this->fields["FK_groups"]);
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["state"][0].":</td><td>";
				$si=new StateItem();
				$t=0;
				if ($template) $t=1;
				$si->getfromDB(MONITOR_TYPE,$this->fields["ID"],$t);
				dropdownValue("glpi_dropdown_state", "state",$si->fields["state"]);
				echo "</td></tr>";
				
				echo "</table>";
	
				echo "</td>\n";	
				echo "<td class='tab_bg_1' valign='top'>";
	
				echo "<table cellpadding='1' cellspacing='0' border='0'";
	
	
				echo "<tr><td>".$LANG["peripherals"][33].":</td><td>";
				globalManagementDropdown($target,$withtemplate,$this->fields["ID"],$this->fields["is_global"]);
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["common"][17].": 	</td><td>";
				dropdownValue("glpi_type_monitors", "type", $this->fields["type"]);
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["common"][22].": 	</td><td>";
				dropdownValue("glpi_dropdown_model_monitors", "model", $this->fields["model"]);
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["common"][19].":	</td><td>";
				autocompletionTextField("serial","glpi_monitors","serial",$this->fields["serial"],20);	
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["common"][20]."*:</td><td>";
				$objectName = autoName($this->fields["otherserial"], "otherserial", ($template === "newcomp"), MONITOR_TYPE);
				autocompletionTextField("otherserial","glpi_monitors","otherserial",$objectName,20);
	
				//autocompletionTextField("otherserial","glpi_monitors","otherserial",$this->fields["otherserial"],20);	
				echo "</td></tr>";
	
				echo "<tr><td>".$LANG["monitors"][21].":</td>";
				echo "<td>";
				autocompletionTextField("size","glpi_monitors","size",$this->fields["size"],2);	
				echo "\"</td></tr>";
	
				echo "<tr><td>".$LANG["monitors"][18].": </td><td>";
	
				// micro?
				echo "<table border='0' cellpadding='2' cellspacing='0'><tr>";
				echo "<td>".$LANG["monitors"][14]."</td>";
				echo "<td>";
				dropdownYesNoInt("flags_micro",$this->fields["flags_micro"]);
				echo "</td>";
	
				// speakers?
				echo "<td>".$LANG["monitors"][15]."</td>";
				echo "<td>";
				dropdownYesNoInt("flags_speaker",$this->fields["flags_speaker"]);
				echo "</td>";
				echo "</tr><tr>";
	
				// sub-d?
				echo "<td>".$LANG["monitors"][19]."</td>";
				echo "<td>";
				dropdownYesNoInt("flags_subd",$this->fields["flags_subd"]);
				echo "</td>";
	
				// bnc?
				echo "<td>".$LANG["monitors"][20]."</td>";
				echo "<td>";
				dropdownYesNoInt("flags_bnc",$this->fields["flags_bnc"]);
				echo "</td>";
	
				echo "</tr><tr>";
	
				// dvi?
				echo "<td>".$LANG["monitors"][32]."</td>";
				echo "<td>";
				dropdownYesNoInt("flags_dvi",$this->fields["flags_dvi"]);
				echo "</td>";
	
				echo "<td>&nbsp;</td><td>&nbsp;</td>";
				echo "</tr></table>";
	
	
				echo "</td></tr>";
	
				echo "</table>";
				echo "</td>\n";	
				echo "</tr>";
				echo "<tr>";
				echo "<td class='tab_bg_1' valign='top' colspan='2'>";
	
				echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td valign='top'>";
				echo $LANG["common"][25].":	</td>";
				echo "<td align='center'><textarea cols='75' rows='4' name='comments' >".$this->fields["comments"]."</textarea>";
				echo "</td></tr></table>";
	
				echo "</td>";
				echo "</tr>";
				$CFG_GLPI["cache"]->end();
			}

			if (haveRight("monitor","w")){

				echo "<tr>";

				if ($template) {

					if (empty($ID)||$withtemplate==2){
						echo "<td class='tab_bg_2' align='center' colspan='2'>\n";
						echo "<input type='hidden' name='ID' value=$ID>";
						echo "<input type='submit' name='add' value=\"".$LANG["buttons"][8]."\" class='submit'>";
						echo "</td>\n";
					} else {
						echo "<td class='tab_bg_2' align='center' colspan='2'>\n";
						echo "<input type='hidden' name='ID' value=$ID>";
						echo "<input type='submit' name='update' value=\"".$LANG["buttons"][7]."\" class='submit'>";
						echo "</td>\n";
					}
				} else {

					echo "<td class='tab_bg_2' valign='top' align='center'>";
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"".$LANG["buttons"][7]."\" class='submit'>";
					echo "</td>\n\n";
					echo "<td class='tab_bg_2' valign='top'>\n";
					echo "<div align='center'>";
					if ($this->fields["deleted"]=='N')
						echo "<input type='submit' name='delete' value=\"".$LANG["buttons"][6]."\" class='submit'>";
					else {
						echo "<input type='submit' name='restore' value=\"".$LANG["buttons"][21]."\" class='submit'>";

						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"".$LANG["buttons"][22]."\" class='submit'>";
					}
					echo "</div>";
					echo "</td>";
				}
				echo "</tr>";
			}

			echo "</table></form></div>";

			return true;
		}
		else {
			echo "<div align='center'><b>".$LANG["monitors"][17]."</b></div>";
			return false;
		}



	}

}

?>
