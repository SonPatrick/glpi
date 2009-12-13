<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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


define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

$dropdown = new Entity();
include (GLPI_ROOT . "/front/dropdown.common.form.php");
/*

} else if (isset($_POST["add_user_rule"])) {
   $entity->check($_POST["affectentity"],'w');

   $rule = new RuleRight;
   $ruleid = $rule->add($_POST);

   if ($ruleid) {
      //Add an action associated to the rule
      $ruleAction = new RuleAction;
      //Action is : affect computer to this entity
      $ruleAction->addActionByAttributes("assign", $ruleid, "entities_id", $_POST["affectentity"]);
      if ($_POST["profiles_id"]) {
         $ruleAction->addActionByAttributes("assign", $ruleid, "profiles_id", $_POST["profiles_id"]);
      }
      $ruleAction->addActionByAttributes("assign", $ruleid, "is_recursive", $_POST["is_recursive"]);
   }

   Event::log($ruleid, "rules", 4, "setup", $_SESSION["glpiname"]." ".$LANG['log'][22]);
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["delete_computer_rule"]) || isset($_POST["delete_user_rule"])) {
   $entity->check($_POST["affectentity"],'w');

   if (isset($_POST["delete_computer_rule"])) {
      $rule = new RuleOcs;
   } else {
      $rule = new RuleRight;
   }

   if (count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         $rule->delete(array('id' => $key));
      }
   }

   Event::log(0, "rules", 4, "setup", $_SESSION["glpiname"]." ".$LANG['log'][22]);
   glpi_header($_SERVER['HTTP_REFERER']);
}

commonHeader($LANG['Menu'][37],$_SERVER['PHP_SELF'],"admin","entity");

$entity->showForm($_SERVER['PHP_SELF'],$_GET["id"]);

commonFooter();
*/
?>
