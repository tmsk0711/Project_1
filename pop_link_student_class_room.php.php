<?php
include_once('./includes/dbopen.php');
include_once('./includes/common.php');
include_once('./includes/member_check.php');

$UseMain = 0;
$UseSub = 0;

$SubCode = "sub_common";
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $_SITE_TITLE_;?></title>
<link rel="icon" href="uploads/favicons/<?php echo $_SITE_FAVICON_;?>">
<link rel="shortcut icon" href="uploads/favicons/<?php echo $_SITE_FAVICON_;?>" />
<?php
include_once('./includes/common_header.php');

?>
</head>
<body class="border_top">


<?
$CenterID = $_LINK_MEMBER_CENTER_ID_;
$PangramLinkCenterDeviceGroupID = isset($_REQUEST["PangramLinkCenterDeviceGroupID"]) ? $_REQUEST["PangramLinkCenterDeviceGroupID"] : "";
$PangramLinkCenterDeviceCategoryID = isset($_REQUEST["PangramLinkCenterDeviceCategoryID"]) ? $_REQUEST["PangramLinkCenterDeviceCategoryID"] : "";


$SearchText = isset($_REQUEST["SearchText"]) ? $_REQUEST["SearchText"] : "";
$SearchPangramLinkCenterDeviceID = isset($_REQUEST["SearchPangramLinkCenterDeviceID"]) ? $_REQUEST["SearchPangramLinkCenterDeviceID"] : "";

$AddSqlWhere = "1=1";
$AddSqlWhere = $AddSqlWhere . " and A.CenterID=$CenterID ";


if ($SearchText != ""){
	$AddSqlWhere = $AddSqlWhere . " and (A.MemberName like '%".$SearchText."%' or A.MemberNickName like '%".$SearchText."%' or A.MemberLoginID like '%".$SearchText."%') ";
}


$AddSqlWhere = $AddSqlWhere . " and A.MemberLevelID=19 ";
$AddSqlWhere = $AddSqlWhere . " and A.MemberState=1 ";
$AddSqlWhere = $AddSqlWhere . " and A.SpeTypeMember=0 ";
$AddSqlWhere = $AddSqlWhere . " and (A.MemberStudentType=3 or A.MemberStudentType=4) ";//PANGRAM, AL+PA

// if ($SearchPangramLinkCenterDeviceID!=""){
// 	$ListParam = $ListParam . "&SearchPangramLinkCenterDeviceID=" . $SearchPangramLinkCenterDeviceID;
// 	$AddSqlWhere = $AddSqlWhere . " and B.SearchPangramLinkCenterDeviceID=".$SearchPangramLinkCenterDeviceID." ";
// }
$Sql = "select 
			count(*) TotalCount  
		from Members A 
			left outer join PangramLinkCenterDeviceDetails B on A.MemberID=B.MemberID and B.PangramLinkCenterDeviceGroupID=".$PangramLinkCenterDeviceGroupID."
		where ".$AddSqlWhere." ";

$Stmt = $DbConn->prepare($Sql);
$Stmt->execute();
$Stmt->setFetchMode(PDO::FETCH_ASSOC);
$Row = $Stmt->fetch();
$Stmt = null;
$TotalCount  = $Row["TotalCount"];

//????????????????????? ???????????? ??????, ????????? 0
$ViewTable = "
	select 
			A.*,
			ifnull(B.PangramLinkCenterDeviceGroupID, 0) as OldPangramLinkCenterDeviceGroupID,
			ifnull(B.PangramLinkCenterDeviceID, 0) as OldPangramLinkCenterDeviceID, 
			B.PangramLinkCenterDeviceDetailRegDateTime
		from Members A 
			left outer join PangramLinkCenterDeviceDetails B on A.MemberID=B.MemberID and B.PangramLinkCenterDeviceGroupID=".$PangramLinkCenterDeviceGroupID."
		where ".$AddSqlWhere." 
";

$Sql = "
		select 
			V.*
		from ($ViewTable) V 
		order by V.OldPangramLinkCenterDeviceGroupID desc, V.PangramLinkCenterDeviceDetailRegDateTime desc";// limit $StartRowNum, $PageListNum";

$Stmt = $DbConn->prepare($Sql);
$Stmt->execute();
$Stmt->setFetchMode(PDO::FETCH_ASSOC);

?>
<h2 class="sub_title popup">????????????</h2>


<form name="SearchForm" method="get" class="search_full" autocomplete="off">
    <input type="hidden" id="PangramLinkCenterDeviceGroupID" name="PangramLinkCenterDeviceGroupID" value="<?=$PangramLinkCenterDeviceGroupID?>">
    <section class="search_wrap list popup">
        <!--
		<select class="search_select">
            <option>????????? ??????</option>
            <option>????????????</option>
            <option>????????????</option>
            <option>????????????</option>
        </select>
		-->
        <input type="text" name="SearchText" value="<?=$SearchText?>" class="search_input">
        <a href="javascript:SearchSubmit();" class="search_btn" style="border-left:0;"></a>
    </section> 
</form>



<div class="sub_wrap">
    <div class="overflow_hidden">
		<form name="CenterDeviceForm" method="get" class="search_full" autocomplete="off">
        <table class="list_table small">
            <col width="10%">
            <col width="10%">
            <col width="17%">
            <col width="">
            <col width="18%">
            <tr>
                <th>No</th>
                <!-- <th>??????</th> -->
                <th>?????? ???</th>
                <th>?????????</th>
				<th>?????? ??? </th>
                <th>?????????</th>
            </tr>
			<?
			$ListCount = 1;
			
			while($Row = $Stmt->fetch()) {

				$ListNumber = $TotalCount - ($ListCount-1);

				$MemberID = $Row["MemberID"];
				$MemberName = $Row["MemberName"];
				$MemberLoginID = $Row["MemberLoginID"];
				$MemberRegDateTime = $Row["MemberRegDateTime"];
				$OldPangramLinkCenterDeviceGroupID = $Row["OldPangramLinkCenterDeviceGroupID"];
				$OldPangramLinkCenterDeviceID = $Row["OldPangramLinkCenterDeviceID"];

				$MemberRegDateTime = str_replace("-",".", substr($MemberRegDateTime, 0, 10));
			?>
            <tr>
                <td><?=$ListNumber?></td>
                <!-- <td>
                    <label class="check_label link">
                        <input type="checkbox" class="check_input" name="MemberID[]" id="MemberID_<?=$MemberID?>" value='<?=$MemberID?>' <?if ($OldPangramLinkCenterDeviceGroupID!=0){?>checked<?}?>>
                        <span class="check_bullet"></span>
                    </label>
                </td> -->
                <td><?=$MemberName?></td>
                <td><?=$MemberLoginID?></td>
				<td>
					<?// sun 2022 11-21?>
					<input type="hidden" id="$MemberID" name="MemberID[]" value="<?=$MemberID?>" index="<?=$ListNumber?>">

					<select id="PangramLinkCenterDeviceID_<?=$MemberID?>" name="PangramLinkCenterDeviceName[]"  class="search_select" style="width:;" onchange="OverLapCheck(<?=$MemberID?>);">
						<option value="-1">?????? ?????? ??????</option>
						<?
						$Sql2 = "SELECT
						*
						FROM PangramLinkCenterDevicies A 
						WHERE A.PangramLinkCenterDeviceCategoryID=:PangramLinkCenterDeviceCategoryID AND A.PangramLinkCenterDeviceState <>0 order by A.PangramLinkCenterDeviceOrder ASC";
						$Stmt2 = $DbConn->prepare($Sql2);
						$Stmt2->bindParam(':PangramLinkCenterDeviceCategoryID', $PangramLinkCenterDeviceCategoryID);
						$Stmt2->execute();
						$Stmt2->setFetchMode(PDO::FETCH_ASSOC);
						while($Row2 = $Stmt2->fetch()) {
							$PangramLinkCenterDeviceID = $Row2["PangramLinkCenterDeviceID"];
							$PangramLinkCenterDeviceDescription = $Row2["PangramLinkCenterDeviceDescription"];
						?>
						<option value="<?=$PangramLinkCenterDeviceID?>" <?if ($PangramLinkCenterDeviceID==$OldPangramLinkCenterDeviceID){?>selected<?}?>><?=$PangramLinkCenterDeviceDescription?></option>
						<?
						}
						$Stmt2 = null;?>

					</select>
				</td>
				<td><?=$MemberRegDateTime?></td>
            </tr>

			<?
				$ListCount++;
			}
			$Stmt = null;
			?>

        </table> 
		</form>
    </div>

    <div class="fixed_btn_wrap"><a href="javascript:FormSubmit();" class="btn_black_gold">????????????</a></div>    
	
</div>

<script language="javascript">

// sun 2022.11.21
let PangramLinkCenterDeviceGroupID = "<?=$PangramLinkCenterDeviceGroupID?>";
let Arr_DeviceID_MemberID = "";

// cheked ??????
// let MemberChecked = $("input:checkbox[name='MemberID[]']:checked");
let MemberChecked = $("input:hidden[name='MemberID[]']");
let length = MemberChecked.length;

function FormSubmit(){
	for(i = 0; i <length; i++){
		MemberID = MemberChecked[i].value;
		let PangramLinkCenterDeviceID = $('#PangramLinkCenterDeviceID_'+MemberID+' option:selected').val();
		Arr_DeviceID_MemberID = Arr_DeviceID_MemberID + PangramLinkCenterDeviceID +','+MemberID+'|'
	}
	url = "./ajax_set_link_student_class_room.php";
	$.ajax(url, {
		data: {
			PangramLinkCenterDeviceGroupID : PangramLinkCenterDeviceGroupID,
			Arr_DeviceID_MemberID: Arr_DeviceID_MemberID
		},
		success: function (data) {
			$.confirm({
				title: "??????",
				content: "?????? ????????????.",
				buttons: {
					??????: function () {
						location.reload();
					}
				}
			});	
		},
		error: function (jqXHR, textStatus, errorThrown){
                    	console.log(jqXHR);  
                    	console.log(textStatus); 
                    	console.log(errorThrown);
		}
	});	
}//FormSubmit()

/* ??????????????? ????????? ????????? CenterID ?????? ??????*/
function OverLapCheck(LncMemberID){
	let LncPangramLinkCenterDeviceID = $('#PangramLinkCenterDeviceID_'+LncMemberID+' option:selected').val(); 
	
	if(LncPangramLinkCenterDeviceID != -1){
		for(i=0; i<length; i++){
			MemberID = MemberChecked[i].value;
			let PangramLinkCenterDeviceID = $('#PangramLinkCenterDeviceID_'+MemberID+' option:selected').val();

			if(MemberID != LncMemberID && PangramLinkCenterDeviceID == LncPangramLinkCenterDeviceID){
				$.confirm({
					title: "??????",
					content: "?????? ????????? ?????? ??? ??? ????????????.",
				});

				$('#PangramLinkCenterDeviceID_'+LncMemberID).val(-1);
				$('#PangramLinkCenterDeviceID_'+LncMemberID).focus();
			}
		}
	}
}// OverLapCheck()


function SearchSubmit(){
	document.SearchForm.action = "pop_link_student_class_room.php";
	document.SearchForm.submit();
}
</script>

<?php
include_once('./includes/common_footer.php');

?>

</body>
</html>
<?php
include_once('./includes/dbclose.php');
?>