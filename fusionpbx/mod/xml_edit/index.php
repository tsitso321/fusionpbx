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
	exit;
}

require_once "mod/xml_edit/config.php";
echo "<html>\n";
echo "<head>\n";
echo "	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
echo "	<title></title>";


	echo "<script type=\"text/javascript\" language=\"javascript\">\n";
	echo "// Replaces all instances of the given substring.\n";
	echo "String.prototype.replaceall = function(\n";
	echo "strTarget, \n"; // The substring you want to replace
	echo "strSubString \n"; // The string you want to replace in
	echo ")\n";
	echo "{\n";
	echo "  var strText = this;\n";
	echo "  var intIndexOfMatch = strText.indexOf( strTarget );\n";
	echo "  \n";
	echo "  // Keep looping while an instance of the target string\n";
	echo "  // still exists in the string.\n";
	echo "  while (intIndexOfMatch != -1){\n";
	echo "  // Relace out the current instance.\n";
	echo "  strText = strText.replace( strTarget, strSubString )\n";
	echo "  \n";
	echo "  // Get the index of any next matching substring.\n";
	echo "  intIndexOfMatch = strText.indexOf( strTarget );\n";
	echo "}\n";
	//echo "// Return the updated string with ALL the target strings\n";
	//echo "// replaced out with the new substring.\n";
	echo "return( strText );\n";
	echo "}\n";

	echo "function urlencode(str) {\n";
	echo "  str=escape(str); \n"; //Escape does not encode '/' and '+' character
	echo "  str=str.replaceall(\"+\", \"%2B\");\n";
	echo "  str=str.replaceall(\"/\", \"%2F\");\n";
	echo "  return str;\n";
	echo "}";
	echo "</script>\n";

	echo "<script type=\"text/javascript\" language=\"javascript\">\n";
	echo "    function makeRequest(url, strpost) {\n";
	//echo "        alert(url); \n";
	echo "        var http_request = false;\n";
	echo "\n";
	echo "        if (window.XMLHttpRequest) { // Mozilla, Safari, ...\n";
	echo "            http_request = new XMLHttpRequest();\n";
	echo "            if (http_request.overrideMimeType) {\n";
	echo "                http_request.overrideMimeType('text/xml');\n";
	echo "                // See note below about this line\n";
	echo "            }\n";
	echo "        } else if (window.ActiveXObject) { // IE\n";
	echo "            try {\n";
	echo "                http_request = new ActiveXObject(\"Msxml2.XMLHTTP\");\n";
	echo "            } catch (e) {\n";
	echo "                try {\n";
	echo "                    http_request = new ActiveXObject(\"Microsoft.XMLHTTP\");\n";
	echo "                } catch (e) {}\n";
	echo "            }\n";
	echo "        }\n";
	echo "\n";
	echo "        if (!http_request) {\n";
	echo "            alert('Giving up :( Cannot create an XMLHTTP instance');\n";
	echo "            return false;\n";
	echo "        }\n";
	echo "        http_request.onreadystatechange = function() { returnContent(http_request); };\n";
	echo "        if (http_request.overrideMimeType) {\n";
	echo "              http_request.overrideMimeType('text/html');\n";
	echo "        }\n";
	echo "        http_request.open('POST', url, true);\n";
	echo "\n";
	echo "\n";
	echo "        if (strpost.length == 0) {\n";
	//echo "            alert('none');\n";
	echo "            //http_request.send(null);\n";
	echo "            http_request.send('name=value&foo=bar');\n";
	echo "        }\n";
	echo "        else {\n";
	//echo "            alert(strpost);\n";
	echo "            http_request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');\n";
	//echo "            http_request.send('name=value&foo=bar');\n";
	echo "            http_request.send(strpost);\n";
	echo "        }\n";
	echo "\n";
	echo "    }\n";
	echo "\n";
	echo "    function returnContent(http_request) {\n";
	echo "\n";
	echo "        if (http_request.readyState == 4) {\n";
	echo "            if (http_request.status == 200) {\n";

	echo "                  parent.editAreaLoader.setValue('edit1', http_request.responseText); \n";
	//echo "                alert(http_request.responseText);\n";
	echo "\n";
	//echo "                //var xmldoc = http_request.responseXML;\n";
	//echo "                //var root_node = xmldoc.getElementsByTagName('doc').item(0);\n";
	//echo "                //alert(xmldoc.getElementByID('fr1').value);\n";
	//echo "                //alert(root_node.firstChild.data);\n";
	//echo "\n";
	echo "            }\n";
	echo "            else {\n";
	echo "                alert('There was a problem with the request.');\n";
	echo "            }\n";
	echo "        }\n";
	echo "\n";
	echo "    }\n";
	echo "</script>";
	?>
	<script language="Javascript" type="text/javascript" src="<?php echo PROJECT_PATH ?>/includes/edit_area/edit_area_full.js"></script>
	<script language="Javascript" type="text/javascript">

	// initialisation
		editAreaLoader.init({
			id: "edit1"	// id of the textarea to transform
			,start_highlight: true
			,allow_toggle: false
			,language: "en"
			,syntax: "xml"
			,toolbar: "save, |, search, go_to_line,|, fullscreen, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight, |, help"
			,syntax_selection_allow: "css,html,js,php,xml,c,cpp,sql"
			,show_line_colors: true
			,load_callback: "my_load"
			,save_callback: "my_save"
		});

		// callback functions
		function my_save(id, content){
		        //alert(content);
            		makeRequest('filesave.php','file='+document.getElementById('file').value+'&content='+urlencode(content));
		        parent.document.title=''+unescape(document.getElementById('file').value)+' :: Saved';
		        //setTimeout("parent.document.title='<?=$applicationname?> - '+unescape(document.getElementById('file').value);", 5);
		        //setTimeout("alert('test')", 5);
		}

		function my_load(elem){
			elem.value="The content is loaded from the load_callback function into EditArea";
		}

		function my_setSelectionRange(id){
			editAreaLoader.setSelectionRange(id, 0, 0);
		}

		function test_setSelectionRange(id){
			editAreaLoader.setSelectionRange(id, 0, 0);
		}

		function test_getSelectionRange(id){
			var sel =editAreaLoader.getSelectionRange(id);
			alert("start: "+sel["start"]+"\nend: "+sel["end"]);
		}

		function test_setSelectedText(id){
			text= "[REPLACED SELECTION]";
			editAreaLoader.setSelectedText(id, text);
		}

		function test_getSelectedText(id){
			alert(editAreaLoader.getSelectedText(id));
		}

  	</script>

</head>
<?php
//<body marginwidth="0" marginheight="0" style="margin: 0" onresize="elem = document.getElementById('edit1'); elem.style.height='100%';  elem.style.width='100%'; document.getElementById('toolbar').style.width='200px';">
?>

<table border='0' style="height: 100%; width: 100%;">
<tr>
<td id='toolbar' valign='top' width='200' style="width: 200;" height='100%' nowrap>

<IFRAME SRC='filelist.php' style='border: solid 1px #CCCCCC; height: 100%; width: 100%;' WIDTH='100%' HEIGHT='100%' TITLE=''>
<!-- File List: Requires IFRAME support -->
</IFRAME>
<!--<IFRAME SRC='cliplist.php' style='border: solid 1px #CCCCCC; height: 55%; width: 100%;' WIDTH='100%' HEIGHT='100%' TITLE=''>-->
<!-- Clip List: Requires IFRAME support -->
<!--</IFRAME>-->
</td>

<td valign='top' width="100%" height='100%' style="height: 100%;">
<?php
	if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
		//IE doesn't work with the 100% width with the IFRAME
		echo "<textarea id='edit1' style='height: 100%; width: 800px;' name=''>\n";
		echo "</textarea>\n";
	}
	else {
		echo "<textarea id='edit1' style='height: 100%; width: 100%;' name=''>\n";
		echo "</textarea>\n";
	}
?>
<input type='hidden' id='file' name='file' value='' />
</td>
</tr>
</table>

<?php
/*
echo "    <p>Custom controls:<br />\n";
echo "    	<input type='button' onclick='alert(editAreaLoader.getValue(\"edit1\"));' value='get value' />\n";
echo "    	<input type='button' onclick='editAreaLoader.setValue(\"edit1\", \"new_value\");' value='set value' />\n";
echo "    	<input type='button' onclick='test_getSelectionRange(\"edit1\");' value='getSelectionRange' />\n";
echo "    	<input type='button' onclick='test_setSelectionRange(\"edit1\");' value='setSelectionRange' />\n";
echo "    	<input type='button' onclick='test_getSelectedText(\"edit1\");' value='getSelectedText' />\n";
echo "    	<input type='button' onclick='test_setSelectedText(\"edit1\");' value='setSelectedText' />\n";
echo "    	<input type='button' onclick='editAreaLoader.insertTags(\"edit1\", \"[OPEN]\", \"[CLOSE]\");' value='insertTags' />\n";
echo "    </p>";
*/
?>

</body>
</html>
