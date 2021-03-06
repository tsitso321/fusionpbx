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

if (ifgroup("admin") || ifgroup("superadmin")) {
	//access allowed
}
else {
	echo "access denied";
	return;
}



$path = check_str($_GET["path"]);
$msg = check_str($_GET["msg"]);

$groupid = check_str($_POST["groupid"]);
$groupdesc = check_str($_POST["groupdesc"]);


if (strlen($groupid) > 0) {

	$sqlinsert = "insert into v_groups ";
	$sqlinsert .= "(";
	$sqlinsert .= "v_id, ";
	$sqlinsert .= "groupid, ";
	$sqlinsert .= "groupdesc ";
	$sqlinsert .= ")";
	$sqlinsert .= "values ";
	$sqlinsert .= "(";
	$sqlinsert .= "'$v_id', ";
	$sqlinsert .= "'$groupid', ";
	$sqlinsert .= "'$groupdesc' ";
	$sqlinsert .= ")";
	//echo $sqlinsert;
	if (!$db->exec($sqlinsert)) {
		//echo $db->errorCode() . "<br>";
		$info = $db->errorInfo();
		print_r($info);
		// $info[0] == $db->errorCode() unified error code
		// $info[1] is the driver specific error code
		// $info[2] is the driver specific error string
	}

	require_once "includes/header.php";
	echo "<meta http-equiv=\"refresh\" content=\"2;url=index.php\">\n";
	echo "<div align='center'>\n";
	echo "Group Added\n";
	echo "</div>\n";
	require_once "includes/footer.php";
	return;

}


include "includes/header.php";
echo "<br><br>";
echo "<div align='center'>";

echo "<table width='100%' cellpadding='6' cellspacing='0'>\n";
echo "<tr>\n";
echo "<td align='left'>\n";
echo "Please choose a group name. ";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "<br>";

echo "<form name='login' METHOD=\"POST\" action=\"groupadd.php\">\n";

echo "<table width='100%' cellpadding='6' cellspacing='0'>\n";
echo "<tr>\n";
echo "<td width='30%' class='vncellreq'>\n";
echo "Group Name:\n";
echo "</td>\n";
echo "<td width='70%' align='left' class='vtable'>\n";
echo "  <input type=\"text\" class='formfld' name=\"groupid\">\n";
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td class='vncellreq'>\n";
echo "Description:\n";
echo "</td>\n";
echo "<td align='left' class='vtable'>\n";
echo "<textarea name='groupdesc' class='formfld'></textarea>\n";
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>\n";
echo "</td>\n";
echo "<td align=\"right\">\n";
echo "  <input type=\"hidden\" name=\"path\" value=\"$path\">\n";
echo "  <input type=\"submit\" class='btn' value=\"Save\">\n";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</form>";

echo "</div>";


echo "<br><br>";
echo "<br><br>";


include "includes/footer.php";
?>
