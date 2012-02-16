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
	Portions created by the Initial Developer are Copyright (C) 2008-2012
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/
include "root.php";
require_once "includes/require.php";
require_once "includes/checkauth.php";
if (permission_exists('virtual_tables_data_add') || permission_exists('virtual_tables_data_edit')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//set http get variables to php variables
	$search_all = check_str($_GET["search_all"]);
	$virtual_table_uuid = check_str($_GET["virtual_table_uuid"]);
	if (strlen($_GET["virtual_data_row_id"])>0) { //update
		$virtual_data_row_id = check_str($_GET["virtual_data_row_id"]);
		$action = "update";
	}
	else {
		if (strlen($search_all) > 0) {
			$action = "update";
		}
		else {
			$action = "add";
		}
	}
	if (strlen($_GET["id"]) > 0) {
		$virtual_table_uuid = check_str($_GET["id"]);
	}
	if (strlen($_GET["virtual_data_parent_row_id"])>0) {
		$virtual_data_parent_row_id = check_str($_GET["virtual_data_parent_row_id"]);
	}

//get virtual table information
	$sql = "";
	$sql .= "select * from v_virtual_tables ";
	$sql .= "where domain_uuid = '$domain_uuid' ";
	$sql .= "and virtual_table_uuid = '$virtual_table_uuid' ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll();
	foreach ($result as &$row) {
		$virtual_table_category = $row["virtual_table_category"];
		$virtual_table_label = $row["virtual_table_label"];
		$virtual_table_name = $row["virtual_table_name"];
		$virtual_table_auth = $row["virtual_table_auth"];
		$virtual_table_captcha = $row["virtual_table_captcha"];
		$virtual_table_parent_id = $row["virtual_table_parent_id"];
		$virtual_table_desc = $row["virtual_table_desc"];
		break; //limit to 1 row
	}
	unset ($prep_statement);

//process the data submitted to by the html form
	if (count($_POST)>0) { //add
		$virtual_table_uuid = check_str($_POST["virtual_table_uuid"]);
		$virtual_table_name = check_str($_POST["virtual_table_name"]);
		$rcount = check_str($_POST["rcount"]);

		//get the field information
			$db_field_name_array = array();
			$db_value_array = array();
			$db_names .= "<tr>\n";
			$sql = "select * from v_virtual_table_fields ";
			$sql .= "where domain_uuid = '$domain_uuid' ";
			$sql .= "and virtual_table_uuid = '$virtual_table_uuid' ";
			$sql .= "order by virtual_field_order asc ";
			$prep_statement = $db->prepare($sql);
			$prep_statement->execute();
			$result_names = $prep_statement->fetchAll(PDO::FETCH_ASSOC);
			$result_count = count($result);
			foreach($result_names as $row) {
				$virtual_field_label = $row["virtual_field_label"];
				$virtual_field_name = $row["virtual_field_name"];
				$virtual_field_type = $row["virtual_field_type"];
				$virtual_field_value = $row["virtual_field_value"];
				$virtual_field_list_hidden = $row["virtual_field_list_hidden"];
				$virtual_field_column = $row["virtual_field_column"];
				$virtual_field_required = $row["virtual_field_required"];
				$virtual_field_order = $row["virtual_field_order"];
				$virtual_field_order_tab = $row["virtual_field_order_tab"];
				$virtual_field_desc = $row["virtual_field_desc"];

				$name_array[$virtual_field_name]['virtual_field_label'] = $row["virtual_field_label"];
				$name_array[$virtual_field_name]['virtual_field_type'] = $row["virtual_field_type"];
				$name_array[$virtual_field_name]['virtual_field_list_hidden'] = $row["virtual_field_list_hidden"];
				$name_array[$virtual_field_name]['virtual_field_column'] = $row["virtual_field_column"];
				$name_array[$virtual_field_name]['virtual_field_required'] = $row["virtual_field_required"];
				$name_array[$virtual_field_name]['virtual_field_order'] = $row["virtual_field_order"];
				$name_array[$virtual_field_name]['virtual_field_order_tab'] = $row["virtual_field_order_tab"];
				$name_array[$virtual_field_name]['virtual_field_desc'] = $row["virtual_field_desc"];
			}
			unset($sql, $prep_statement, $row);
			$fieldcount = count($name_array);

		$i = 1;
		while($i <= $rcount){
			$virtual_field_name = check_str($_POST[$i."field_name"]);
			$virtual_data_field_value = check_str($_POST[$i."field_value"]);
			if ($i==1) {
				$unique_temp_id = md5('7k3j2m'.date('r')); //used to find the first item
				$virtual_data_row_id = $unique_temp_id;
			}
			$sql = "select virtual_field_type, virtual_field_name from v_virtual_table_fields ";
			$sql .= "where domain_uuid  = '$domain_uuid' ";
			$sql .= "and virtual_table_uuid  = '$virtual_table_uuid' ";
			$sql .= "and virtual_field_name = '$virtual_field_name' ";
			$prep_statement = $db->prepare($sql);
			$prep_statement->execute();
			while($row = $prep_statement->fetch()){
				$virtual_field_type = $row['virtual_field_type'];
			}

			if ($virtual_field_type == "upload_file" || $virtual_field_type == "uploadimage") {
				//print_r($_FILES);
				$upload_temp_dir = $_ENV["TEMP"]."\\";
				ini_set('upload_tmp_dir', $upload_temp_dir);
				//$uploaddir = "";
				if ($virtual_field_type == "upload_file") {
					$upload_file = $filedir . $_FILES[$i.'field_value']['name'];
				}
				if ($virtual_field_type == "uploadimage") {
					$upload_file = $imagedir . $_FILES[$i.'field_value']['name'];
				}
				//  $_POST[$i."field_name"]
				//print_r($_FILES);
				//echo "upload_file $upload_file<br>\n";
				//echo "upload_temp_dir $upload_temp_dir<br>\n";

				$virtual_data_field_value = $_FILES[$i.'field_value']['name'];
				//echo "name $virtual_data_field_value<br>\n";
				//echo "virtual_field_name $virtual_field_name<br>\n";
				//$i."field_value"
				//echo "if (move_uploaded_file(\$_FILES[$i.'field_value']['tmp_name'], $upload_file)) ";
				//if (strlen($_FILES[$i.'field_value']['name'])>0) { //only do the following if there is a file name
					//foreach($_FILES as $file)
					//{
						//[$i.'field_value']
						//print_r($file);
						if($_FILES[$i.'field_value']['error'] == 0 && $_FILES[$i.'field_value']['size'] > 0) {
								if (move_uploaded_file($_FILES[$i.'field_value']['tmp_name'], $upload_file)) {
									//echo $_FILES['userfile']['name'] ." <br>";
									//echo "was successfully uploaded. ";
									//echo "<br><br>";
									//print "<pre>";
									//print_r($_FILES);
									//print "</pre>";
								}
								else {
									//echo "Upload Error.  Here's some debugging info:\n";
									//print "<pre>\n";
									//print_r($_FILES);
									//print "</pre>\n";
									//exit;
								}
						}
					//}
				//}
			} //end if file or image

			if ($action == "add" && permission_exists('virtual_tables_data_add')) {
				//get a unique id for the virtual_data_row_id
					if ($i==1) {
						$virtual_data_row_id = uuid();
					}

				//insert the field data
					$sql = "insert into v_virtual_table_data ";
					$sql .= "(";
					$sql .= "domain_uuid, ";
					$sql .= "virtual_data_row_id, ";
					if(strlen($virtual_data_parent_row_id)>0) {
						$sql .= "virtual_data_parent_row_id, ";
					}
					$sql .= "virtual_table_uuid, ";
					if (strlen($virtual_table_parent_id) > 0) {
						$sql .= "virtual_table_parent_id, ";
					}
					$sql .= "virtual_field_name, ";
					$sql .= "virtual_data_field_value, ";
					$sql .= "virtual_data_add_user, ";
					$sql .= "virtual_data_add_date ";
					$sql .= ")";
					$sql .= "values ";
					$sql .= "(";
					$sql .= "'$domain_uuid', ";
					$sql .= "'$virtual_data_row_id', ";
					if(strlen($virtual_data_parent_row_id)>0) {
						$sql .= "'$virtual_data_parent_row_id', ";
					}
					$sql .= "'$virtual_table_uuid', ";
					if (strlen($virtual_table_parent_id) > 0) {
						$sql .= "'$virtual_table_parent_id', ";
					}
					$sql .= "'$virtual_field_name', ";
					switch ($name_array[$virtual_field_name]['virtual_field_type']) {
						case "phone":
							$tmp_phone = preg_replace('{\D}', '', $virtual_data_field_value);
							$sql .= "'$tmp_phone', ";
							break;
						case "add_user":
							$sql .= "'".$_SESSION["username"]."', ";
							break;
						case "add_date":
							$sql .= "now(), ";
							break;
						case "mod_user":
							$sql .= "'".$_SESSION["username"]."', ";
							break;
						case "mod_date":
							$sql .= "now(), ";
							break;
						default:
							$sql .= "'$virtual_data_field_value', ";
					}
					$sql .= "'".$_SESSION["username"]."', ";
					$sql .= "now() ";
					$sql .= ")";
					$db->exec(check_sql($sql));
					$lastinsertid = $db->lastInsertId($id);
					unset($sql);
			} //end action add

			if ($action == "update" && permission_exists('virtual_tables_data_edit')) {
					$virtual_data_row_id = $_POST["virtual_data_row_id"];

					$sql_update  = "update v_virtual_table_data set ";
					switch ($name_array[$virtual_field_name]['virtual_field_type']) {
						case "phone":
							$tmp_phone = preg_replace('{\D}', '', $virtual_data_field_value);
							$sql_update .= "virtual_data_field_value = '$tmp_phone' ";
							break;
						case "add_user":
							$sql_update .= "virtual_data_field_value = '".$_SESSION["username"]."' ";
							break;
						case "add_date":
							$sql_update .= "virtual_data_field_value = now() ";
							break;
						case "mod_user":
							$sql_update .= "virtual_data_field_value = '".$_SESSION["username"]."' ";
							break;
						case "mod_date":
							$sql_update .= "virtual_data_field_value = now() ";
							break;
						default:
							$sql_update .= "virtual_data_field_value = '$virtual_data_field_value' ";
					}
					$sql_update .= "where domain_uuid = '$domain_uuid' ";
					$sql_update .= "and virtual_table_uuid = '$virtual_table_uuid' ";
					if (strlen($virtual_table_parent_id) > 0) {
						$sql_update .= "and virtual_table_parent_id = '$virtual_table_parent_id' ";
					}
					$sql_update .= "and virtual_data_row_id = '$virtual_data_row_id' ";
					if(strlen($virtual_data_parent_row_id)>0) {
						$sql_update .= "and virtual_data_parent_row_id = '$virtual_data_parent_row_id' ";
					}
					$sql_update .= "and virtual_field_name = '$virtual_field_name' ";
					$count = $db->exec(check_sql($sql_update));
					unset ($sql_update);
					if ($count > 0) {
						//do nothing the update was successfull
					}
					else {
						//no value to update so insert new value
						$sql = "insert into v_virtual_table_data ";
						$sql .= "(";
						$sql .= "domain_uuid, ";
						$sql .= "virtual_data_row_id, ";
						if(strlen($virtual_data_parent_row_id)>0) {
							$sql .= "virtual_data_parent_row_id, ";
						}
						$sql .= "virtual_table_uuid, ";
						$sql .= "virtual_table_parent_id, ";
						$sql .= "virtual_field_name, ";
						$sql .= "virtual_data_field_value, ";
						$sql .= "virtual_data_add_user, ";
						$sql .= "virtual_data_add_date ";
						$sql .= ")";
						$sql .= "values ";
						$sql .= "(";
						$sql .= "'$domain_uuid', ";
						$sql .= "'$virtual_data_row_id', ";
						if(strlen($virtual_data_parent_row_id)>0) {
							$sql .= "'$virtual_data_parent_row_id', ";
						}
						$sql .= "'$virtual_table_uuid', ";
						$sql .= "'$virtual_table_parent_id', ";
						$sql .= "'$virtual_field_name', ";
						switch ($name_array[$virtual_field_name]['virtual_field_type']) {
							case "phone":
								$tmp_phone = preg_replace('{\D}', '', $virtual_data_field_value);
								$sql .= "'$tmp_phone', ";
								break;
							case "add_user":
								$sql .= "'".$_SESSION["username"]."', ";
								break;
							case "add_date":
								$sql .= "now(), ";
								break;
							case "mod_user":
								$sql .= "'".$_SESSION["username"]."', ";
								break;
							case "mod_date":
								$sql .= "now(), ";
								break;
							default:
								$sql .= "'$virtual_data_field_value', ";
						}
						$sql .= "'".$_SESSION["username"]."', ";
						$sql .= "now() ";
						$sql .= ")";

						$db->exec(check_sql($sql));
						$lastinsertid = $db->lastInsertId($id);
						unset($sql);
					}
			}
			$i++;
		}

		//show the header
			require_once "includes/header.php";

		//set the meta redirect
			if (strlen($virtual_data_parent_row_id) == 0) {
				echo "<meta http-equiv=\"refresh\" content=\"2;url=v_virtual_table_data_edit.php?id=$virtual_table_uuid&virtual_data_row_id=$virtual_data_row_id\">\n";
			}
			else {
				echo "<meta http-equiv=\"refresh\" content=\"2;url=v_virtual_table_data_edit.php?virtual_table_uuid=$virtual_table_parent_id&virtual_data_row_id=$virtual_data_parent_row_id\">\n";
			}

		//show a message to the user before the redirect
			echo "<div align='center'>\n";
			if ($action == "add") { echo "Add Complete\n"; }
			if ($action == "update") { echo "Update Complete\n"; }
			echo "</div>\n";
			require_once "includes/footer.php";
			return;
	}

//show the header
	require_once "includes/header.php";

//pre-populate the form
	if ($action == "update") {
		//get the field values
			$sql = "";
			$sql .= "select * from v_virtual_table_data ";
			$sql .= "where domain_uuid = '".$domain_uuid."' ";
			if (strlen($search_all) == 0) {
				$sql .= "and virtual_table_uuid = '$virtual_table_uuid' ";
				if (strlen($virtual_data_parent_row_id) > 0) {
					$sql .= " and virtual_data_parent_row_id = '$virtual_data_parent_row_id' ";
				}
			}
			else {
				$sql .= "and virtual_data_row_id in (";
				$sql .= "select virtual_data_row_id from v_virtual_table_data \n";
				$sql .= "where domain_uuid = '".$domain_uuid."' ";
				$sql .= "and virtual_table_uuid = '$virtual_table_uuid' ";
				if (strlen($virtual_data_parent_row_id) > 0) {
					$sql .= " and virtual_data_parent_row_id = '$virtual_data_parent_row_id' ";
				}
				else {
					//$sql .= "and virtual_data_field_value like '%$search_all%' )\n";
					$tmp_digits = preg_replace('{\D}', '', $search_all);
					if (is_numeric($tmp_digits) && strlen($tmp_digits) > 5) {
						if (strlen($tmp_digits) == '11' ) {
							$sql .= "and virtual_data_field_value like '%".substr($tmp_digits, -10)."%' )\n";
						}
						else {
							$sql .= "and virtual_data_field_value like '%$tmp_digits%' )\n";
						}
					}
					else {
						$sql .= "and virtual_data_field_value like '%$search_all%' )\n";
					}
				}
			}
			$sql .= "order by virtual_data_row_id asc ";

			$row_id = '';
			$row_id_found = false;
			$next_row_id_found = false;
			$prep_statement = $db->prepare($sql);
			$prep_statement->execute();
			$x=0;
			while($row = $prep_statement->fetch()) {
				//set the last last row id
					if ($x==0) {
						if (strlen($virtual_data_row_id) == 0) {
							$virtual_data_row_id = $row['virtual_data_row_id'];
						}
						$first_virtual_data_row_id = $row['virtual_data_row_id'];
					}
				//get the data for the specific row id
					if ($virtual_data_row_id == $row['virtual_data_row_id']) {
						//set the data and save it to an array
							$data_row[$row['virtual_field_name']] = $row['virtual_data_field_value'];
						//set the previous row id
							if ($previous_row_id != $row['virtual_data_row_id']) {
								$previous_virtual_data_row_id = $previous_row_id;
								$row_id_found = true;
							}
					}
				//detect a new row id
					if ($previous_row_id != $row['virtual_data_row_id']) {
						if ($row_id_found) { 
							if (!$next_row_id_found) {
								//make sure it is not the current row id
								if ($virtual_data_row_id != $row['virtual_data_row_id']) {
									$next_virtual_data_row_id = $row['virtual_data_row_id'];
									$next_row_id_found = true;
								}
							}
						}

						//set the last last row id
							$last_virtual_data_row_id = $row['virtual_data_row_id'];

						//set the temporary previous row id
							$previous_row_id = $row['virtual_data_row_id'];

						//set the record number array
							$record_number_array[$row['virtual_data_row_id']] = $x+1;

						$x++;
					}
			}

			//save the total number of records
				$total_records = $x;

			//set record number
				if (strlen($_GET["n"]) == 0) { 
					$n = 1;
				}
				else {
					$n = $_GET["n"];
				}
			unset($sql, $prep_statement, $row);
	}

//use this when the calendar is needed
	//echo "<script language='javascript' src=\"/includes/calendar_popcalendar.js\"></script>\n";
	//echo "<script language=\"javascript\" src=\"/includes/calendar_lw_layers.js\"></script>\n";
	//echo "<script language=\"javascript\" src=\"/includes/calendar_lw_menu.js\"></script>";

//begin creating the content 
	echo "<br />";

//get the title and description of the virtual table
	echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo "	<tr>\n";
	echo "		<td width='50%' valign='top' nowrap='nowrap'>\n";
	echo 	"	<b>$virtual_table_label \n";
	if ($action == "add") {
		echo 	"	Add\n";
	}
	else {
		echo 	"Edit\n";
	}
	echo "	</b>\n";
	echo "	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	if ($action == "update" && permission_exists('virtual_tables_data_edit')) {
		echo "	<input type='button' class='btn' name='' alt='add' onclick=\"window.location='v_virtual_table_data_edit.php?virtual_table_uuid=$virtual_table_uuid'\" value='Add'>\n";
		//echo "	<input type='button' class='btn' name='' alt='delete' onclick=\"if (confirm('Do you really want to delete this?')){window.location='v_virtual_table_data_delete.php?id=".$virtual_table_uuid."&?virtual_data_row_id=".$virtual_data_row_id."&virtual_data_parent_row_id=$virtual_data_parent_row_id';}\" value='Delete'>\n";
	}
	echo "			<br />\n";
	echo "			$virtual_table_desc\n";
	echo "			<br />\n";
	echo "			<br />\n";
	echo "		</td>\n";

	if (strlen($virtual_data_parent_row_id) == 0) {
		echo "<td align='center' valign='top' nowrap='nowrap'>\n";

		if ($action == "update" && permission_exists('virtual_tables_data_edit')) {
			//echo "		<input type='button' class='btn' name='' alt='first' onclick=\"window.location='v_virtual_table_data_edit.php?virtual_table_uuid=$virtual_table_uuid&virtual_data_row_id=".$first_virtual_data_row_id."'\" value='First'>\n";
			if (strlen($previous_virtual_data_row_id) == 0) {
				echo "		<input type='button' class='btn' name='' alt='prev' disabled='disabled' value='Prev'>\n";
			}
			else {
				echo "		<input type='button' class='btn' name='' alt='prev' onclick=\"window.location='v_virtual_table_data_edit.php?virtual_table_uuid=$virtual_table_uuid&virtual_data_row_id=".$previous_virtual_data_row_id."&search_all=$search_all&n=".($n-1)."'\" value='Prev ".$previous_record_id."'>\n";
			}
			echo "		<input type='button' class='btn' name='' alt='prev' value='".$record_number_array[$virtual_data_row_id]." of $total_records'>\n";
			if (strlen($next_virtual_data_row_id) == 0) {
				echo "		<input type='button' class='btn' name='' alt='next' disabled='disabled' value='Next'>\n";
			}
			else {
				echo "		<input type='button' class='btn' name='' alt='next' onclick=\"window.location='v_virtual_table_data_edit.php?virtual_table_uuid=$virtual_table_uuid&virtual_data_row_id=".$next_virtual_data_row_id."&search_all=$search_all&n=".($n+1)."'\" value='Next ".$next_record_id."'>\n";
			}
			//echo "		<input type='button' class='btn' name='' alt='last' onclick=\"window.location='v_virtual_table_data_edit.php?virtual_table_uuid=$virtual_table_uuid&virtual_data_row_id=".$last_virtual_data_row_id."'\" value='Last'>\n";
		}
		echo "		&nbsp;&nbsp;&nbsp;";
		echo "		&nbsp;&nbsp;&nbsp;";
		echo "		&nbsp;&nbsp;&nbsp;";
		echo "</td>\n";

		echo "<form method='GET' name='frm_search' action='v_virtual_table_data_edit.php'>\n";
		echo "<td width='45%' align='right' valign='top' nowrap='nowrap'>\n";
		echo "	<input type='hidden' name='virtual_table_uuid' value='$virtual_table_uuid'>\n";
		//echo "	<input type='hidden' name='id' value='$virtual_table_uuid'>\n";
		//echo "	<input type='hidden' name='virtual_data_parent_row_id' value='$virtual_data_parent_row_id'>\n";
		//echo "	<input type='hidden' name='virtual_data_row_id' value='$first_virtual_data_row_id'>\n";
		echo "	<input class='formfld' type='text' name='search_all' value='$search_all'>\n";
		echo "	<input class='btn' type='submit' name='submit' value='Search All'>\n";
		echo "</td>\n";
		echo "</form>\n";
		echo "<td width='5%' align='right' valign='top' nowrap='nowrap'>\n";
		echo "		<input type='button' class='btn' name='' alt='back' onclick=\"window.location='v_virtual_table_data_view.php?id=$virtual_table_uuid'\" value='Back'>\n";
		echo "</td>\n";
	}
	else {
		echo "	<td width='50%' align='right'>\n";
		//echo "		<input type='button' class='btn' name='' alt='prev' onclick=\"window.location='v_virtual_table_data_edit.php?virtual_table_uuid=$virtual_table_parent_id&virtual_data_row_id=$virtual_data_parent_row_id'\" value='Prev'>\n";
		//echo "		<input type='button' class='btn' name='' alt='next' onclick=\"window.location='v_virtual_table_data_edit.php?virtual_table_uuid=$virtual_table_parent_id&virtual_data_row_id=$virtual_data_parent_row_id'\" value='Next'>\n";
		echo "		<input type='button' class='btn' name='' alt='back' onclick=\"window.location='v_virtual_table_data_edit.php?virtual_table_uuid=$virtual_table_parent_id&virtual_data_row_id=$virtual_data_parent_row_id'\" value='Back'>\n";
		echo "	</td>\n";
	}
	echo "  </tr>\n";
	echo "</table>\n";

//begin the div and table that will hold the html form
	echo "<div align='center'>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='10'>\n";

//determine if a file should be uploaded
	$sql = "SELECT * FROM v_virtual_table_fields ";
	$sql .= "where domain_uuid  = '$domain_uuid ' ";
	$sql .= "and virtual_table_uuid  = '$virtual_table_uuid ' ";
	$sql .= "and virtual_field_type = 'uploadimage' ";
	$sql .= "or domain_uuid  = '$domain_uuid ' ";
	$sql .= "and virtual_table_uuid  = '$virtual_table_uuid ' ";
	$sql .= "and virtual_field_type = 'upload_file' ";
	$prep_statement = $db->prepare($sql);
	$prep_statement->execute();
	if (count($prep_statement->fetchAll()) > 0) {
		echo "<form method='post' name='frm' enctype='multipart/form-data' action=''>\n";
		echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"104857600\" />\n";
	}
	else {
		echo "<form method='post' name='frm' action=''>\n";
	}

//get the table fields and then display them
	$sql = "";
	$sql .= "select * from v_virtual_table_fields ";
	$sql .= "where domain_uuid  = '$domain_uuid' ";
	$sql .= "and virtual_table_uuid  = '$virtual_table_uuid' ";
	$sql .= "order by virtual_field_column asc, virtual_field_order asc ";
	$prep_statement = $db->prepare($sql);
	$prep_statement->execute();
	$result = $prep_statement->fetchAll();
	$result_count = count($result);

	echo "<input type='hidden' name='rcount' value='$result_count'>\n";
	echo "<input type='hidden' name='virtual_table_uuid' value='$virtual_table_uuid'>\n";

	if ($result_count == 0) { //no results
		echo "<tr><td class='vncell'>&nbsp;</td></tr>\n";
	}
	else { //received results
		$x=1;
		$virtual_field_column_previous = '';
		$column_table_cell_status = '';
		foreach($result as $row) {
			//handle more than one column
				$virtual_field_column = $row[virtual_field_column];
				//echo "<!--[column: $virtual_field_column]-->\n";
				if ($virtual_field_column != $virtual_field_column_previous) {
					$column_table_cell_status = 'open';
					//do the following except for the first time through the loop
						if ($x != 1) {
							//close the table
								echo "</td>\n";
								echo "</tr>\n";
								echo "</table>\n";
							//close the row
								echo "</td>\n";
						}
					//open a new row
						echo "<td valign='top'>\n";
					//start a table in the new row
						echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				}

			//display the fields
					if ($row['virtual_field_type'] != "hidden"){
						switch ($row['virtual_field_type']) {
						case "add_user":
							break;
						case "add_date":
							break;
						case "mod_user":
							break;
						case "mod_date":
							break;
						default:
							echo "<tr>\n";
							if ($row['virtual_field_type'] == "label") {
								echo "<td valign='bottom' align='left' class='' style='padding-top:10px;padding-bottom:7px;padding-right:5px;padding-left:0px;' nowrap='nowrap'>\n";
								echo "	<strong>".$row['virtual_field_label']."</strong>\n";
								echo "</td>\n";
							}
							else {
								if ($row['virtual_field_required'] == "yes") {
									echo "<td valign='top' align='left' class='vncellreq' style='padding-top:3px;' nowrap='nowrap'>\n";
								}
								else {
									echo "<td valign='top' align='left' class='vncell' style='padding-top:3px;' nowrap='nowrap'>\n";
								}
								echo "".$row['virtual_field_label'].": \n";
								echo "</td>\n";
							}
						}
					}
					switch ($row['virtual_field_type']) {
						case "checkbox":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							if (strlen($data_row[$row['virtual_field_name']])>0) {
								echo "<input tabindex='".$row['virtual_field_order_tab']."' class='' type='checkbox' name='".$x."field_value' maxlength='50' value=\"".$row['virtual_field_value']."\" checked='checked'/>\n";
							}
							else {
								echo "<input tabindex='".$row['virtual_field_order_tab']."' class='' type='checkbox' name='".$x."field_value' maxlength='50' value=\"".$row['virtual_field_value']."\" />\n";
							}
							echo "</td>\n";
							break;
						case "text":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%' type='text' name='".$x."field_value' maxlength='50' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							echo "</td>\n";
							break;
						case "email":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='text' name='".$x."field_value' maxlength='50' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							echo "</td>\n";
							break;
						case "label":
							break;
						case "password":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='password' name='".$x."field_value' maxlength='50' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							echo "</td>\n";
							break;
						case "pin_number":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value=\"".$row['virtual_field_name']."\">\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='password' name='".$x."field_value' maxlength='50' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							echo "</td>\n";
							break;
						case "hidden":
							echo "<input type='hidden' name='".$x."field_name' value=\"".$row['virtual_field_name']."\">\n";
							echo "<input type='hidden' name='".$x."field_value' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							break;
						case "url":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value=\"".$row['virtual_field_name']."\">\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='text' name='".$x."field_value' maxlength='50' value='".$data_row[$row['virtual_field_name']]."'>\n";
							echo "</td>\n";
							break;
						case "date":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value=\"".$row['virtual_field_name']."\">\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='text' name='".$x."field_value' maxlength='50' value='".$data_row[$row['virtual_field_name']]."'>\n";

							//echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							//echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
							//echo "<tr>";
							//echo "<td valign='top'><input tabindex='".$row['virtual_field_order_tab']."' name='".$x."field_value' readonly class='formfld' style='width:90%'  value='".$data_row[$row['virtual_field_name']]."' type='text' class='frm' onclick='popUpCalendar(this, this, \"mm/dd/yyyy\");'></td>\n";
							//echo "<td valign='middle' width='20' align='right'><img src='/images/icon_calendar.gif' onclick='popUpCalendar(this, frm.".$x."field_value, \"mm/dd/yyyy\");'></td>	\n";
							//echo "</tr>";
							//echo "</table>";
							//echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='text' name='".$x."field_value' maxlength='50' value='".$data_row[$row['virtual_field_name']]."'>\n";
							echo "</td>\n";
							break;
						case "truefalse":
							//checkbox
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value=\"".$row['virtual_field_name']."\">\n";
							echo "<table border='0'>\n";
							echo "<tr>\n";
							switch ($row['virtual_field_name']) {
								case "true":
									echo "<td>True</td><td width='50'><input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='checkbox' name='".$x."field_value' checked='checked' value='true' /></td>\n";
									echo "<td>False</td><td><input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='checkbox' name='".$x."field_value' value='false'></td>\n";
									break;
								case "false":
									echo "<td>True</td><td width='50'><input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='checkbox' name='".$x."field_value' value='true' /></td>\n";
									echo "<td>False</td><td><input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='checkbox' name='".$x."field_value' checked='checked' value='false' /></td>\n";
									break;
								default:
									echo "<td>True</td><td width='50'><input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='checkbox' name='".$x."field_value' value='true' /></td>\n";
									echo "<td>False</td><td><input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='checkbox' name='".$x."field_value' value='false' /></td>\n";
							}

							echo "</tr>\n";
							echo "</table>\n";
							echo "</td>\n";
							break;
						case "textarea":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<textarea tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  name='".$x."field_value' rows='4'>".$data_row[$row['virtual_field_name']]."</textarea>\n";
							echo "</td>\n";
							break;
						case "radiobutton":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name=\"".$x."field_name\" value=\"".$row['virtual_field_name']."\">\n";

							$sqlselect = "SELECT virtual_data_types_name, virtual_data_types_value ";
							$sqlselect .= "FROM v_virtual_table_data_types_name_value ";
							$sqlselect .= "where domain_uuid = '".$domain_uuid."' ";
							$sqlselect .= "and virtual_table_field_uuid = '".$row[virtual_table_field_uuid]."' ";
							$prep_statement_2 = $db->prepare($sqlselect);
							$prep_statement_2->execute();
							$result2 = $prep_statement_2->fetchAll();
							$result_count2 = count($result2);

							echo "<table>";
							if ($result_count > 0) {
								foreach($result2 as $row2) {
										echo "<tr><td>".$row2["virtual_data_types_name"]."</td><td><input tabindex='".$row['virtual_field_order_tab']."' type='radio' name='".$x."field_value' value='".$row2["virtual_data_types_select_value"]."'";
										if ($row2["virtual_data_types_value"] == $data_row[$row['virtual_field_name']]) { echo " checked>"; } else { echo ">"; }
										echo "</td></tr>";
								} //end foreach
							} //end if results
							unset($sqlselect, $result2, $result_count2);
							echo "</table>";
							//echo "</select>\n";
							echo "</td>\n";
							break;
						case "select":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";

							$sqlselect = "SELECT virtual_data_types_name, virtual_data_types_value ";
							$sqlselect .= "FROM v_virtual_table_data_types_name_value ";
							$sqlselect .= "where domain_uuid = '".$domain_uuid."' ";
							$sqlselect .= "and virtual_table_field_uuid = '".$row[virtual_table_field_uuid]."' ";
							$prep_statement_2 = $db->prepare($sqlselect);
							$prep_statement_2->execute();
							$result2 = $prep_statement_2->fetchAll();
							$result_count2 = count($result2);

							echo "<select tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  name='".$x."field_value'>\n";
							echo "<option value=''></option>\n";
							if ($result_count > 0) {
								foreach($result2 as $row2) {
										echo "<option value=\"" . $row2["virtual_data_types_value"] . "\"";
										if (strtolower($row2["virtual_data_types_value"]) == strtolower($data_row[$row['virtual_field_name']])) { echo " selected='selected' "; }
										echo ">" . $row2["virtual_data_types_name"] . "</option>\n";
								} //end foreach
							} //end if results
							unset($sqlselect, $result2, $result_count2);
							echo "</select>\n";
							echo "</td>\n";
							break;
						case "ipv4":
							//max 15
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='text' name='".$x."field_value' maxlength='15' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							echo "</td>\n";
							break;
						case "ipv6":
							//maximum number of characters 39
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='text' name='".$x."field_value' maxlength='39' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							echo "</td>\n";
							break;
						case "phone":
							$tmp_phone = $data_row[$row['virtual_field_name']];
							$tmp_phone = format_phone($tmp_phone);
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='text' name='".$x."field_value' maxlength='20' value=\"".$tmp_phone."\">\n";
							echo "</td>\n";
							break;
						case "money":
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='text' name=".$x."field_value' maxlength='255' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							echo "</td>\n";
							break;
						case "add_user":
							//echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input type='hidden' name='".$x."field_value' maxlength='255' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							//echo "</td>\n";
							break;
						case "add_date":
							//echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input type='hidden' name='".$x."field_value' maxlength='255' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							//echo "</td>\n";
							break;
						case "mod_user":
							//echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input type='hidden' name='".$x."field_value' maxlength='255' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							//echo "</td>\n";
							break;
						case "mod_date":
							//echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input type='hidden' name='".$x."field_value' maxlength='255' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							//echo "</td>\n";
							break;
						case "uploadimage":
							if (strlen($data_row[$row['virtual_field_name']]) > 0) {
								echo "<td valign='top' align='left' class='vtable'>\n";
								echo "<script type=\"text/javascript\">\n";
								echo $row['virtual_field_name']." = \"\<input type=\'hidden\' name=\'".$x."field_name\' value=\'".$row['virtual_field_name']."\'>\\n\";\n";
								echo $row['virtual_field_name']." += \"\<input tabindex='".$row['virtual_field_order_tab']."' class=\'txt\' type=\'file\' name='".$x."field_value\' value=\'".$data_row[$row['virtual_field_name']]."\'>\\n\";\n";
								echo "</script>\n";

								echo "<div id='".$row['virtual_field_name']."id'>";
								echo "<table border='0' width='100%'>";
								echo "<tr>";
								echo "<td align='left'>";
									echo "".$data_row[$row['virtual_field_name']]."";
								echo "</td>";
								echo "<td align='right'>";
									echo "<input tabindex='".$row['virtual_field_order_tab']."' type='button' class='btn' title='delete' onclick=\"document.getElementById('".$row['virtual_field_name']."id').innerHTML=".$row['virtual_field_name']."\" value='x'>\n";
									//echo "<input type='button' class='btn' title='delete' onclick=\"addField('".$row['virtual_field_name']."id','".$x."field_name', 'hidden', '".$row['virtual_field_name']."',1);addField('".$row['virtual_field_name']."id','".$x."field_value', 'file', '',1);//'".$row['virtual_field_name']."'\" value='x'>\n";
								echo "</td>";
								echo "</tr>";
								echo "<tr>";
								echo "<td colspan='2' align='center'>";
								if (file_exists($imagetempdir.$data_row[$row['virtual_field_name']])) {
									echo "<img src='/images/cache/".$data_row[$row['virtual_field_name']]."'>";
								}
								else {
									echo "<img src='imagelo.php?max=125&img=".$data_row[$row['virtual_field_name']]."'>";
								}
								echo "</td>";
								echo "</tr>";

								echo "</table>";
								echo "<div>";
								echo "</td>\n";
							}
							else {
								echo "<td valign='top' align='left' class='vtable'>\n";
								echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
								echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='file' name='".$x."field_value' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
								echo "</td>\n";
							}
							break;
						case "upload_file":
							if (strlen($data_row[$row['virtual_field_name']]) > 0) {
								echo "<td valign='top' align='left' class='vtable'>\n";
								echo "<script type=\"text/javascript\">\n";
								echo $row['virtual_field_name']." = \"<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\";\n";
								echo $row['virtual_field_name']." += \"<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='file' name='".$x."field_value' value='".$data_row[$row['virtual_field_name']]."'>\";\n";
								echo "</script>\n";

								echo "<span id='".$row['virtual_field_name']."'>";
								echo "<table width='100%'>";
								echo "<tr>";
								echo "<td>";
								echo "<a href='download.php?f=".$data_row[$row['virtual_field_name']]."'>".$data_row[$row['virtual_field_name']]."</a>";
								echo "</td>";
								echo "<td align='right'>";
									echo "<input tabindex='".$row['virtual_field_order_tab']."' type='button' class='btn' title='delete' onclick=\"document.getElementById('".$row['virtual_field_name']."').innerHTML=".$row['virtual_field_name']."\" value='x'>\n";
								echo "</td>";
								echo "</tr>";
								echo "</table>";
								echo "<span>";
								echo "</td>\n";
							}
							else {
								echo "<td valign='top' align='left' class='vtable'>\n";
								echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
								echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='file' name='".$x."field_value' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
								echo "</td>\n";
							}

							break;
						default:
							echo "<td valign='top' align='left' class='vtable'>\n";
							echo "<input type='hidden' name='".$x."field_name' value='".$row['virtual_field_name']."'>\n";
							echo "<input tabindex='".$row['virtual_field_order_tab']."' class='formfld' style='width:90%'  type='text' style='' name='".$x."field_value' maxlength='255' value=\"".$data_row[$row['virtual_field_name']]."\">\n";
							echo "</td>\n";
						}
					if ($row['virtual_field_type'] != "hidden"){
						echo "</tr>\n";
					}

			//set the current value to the previous value
				$virtual_field_column_previous = $virtual_field_column;

			$x++;

		} //end foreach
		unset($sql, $result, $row_count);

		if ($column_table_cell_status == 'open') {
			$column_table_cell_status = 'closed';
		}
	} //end if results

	echo "	<tr>\n";
	echo "		<td colspan='999' align='right'>\n";
		if ($action == "add" && permission_exists('virtual_tables_data_add')) {
			echo "			<input type='submit' class='btn' name='submit' value='save'>\n";
		}
		if ($action == "update" && permission_exists('virtual_tables_data_edit')) {
			echo "			<input type='hidden' name='virtual_data_row_id' value='$virtual_data_row_id'>\n";
			echo "			<input type='submit' tabindex='9999999' class='btn' name='submit' value='Save'>\n";
		}
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";

	echo "	</td>\n";
	echo "	</tr>\n";
	echo "</form>\n";

	if ($action == "update" && permission_exists('virtual_tables_data_edit')) {
		//get the child virtual_table_uuid and use it to show the list of data
			$sql = "";
			$sql .= "select * from v_virtual_tables ";
			$sql .= "where domain_uuid = '$domain_uuid' ";
			$sql .= "and virtual_table_parent_id = '$virtual_table_uuid' ";
			$prep_statement = $db->prepare($sql);
			$prep_statement->execute();
			$result = $prep_statement->fetchAll();
			foreach ($result as &$row) {
				echo "<tr class='border'>\n";
				echo "	<td colspan='999' align=\"left\">\n";
				echo "		<br>";
				$_GET["id"] = $row["virtual_table_uuid"];
				$virtual_table_label = $row["virtual_table_label"];
				$_GET["virtual_data_parent_row_id"] = $virtual_data_row_id;

				//show button
				//echo "<input type='button' class='btn' name='' alt='".$virtual_table_label."' onclick=\"window.location='v_virtual_table_data_view.php?id=".$row["virtual_table_uuid"]."&virtual_data_parent_row_id=".$virtual_data_row_id."'\" value='".$virtual_table_label."'>\n";

				//show list
				require_once "v_virtual_table_data_view.php";
				echo "	</td>";
				echo "	</tr>";
			}
	}
	echo "</table>\n";
	echo "</div>\n";

require_once "includes/footer.php";
?>
