<?php

if (!isset($_SESSION['FilterCallSign'])) {
   $_SESSION['FilterCallSign'] = null;
}

if (!isset($_SESSION['FilterModule'])) {
   $_SESSION['FilterModule'] = null;
}

if (isset($_POST['do'])) {
   if ($_POST['do'] == 'SetFilter') {

      if (isset($_POST['txtSetCallsignFilter'])) {
         $_POST['txtSetCallsignFilter'] = trim($_POST['txtSetCallsignFilter']);
         if ($_POST['txtSetCallsignFilter'] == "") {
            $_SESSION['FilterCallSign'] = null;
         }
         else {
            $_SESSION['FilterCallSign'] = htmlspecialchars($_POST['txtSetCallsignFilter'], ENT_QUOTES, 'UTF-8');
            if (strpos($_SESSION['FilterCallSign'], "*") === false) {
               $_SESSION['FilterCallSign'] = "*".$_SESSION['FilterCallSign']."*";
            }
         }

      }

      if (isset($_POST['txtSetModuleFilter'])) {
         $_POST['txtSetModuleFilter'] = trim(strtoupper($_POST['txtSetModuleFilter']));
         if ($_POST['txtSetModuleFilter'] == "") {
            $_SESSION['FilterModule'] = null;
         }
         else {
            $_SESSION['FilterModule'] = htmlspecialchars($_POST['txtSetModuleFilter'], ENT_QUOTES, 'UTF-8');
         }
      }
   }
}

if (isset($_GET['do'])) {
   if ($_GET['do'] == "resetfilter") {
      $_SESSION['FilterModule'] = null;
      $_SESSION['FilterCallSign'] = null;
   }
}


?>
<table border="0">
   <tr>
      <td  valign="top">


<table class="listingtable"><?php

if ($PageOptions['UserPage']['ShowFilter']) {
   echo '
 <tr>
   <th colspan="8">
      <table width="100%" border="0">
         <tr>
            <td align="left">
               <form name="frmFilterCallSign" method="post" action="./index.php">
                  <input type="hidden" name="do" value="SetFilter" />
                  <input type="text" class="FilterField" value="'.htmlspecialchars((string)$_SESSION['FilterCallSign'], ENT_QUOTES, 'UTF-8').'" name="txtSetCallsignFilter" placeholder="Callsign" onfocus="SuspendPageRefresh();" onblur="setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');" />
                  <input type="submit" value="Apply" class="FilterSubmit" />
               </form>
            </td>';
   if (($_SESSION['FilterModule'] != null) || ($_SESSION['FilterCallSign'] != null)) {
      echo '
         <td><a href="./index.php?do=resetfilter" class="smalllink">Disable filters</a></td>';
   }
   echo '
            <td align="right" style="padding-right:3px;">
               <form name="frmFilterModule" method="post" action="./index.php">
                  <input type="hidden" name="do" value="SetFilter" />
                  <input type="text" class="FilterField" value="'.htmlspecialchars((string)$_SESSION['FilterModule'], ENT_QUOTES, 'UTF-8').'" name="txtSetModuleFilter" placeholder="Module(s)" onfocus="SuspendPageRefresh();" onblur="setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');" />
                  <input type="submit" value="Apply" class="FilterSubmit" />
               </form>
            </td>
      </table>
   </th>
</tr>';
}


?>
 <tr>
   <th>#</th>
   <th>Flag</th>
   <th>Callsign</th>
   <th>Suffix</th>
   <th>DPRS</th>
   <th>Via / Peer</th>
   <th>Last heard</th>
   <th align="center" valign="middle"><img src="./img/ear.png" alt="Listening on" /></th>
 </tr><?php

$Reflector->LoadFlags();
$odd = "";
for ($i=0;$i<$Reflector->StationCount();$i++) {
   $ShowThisStation = true;
   if ($PageOptions['UserPage']['ShowFilter']) {
      $CS = true;
      if ($_SESSION['FilterCallSign'] != null) {
         if (!fnmatch($_SESSION['FilterCallSign'], $Reflector->Stations[$i]->GetCallSign(), FNM_CASEFOLD)) {
            $CS = false;
         }
      }
      $MO = true;
      if ($_SESSION['FilterModule'] != null) {
         $MO = false;
         // Check if any character in the filter matches the module
         for ($m = 0; $m < strlen($_SESSION['FilterModule']); $m++) {
            if (strtoupper($Reflector->Stations[$i]->GetModule()) === substr($_SESSION['FilterModule'], $m, 1)) {
               $MO = true;
               break;
            }
         }
      }

      $ShowThisStation = ($CS && $MO);
   }


   if ($ShowThisStation) {
      if ($odd == "#FFFFFF") { $odd = "#F1FAFA"; } else { $odd = "#FFFFFF"; }
      echo '
  <tr height="30" bgcolor="'.$odd.'" onMouseOver="this.bgColor=\'#FFFFCA\';" onMouseOut="this.bgColor=\''.$odd.'\';">
   <td align="center" valign="middle" width="35">';
      if ($i==0 && $Reflector->Stations[$i]->GetLastHeardTime() > (time() - 60)) {
         echo '<img src="./img/tx.gif" style="margin-top:3px;" height="20"/>';
      }
      else {
         echo ($i+1);
      }


      echo '</td>
   <td align="center" width="60">';

      list ($Flag, $Name) = $Reflector->GetFlag($Reflector->Stations[$i]->GetCallSign());
      if (file_exists("./img/flags/".$Flag.".png")) {
         echo '<a href="#" class="tip"><img src="./img/flags/'.htmlspecialchars($Flag, ENT_QUOTES, 'UTF-8').'.png" height="15" alt="'.htmlspecialchars($Name, ENT_QUOTES, 'UTF-8').'" /><span>'.htmlspecialchars($Name, ENT_QUOTES, 'UTF-8').'</span></a>';
      }
      echo '</td>
   <td width="75"><a href="https://www.qrz.com/db/'.htmlspecialchars($Reflector->Stations[$i]->GetCallsignOnly(), ENT_QUOTES, 'UTF-8').'" class="pl" target="_blank">'.htmlspecialchars($Reflector->Stations[$i]->GetCallsignOnly(), ENT_QUOTES, 'UTF-8').'</a></td>
   <td width="60">'.htmlspecialchars($Reflector->Stations[$i]->GetSuffix(), ENT_QUOTES, 'UTF-8').'</td>
   <td width="50" align="center"><a href="http://www.aprs.fi/'.htmlspecialchars($Reflector->Stations[$i]->GetCallsignOnly(), ENT_QUOTES, 'UTF-8').'" class="pl" target="_blank"><img src="./img/sat.png" /></a></td>
   <td width="150">'.htmlspecialchars($Reflector->Stations[$i]->GetVia(), ENT_QUOTES, 'UTF-8');
      if ($Reflector->Stations[$i]->GetPeer() != $Reflector->GetReflectorName()) {
         echo ' / '.htmlspecialchars($Reflector->Stations[$i]->GetPeer(), ENT_QUOTES, 'UTF-8');
      }
      echo '</td>
   <td width="150">'.@date("d.m.Y H:i", $Reflector->Stations[$i]->GetLastHeardTime()).'</td>
   <td align="center" width="30">'.htmlspecialchars($Reflector->Stations[$i]->GetModule(), ENT_QUOTES, 'UTF-8').'</td>
 </tr>';
   }
   if ($i == $PageOptions['LastHeardPage']['LimitTo']) { $i = $Reflector->StationCount()+1; }
}

?>

</table>


</td>
<td style="padding-left:50px;" align="center" valign="top">




<table class="listingtable">
<?php

$Modules = $Reflector->GetModules();
sort($Modules, SORT_STRING);
echo '
 <tr>';
for ($i=0;$i<count($Modules);$i++) {

   if (isset($PageOptions['ModuleNames'][$Modules[$i]])) {
      echo '

      <th>'.htmlspecialchars($PageOptions['ModuleNames'][$Modules[$i]], ENT_QUOTES, 'UTF-8');
      if (trim($PageOptions['ModuleNames'][$Modules[$i]]) != "") {
         echo '<br />';
      }
      echo htmlspecialchars($Modules[$i], ENT_QUOTES, 'UTF-8').'</th>
';
   }
   else {
   echo '

      <th>'.htmlspecialchars($Modules[$i], ENT_QUOTES, 'UTF-8').'</th>';
   }
}

echo '
</tr>
<tr bgcolor="#FFFFFF" style="padding:0px;">';

$GlobalPositions = array();

for ($i=0;$i<count($Modules);$i++) {

   $Users = $Reflector->GetNodesInModulesByID($Modules[$i]);
   echo '
   <td valign="top" style="border:0px;padding:0px;">

      <table width="100" border="0" style="padding:0px;margin:0px;">';
   $odd = "";

   $UserCheckedArray = array();

   for ($j=0;$j<count($Users);$j++) {

      if ($odd == "#FFFFFF") { $odd = "#F1FAFA"; } else { $odd = "#FFFFFF"; }
      $Displayname = $Reflector->GetCallsignAndSuffixByID($Users[$j]);
      echo '
            <tr height="25" bgcolor="'.$odd.'" onMouseOver="this.bgColor=\'#FFFFCA\';" onMouseOut="this.bgColor=\''.$odd.'\';">
               <td valign="top" style="border-bottom:1px #C1DAD7 solid;"><a href="http://www.aprs.fi/'.htmlspecialchars($Displayname, ENT_QUOTES, 'UTF-8').'" class="pl" target="_blank">'.htmlspecialchars($Displayname, ENT_QUOTES, 'UTF-8').'</a> </td>
            </tr>';
      $UserCheckedArray[] = $Users[$j];
   }
   echo '
      </table>

   </td>';
}

echo '
</tr>';

?>
</table>


</td>
</tr>
</table>