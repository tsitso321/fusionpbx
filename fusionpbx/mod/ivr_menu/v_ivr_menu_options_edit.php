<?php
require_once "root.php";
require_once "includes/config.php";
require_once "includes/checkauth.php";
if (ifgroup("admin") || ifgroup("superadmin")) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//action add or update
	if (isset($_REQUEST["id"])) {
		$action = "update";
		$ivr_menu_option_id = check_str($_REQUEST["id"]);
	}
	else {
		$action = "add";
	}

//get the menu id
	if (strlen($_GET["ivr_menu_id"]) > 0) {
		$ivr_menu_id = check_str($_GET["ivr_menu_id"]);
	}

//get the http post variables and set them to php variables
	if (count($_POST)>0) {
		//$v_id = check_str($_POST["v_id"]);
		$ivr_menu_id = check_str($_POST["ivr_menu_id"]);
		$ivr_menu_options_digits = check_str($_POST["ivr_menu_options_digits"]);
		$ivr_menu_options_action = check_str($_POST["ivr_menu_options_action"]);
		$ivr_menu_options_param = check_str($_POST["ivr_menu_options_param"]);
		$ivr_menu_options_order = check_str($_POST["ivr_menu_options_order"]);
		$ivr_menu_options_desc = check_str($_POST["ivr_menu_options_desc"]);

		//set the default ivr_menu_options_action
			if (strlen($ivr_menu_options_action) == 0) {
				$ivr_menu_options_action = "menu-exec-app";
			}

		//seperate the action and the param
			$options_array = explode(":", $ivr_menu_options_param);
			$ivr_menu_options_action = array_shift($options_array);
			$ivr_menu_options_param = join(':', $options_array);
	}

if (count($_POST)>0 && strlen($_POST["persistformvar"]) == 0) {

	$msg = '';
	if ($action == "update") {
		$ivr_menu_option_id = check_str($_POST["ivr_menu_option_id"]);
	}

	//check for all required data
		//if (strlen($v_id) == 0) { $msg .= "Please provide: v_id<br>\n"; }
		//if (strlen($ivr_menu_id) == 0) { $msg .= "Please provide: ivr_menu_id<br>\n"; }
		if (strlen($ivr_menu_options_digits) == 0) { $msg .= "Please provide: Option<br>\n"; }
		//if (strlen($ivr_menu_options_action) == 0) { $msg .= "Please provide: Type<br>\n"; }
		//if (strlen($ivr_menu_options_param) == 0) { $msg .= "Please provide: Destination<br>\n"; }
		if (strlen($ivr_menu_options_order) == 0) { $msg .= "Please provide: Order<br>\n"; }
		//if (strlen($ivr_menu_options_desc) == 0) { $msg .= "Please provide: Description<br>\n"; }
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

	//add or update the database
		if ($_POST["persistformvar"] != "true") {
			if ($action == "add") {
				$sql = "insert into v_ivr_menu_options ";
				$sql .= "(";
				$sql .= "v_id, ";
				$sql .= "ivr_menu_id, ";
				$sql .= "ivr_menu_options_digits, ";
				$sql .= "ivr_menu_options_action, ";
				$sql .= "ivr_menu_options_param, ";
				$sql .= "ivr_menu_options_order, ";
				$sql .= "ivr_menu_options_desc ";
				$sql .= ")";
				$sql .= "values ";
				$sql .= "(";
				$sql .= "'$v_id', ";
				$sql .= "'$ivr_menu_id', ";
				$sql .= "'$ivr_menu_options_digits', ";
				$sql .= "'$ivr_menu_options_action', ";
				$sql .= "'$ivr_menu_options_param', ";
				$sql .= "'$ivr_menu_options_order', ";
				$sql .= "'$ivr_menu_options_desc' ";
				$sql .= ")";
				$db->exec(check_sql($sql));
				unset($sql);

				//synchronize the xml config
				sync_package_v_ivr_menu();

				require_once "includes/header.php";
				echo "<meta http-equiv=\"refresh\" content=\"2;url=v_ivr_menu_edit.php?id=$ivr_menu_id\">\n";
				echo "<div align='center'>\n";
				echo "Add Complete\n";
				echo "</div>\n";
				require_once "includes/footer.php";
				return;
			} //if ($action == "add")

			if ($action == "update") {
				$sql = "update v_ivr_menu_options set ";
				$sql .= "v_id = '$v_id', ";
				$sql .= "ivr_menu_id = '$ivr_menu_id', ";
				$sql .= "ivr_menu_options_digits = '$ivr_menu_options_digits', ";
				$sql .= "ivr_menu_options_action = '$ivr_menu_options_action', ";
				$sql .= "ivr_menu_options_param = '$ivr_menu_options_param', ";
				$sql .= "ivr_menu_options_order = '$ivr_menu_options_order', ";
				$sql .= "ivr_menu_options_desc = '$ivr_menu_options_desc' ";
				$sql .= "where ivr_menu_option_id = '$ivr_menu_option_id'";
				$db->exec(check_sql($sql));
				unset($sql);

				//synchronize the xml config
				sync_package_v_ivr_menu();

				require_once "includes/header.php";
				echo "<meta http-equiv=\"refresh\" content=\"2;url=v_ivr_menu_edit.php?id=$ivr_menu_id\">\n";
				echo "<div align='center'>\n";
				echo "Update Complete\n";
				echo "</div>\n";
				require_once "includes/footer.php";
				return;
			} //if ($action == "update")
		} //if ($_POST["persistformvar"] != "true")

} //(count($_POST)>0 && strlen($_POST["persistformvar"]) == 0)

//pre-populate the form
	if (count($_GET)>0 && $_POST["persistformvar"] != "true") {
		$ivr_menu_option_id = $_GET["id"];
		$sql = "";
		$sql .= "select * from v_ivr_menu_options ";
		$sql .= "where ivr_menu_option_id = '$ivr_menu_option_id' ";
		$sql .= "and v_id = '$v_id' ";
		$prepstatement = $db->prepare(check_sql($sql));
		$prepstatement->execute();
		$result = $prepstatement->fetchAll();
		foreach ($result as &$row) {
			$v_id = $row["v_id"];
			$ivr_menu_id = $row["ivr_menu_id"];
			$ivr_menu_options_digits = $row["ivr_menu_options_digits"];
			$ivr_menu_options_action = $row["ivr_menu_options_action"];
			$ivr_menu_options_param = $row["ivr_menu_options_param"];

			//if admin show only the param
				if (ifgroup("admin")) {
					$ivr_menu_options_label = $ivr_menu_options_param;
				}

			//if superadmin show both the action and param
				if (ifgroup("superadmin")) {
					$ivr_menu_options_label = $ivr_menu_options_action.':'.$ivr_menu_options_param;
				}

			$ivr_menu_options_order = $row["ivr_menu_options_order"];
			$ivr_menu_options_desc = $row["ivr_menu_options_desc"];
			break; //limit to 1 row
		}
		unset ($prepstatement);
	}

//send the content to the browser
	require_once "includes/header.php";


	echo "<div align='center'>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing=''>\n";

	echo "<tr class='border'>\n";
	echo "	<td align=\"left\">\n";
	echo "	  <br>";


	echo "<form method='post' name='frm' action=''>\n";

	echo "<div align='center'>\n";
	echo "<table width='100%'  border='0' cellpadding='6' cellspacing='0'>\n";

	echo "<tr>\n";
	if ($action == "add") {
		echo "<td align='left' width='30%' nowrap><b>IVR Menu Option Add</b></td>\n";
	}
	if ($action == "update") {
		echo "<td align='left' width='30%' nowrap><b>IVR Menu Option Edit</b></td>\n";
	}
	echo "<td width='70%' align='right'><input type='button' class='btn' name='' alt='back' onclick=\"window.location='v_ivr_menu_edit.php?id=$ivr_menu_id'\" value='Back'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan='2'>\n";
	echo "The recording presents options to the caller. Options match key presses (DTMF digits) from the caller which directs the call to the destinations. <br /><br />\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "	Option:\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "  <input class='formfld' type='text' name='ivr_menu_options_digits' maxlength='255' value='$ivr_menu_options_digits'>\n";
	echo "<br />\n";
	echo "Any number between 1-5 digits or regular expressions.\n";
	echo "</td>\n";
	echo "</tr>\n";

	/*
	if (ifgroup("superadmin")) {
		echo "<tr>\n";
		echo "<td class='vncell' valign='top' align='left' nowrap>\n";
		echo "	Type:\n";
		echo "</td>\n";
		echo "<td class='vtable' align='left'>\n";

		echo "		<select name='ivr_menu_options_action' class='formfld'>\n";
		echo "		<option></option>\n";
		if (strlen($ivr_menu_options_action) == 0) {
			echo "		<option value='menu-exec-app' selected='selected'>menu-exec-app</option>\n";
		}
		else {
			if ($ivr_menu_options_action == "menu-exec-app") {
				echo "		<option value='menu-exec-app' selected='selected'>menu-exec-app</option>\n";
			}
			else {
				echo "		<option value='menu-exec-app'>menu-exec-app</option>\n";
			}
		}
		if ($ivr_menu_options_action == "menu-sub") {
			echo "		<option value='menu-sub' selected='selected'>menu-sub</option>\n";
		}
		else {
			echo "		<option value='menu-sub'>menu-sub</option>\n";
		}
		if ($ivr_menu_options_action == "menu-exec-app") {
			echo "		<option value='menu-exec-app' selected='selected'>menu-exec-app</option>\n";
		}
		else {
			echo "		<option value='menu-exec-app'>menu-exec-app</option>\n";
		}
		if ($ivr_menu_options_action == "menu-top") {
			echo "		<option value='menu-top' selected='selected'>menu-top</option>\n";
		}
		else {
			echo "		<option value='menu-top'>menu-top</option>\n";
		}
		if ($ivr_menu_options_action == "menu-playback") {
			echo "		<option value='menu-playback' selected='selected'>menu-playback</option>\n";
		}
		else {
			echo "		<option value='menu-playback'>menu-playback</option>\n";
		}
		if ($ivr_menu_options_action == "menu-exit") {
			echo "		<option value='menu-exit' selected='selected'>menu-exit</option>\n";
		}
		else {
			echo "		<option value='menu-exit'>menu-exit</option>\n";
		}
		echo "		</select>\n";

		echo "<br />\n";
		echo "The type is required when a custom destination is defined. \n";
		echo "</td>\n";
		echo "</tr>\n";
	}
	*/

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "	Destination:\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";

	//switch_select_destination(select_type, select_label, select_name, select_value, select_style, action);
	switch_select_destination("ivr", $ivr_menu_options_label, "ivr_menu_options_param", $ivr_menu_options_action.':'.$ivr_menu_options_param, "", $ivr_menu_options_action);

	echo "<br />\n";
	echo "Select the destination.\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "	Order:\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select name='ivr_menu_options_order' class='formfld'>\n";
	//echo "	<option></option>\n";
	if (strlen(htmlspecialchars($ivr_menu_options_order))> 0) {
		echo "	<option selected='yes' value='".htmlspecialchars($ivr_menu_options_order)."'>".htmlspecialchars($ivr_menu_options_order)."</option>\n";
	}
	$i=0;
	while($i<=999) {
		if (strlen($i) == 1) {
			echo "	<option value='00$i'>00$i</option>\n";
		}
		if (strlen($i) == 2) {
			echo "	<option value='0$i'>0$i</option>\n";
		}
		if (strlen($i) == 3) {
			echo "	<option value='$i'>$i</option>\n";
		}
		$i++;
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo "Select the order.\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "	Description:\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='ivr_menu_options_desc' maxlength='255' value=\"$ivr_menu_options_desc\">\n";
	echo "<br />\n";
	echo "Enter a description here for your reference.\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	echo "				<input type='hidden' name='ivr_menu_id' value='$ivr_menu_id'>\n";
	if ($action == "update") {
		echo "				<input type='hidden' name='ivr_menu_option_id' value='$ivr_menu_option_id'>\n";
	}
	echo "				<input type='submit' name='submit' class='btn' value='Save'>\n";
	echo "		</td>\n";
	echo "	</tr>";
	echo "</table>";
	echo "</form>";

	echo "	</td>";
	echo "	</tr>";
	echo "</table>";
	echo "</div>";


require_once "includes/footer.php";
?>
