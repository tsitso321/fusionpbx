<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2010
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/
include "root.php";
require_once "includes/config.php";
require_once "includes/checkauth.php";
if (ifgroup("superadmin")) {
	//access granted
}
else {
	echo "access denied";
	return;
}

//Action add or update
if (isset($_REQUEST["menuid"])) {
	$action = "update";
	$menuid = check_str($_REQUEST["menuid"]);
}
else {
	$action = "add";
}

if (count($_POST)>0) {

	$_SESSION["menu"] = ""; //clear the menu session so it will rebuild with the update

	$menuid = check_str($_POST["menuid"]);
	$menutitle = check_str($_POST["menutitle"]);
	$menustr = check_str($_POST["menustr"]);
	$menucategory = check_str($_POST["menucategory"]);
	$menugroup = check_str($_POST["menugroup"]);
	$menudesc = check_str($_POST["menudesc"]);
	$menuparentid = check_str($_POST["menuparentid"]);
	$menuorder = check_str($_POST["menuorder"]);
}

if (count($_POST)>0 && strlen($_POST["persistformvar"]) == 0) {

	$msg = '';

	if ($action == "update") {
		$menuid = check_str($_POST["menuid"]);
	}

	//check for all required data
		//if (strlen($v_id) == 0) { $msg .= "Please provide: v_id<br>\n"; }
		if (strlen($menutitle) == 0) { $msg .= "Please provide: title<br>\n"; }
		if (strlen($menucategory) == 0) { $msg .= "Please provide: category<br>\n"; }
		//if (strlen($menustr) == 0) { $msg .= "Please provide: menustr<br>\n"; }
		//if (strlen($menugroup) == 0) { $msg .= "Please provide: menugroup<br>\n"; }
		if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
			require_once "includes/header.php";
			require_once "includes/persistformvar.php";
			echo "<div align='center'>\n";
			echo "<table><tr><td>\n";
			echo $msg."<br />";
			echo "</td></tr></table>\n";
			persistformvar($_POST);
			echo "</div>\n";
			require_once "includes/footer.php";
			return;
		}

	//Add or update the database
	if ($_POST["persistformvar"] != "true") {
		if ($action == "add") {

			$sql = "SELECT menuorder FROM v_menu ";
			$sql .= "where v_id = '$v_id' ";
			$sql .= "and menuparentid  = '$menuparentid' ";
			$sql .= "order by menuorder desc ";
			$sql .= "limit 1 ";
			$prepstatement = $db->prepare(check_sql($sql));
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			foreach ($result as &$row) {
				//print_r( $row );
				$highestmenuorder = $row[menuorder];
			}
			unset($prepstatement);

			$sql = "insert into v_menu ";
			$sql .= "(";
			$sql .= "v_id, ";
			$sql .= "menutitle, ";
			$sql .= "menustr, ";
			$sql .= "menucategory, ";
			$sql .= "menugroup, ";
			$sql .= "menudesc, ";
			$sql .= "menuparentid, ";
			$sql .= "menuorder, ";
			$sql .= "menuadduser, ";
			$sql .= "menuadddate ";
			$sql .= ")";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'$v_id', ";
			$sql .= "'$menutitle', ";
			$sql .= "'$menustr', ";
			$sql .= "'$menucategory', ";
			$sql .= "'$menugroup', ";
			$sql .= "'$menudesc', ";
			$sql .= "'$menuparentid', ";
			$sql .= "'".($highestmenuorder+1)."', ";
			$sql .= "'".$_SESSION["username"]."', ";
			$sql .= "now() ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);

			require_once "includes/header.php";
			echo "<meta http-equiv=\"refresh\" content=\"2;url=menu_list.php\">\n";
			echo "<div align='center'>\n";
			echo "Add Complete\n";
			echo "</div>\n";
			require_once "includes/footer.php";
			return;

		} //if ($action == "add")

		if ($action == "update") {
			$sql  = "update v_menu set ";
			$sql .= "menutitle = '$menutitle', ";
			$sql .= "menustr = '$menustr', ";
			$sql .= "menucategory = '$menucategory', ";
			$sql .= "menugroup = '$menugroup', ";
			$sql .= "menudesc = '$menudesc', ";
			$sql .= "menuparentid = '$menuparentid', ";
			$sql .= "menuorder = '$menuorder', ";
			$sql .= "menumoduser = '".$_SESSION["username"]."', ";
			$sql .= "menumoddate = now() ";
			$sql .= "where v_id = '$v_id' ";
			$sql .= "and menuid = '$menuid' ";
			//echo $sql;
			$count = $db->exec(check_sql($sql));

			require_once "includes/header.php";
			echo "<meta http-equiv=\"refresh\" content=\"2;url=menu_list.php\">\n";
			echo "<div align='center'>\n";
			echo "Edit Complete\n";
			echo "</div>\n";
			require_once "includes/footer.php";
			return;
		}  //if ($action == "update")
	} //if ($_POST["persistformvar"] != "true")
} //(count($_POST)>0 && strlen($_POST["persistformvar"]) == 0)

//Pre-populate the form
if (count($_GET)>0 && $_POST["persistformvar"] != "true") {
	//get data from the db
	$menuid = $_GET["menuid"];

	$sql = "";
	$sql .= "select * from v_menu ";
	$sql .= "where v_id = '$v_id' ";
	$sql .= "and menuid = '$menuid' ";
	$prepstatement = $db->prepare(check_sql($sql));
	$prepstatement->execute();
	$result = $prepstatement->fetchAll();
	foreach ($result as &$row) {
		$menutitle = $row["menutitle"];
		$menustr = $row["menustr"];
		$menucategory = $row["menucategory"];
		$menudesc = $row["menudesc"];
		$menuparentid = $row["menuparentid"];
		$menuorder = $row["menuorder"];
		$menugroup = $row["menugroup"];
		$menuadduser = $row["menuadduser"];
		$menuadddate = $row["menuadddate"];
		//$menudeluser = $row["menudeluser"];
		//$menudeldate = $row["menudeldate"];
		$menumoduser = $row["menumoduser"];
		$menumoddate = $row["menumoddate"];
		break; //limit to 1 row
	}
}

	require_once "includes/header.php";
	echo "<div align='center'>";
	echo "<table width='90%' border='0' cellpadding='0' cellspacing='2'>\n";

	echo "<tr class='border'>\n";
	echo "	<td align=\"left\">\n";
	echo "      <br>";


	echo "<form method='post' action=''>";
	echo "<table width='100%' cellpadding='6' cellspacing='0'>";

	echo "<tr>\n";
	echo "<td width='30%' align='left' valign='top' nowrap><b>Menu Edit</b></td>\n";
	echo "<td width='70%' align='right' valign='top'><input type='button' class='btn' name='' alt='back' onclick=\"window.location='menu_list.php'\" value='Back'><br /><br /></td>\n";
	echo "</tr>\n";

	echo "	<tr>";
	echo "		<td class='vncellreq'>Title:</td>";
	echo "		<td class='vtable'><input type='text' class='formfld' name='menutitle' value='$menutitle'></td>";
	echo "	</tr>";
	echo "	<tr>";
	echo "		<td class='vncellreq'>Link:</td>";
	echo "		<td class='vtable'><input type='text' class='formfld' name='menustr' value='$menustr'></td>";
	echo "	</tr>";
	echo "	<tr>";
	echo "		<td class='vncellreq'>Category:</td>";
	echo "		<td class='vtable'>";
	//echo "            <input type='text' class='formfld' name='menucategory' value='$menucategory'>";
	echo "            <select name=\"menucategory\" class='formfld'>\n";
	echo "            <option value=\"\"></option>\n";
	if ($menucategory == "internal") { echo "<option value=\"internal\" selected>internal</option>\n"; } else { echo "<option value=\"internal\">internal</option>\n"; }
	if ($menucategory == "external") { echo "<option value=\"external\" selected>external</option>\n"; } else { echo "<option value=\"external\">external</option>\n"; }
	if ($menucategory == "email") { echo "<option value=\"email\" selected>email</option>\n"; } else { echo "<option value=\"email\">email</option>\n"; }
	echo "            </select>";
	echo "        </td>";
	echo "	</tr>";
	echo "	<tr>";
	echo "		<td class='vncell'>Description:</td>";
	echo "		<td class='vtable'><input type='text' class='formfld' name='menudesc' value='$menudesc'></td>";
	echo "	</tr>";
	echo "	<tr>";
	echo "		<td class='vncell'>Parent Menu:</td>";
	echo "		<td class='vtable'>";
	//echo "            <input type='text' class='formfld' name='menuparentid' value='$menuparentid'>";

	//---- Begin Select List --------------------
	$sql = "SELECT * FROM v_menu ";
	$sql .= "where v_id = '$v_id' ";
	$sql .= "order by menutitle asc ";
	$prepstatement = $db->prepare(check_sql($sql));
	$prepstatement->execute();

	echo "<select name=\"menuparentid\" class='formfld'>\n";
	echo "<option value=\"\"></option>\n";
	$result = $prepstatement->fetchAll();
	//$count = count($result);
	foreach($result as $field) {
			if ($menuparentid == $field[menuid]) {
				echo "<option value='".$field[menuid]."' selected>".$field[menutitle]."</option>\n";
			}
			else {
				echo "<option value='".$field[menuid]."'>".$field[menutitle]."</option>\n";
			}
	}

	echo "</select>";
	unset($sql, $result);
	//---- End Select List --------------------

	echo "        </td>";
	echo "	</tr>";


	echo "	<tr>";
	echo "		<td class='vncellreq'>Group:</td>";
	echo "		<td class='vtable'>";
	//echo "            <input type='text' class='formfld' name='menuparentid' value='$menuparentid'>";

	//---- Begin Select List --------------------
	$sql = "SELECT * FROM v_groups ";
	$sql .= "where v_id = '$v_id' ";
	$prepstatement = $db->prepare(check_sql($sql));
	$prepstatement->execute();

	echo "<select name=\"menugroup\" class='formfld'>\n";
	echo "<option value=\"\">public</option>\n";
	$result = $prepstatement->fetchAll();
	//$count = count($result);
	foreach($result as $field) {
			if ($menugroup == $field[groupid]) {
				echo "<option value='".$field[groupid]."' selected>".$field[groupid]."</option>\n";
			}
			else {
				echo "<option value='".$field[groupid]."'>".$field[groupid]."</option>\n";
			}
	}

	echo "</select>";
	unset($sql, $result);
	//---- End Select List --------------------

	echo "        </td>";
	echo "	</tr>";


	if ($action == "update") {
		echo "	<tr>";
		echo "		<td class='vncell'>Menu Order:</td>";
		echo "		<td class='vtable'><input type='text' class='formfld' name='menuorder' value='$menuorder'></td>";
		echo "	</tr>";
		//echo "	<tr>";
		//echo "		<td class='vncell'>Added By:</td>";
		//echo "		<td class='vtable'>$menuadduser &nbsp;</td>";
		//echo "	</tr>";
		//echo "	<tr>";
		//echo "		<td class='vncell'>Add Date:</td>";
		//echo "		<td class='vtable'>$menuadddate &nbsp;</td>";
		//echo "	</tr>";
		//echo "	<tr>";
		//echo "		<td class='vncell'>Menudeluser:</td>";
		//echo "		<td><input type='text' name='menudeluser' value='$menudeluser'></td>";
		//echo "	</tr>";
		//echo "	<tr>";
		//echo "		<td class='vncell'>Menudeldate:</td>";
		//echo "		<td><input type='text' name='menudeldate' value='$menudeldate'></td>";
		//echo "	</tr>";
		//echo "	<tr>";
		//echo "		<td class='vncell'>Modified By:</td>";
		//echo "		<td class='vtable'>$menumoduser &nbsp;</td>";
		//echo "	</tr>";
		//echo "	<tr>";
		//echo "		<td class='vncell'>Modified Date:</td>";
		//echo "		<td class='vtable'>$menumoddate &nbsp;</td>";
		//echo "	</tr>";
	}

	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	echo "			<table width='100%'>";
	echo "			<tr>";
	echo "			<td align='left'>";
	echo "			</td>\n";
	echo "			<td align='right'>";
	if ($action == "update") {
		echo "              <input type='hidden' name='menuid' value='$menuid'>";
	}
	echo "              <input type='submit' class='btn' name='submit' value='Save'>\n";
	echo "          </td>";
	echo "          </tr>";
	echo "          </table>";
	echo "		</td>";
	echo "	</tr>";
	echo "</table>";
	echo "</form>";


	echo "	</td>";
	echo "	</tr>";
	echo "</table>";
	echo "</div>";


  require_once "includes/footer.php";
?>
