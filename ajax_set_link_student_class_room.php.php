<?php
header('Content-Type: application/json; charset=UTF-8');
include_once('./includes/dbopen.php');
include_once('./includes/common.php');
include_once('./includes/member_check.php');

$MemberID = $_LINK_MEMBER_ID_;
$MemberLevelID = $_LINK_MEMBER_LEVEL_ID_;



$PangramLinkCenterDeviceGroupID = isset($_REQUEST["PangramLinkCenterDeviceGroupID"]) ? $_REQUEST["PangramLinkCenterDeviceGroupID"] : "";
$ArrCheckedData = isset($_REQUEST["ArrCheckedData"]) ? $_REQUEST["ArrCheckedData"] : "";

$ArrMemberDeviceID = explode("|", $ArrCheckedData);

for ($ii=0; $ii<=count($ArrMemberDeviceID)-2; $ii++){

	$ArrArrMemberID = explode(",", $ArrMemberDeviceID[$ii]);

	$PangramLinkCenterDeviceID = $ArrArrMemberID[0];
	$MemberID = $ArrArrMemberID[1];

	
	
	$Sql2 = "select A.MemberID as OldMemberID from PangramLinkCenterDeviceDetails A where MemberID=$MemberID and PangramLinkCenterDeviceGroupID=$PangramLinkCenterDeviceGroupID ";
	
	$Stmt2 = $DbConn->prepare($Sql2);
	$Stmt2->execute();
	$Stmt2->setFetchMode(PDO::FETCH_ASSOC);
	$Row2 = $Stmt2->fetch();
	$Stmt2 = null;
	$OldMemberID = $Row2["OldMemberID"];


	if ($MemberID != "" && $PangramLinkCenterDeviceID != ""){//등록

		$Sql3 = "select ifnull(Max(PangramLinkCenterDeviceDetailOrder),0) AS PangramLinkCenterDeviceDetailOrder from PangramLinkCenterDeviceDetails ";
		$Stmt = $DbConn->prepare($Sql3);
		$Stmt->execute();
		$Stmt->setFetchMode(PDO::FETCH_ASSOC);
		$Row = $Stmt->fetch();
		$Stmt = null;

		$PangramLinkCenterDeviceDetailOrder = $Row["PangramLinkCenterDeviceDetailOrder"]+10;
		if (!$OldMemberID){

			$Sql = "insert into PangramLinkCenterDeviceDetails (
							PangramLinkCenterDeviceID, 
							PangramLinkCenterDeviceGroupID,
							MemberID, 
							PangramLinkCenterDeviceDetailRegDateTime,
							PangramLinkCenterDeviceDetailModiDateTime,
							PangramLinkCenterDeviceDetailState,
							PangramLinkCenterDeviceDetailOrder
				) values (
							:PangramLinkCenterDeviceID,
							:PangramLinkCenterDeviceGroupID,
							:MemberID, 
							now(), 
							now(),
							1,
							:PangramLinkCenterDeviceDetailOrder
				)";
			
			$Stmt = $DbConn->prepare($Sql);
			$Stmt->bindParam(':PangramLinkCenterDeviceID', $PangramLinkCenterDeviceID);
			$Stmt->bindParam(':PangramLinkCenterDeviceGroupID', $PangramLinkCenterDeviceGroupID);
  			$Stmt->bindParam(':MemberID', $MemberID);

			$Stmt->bindParam(':PangramLinkCenterDeviceDetailOrder', $PangramLinkCenterDeviceDetailOrder);
			$Stmt->execute();
			$Stmt = null;
		}
	}else{//삭제
		if ($OldMemberID){

			$Sql = "delete from PangramLinkCenterDeviceDetails where  MemberID=$MemberID and PangramLinkCenterDeviceGroupID=$PangramLinkCenterDeviceGroupID";
			$Stmt = $DbConn->prepare($Sql);
			$Stmt->execute();
			$Stmt = null;

		}
	}

}



$ArrValue["ResultValue"] = 1;


$QueryResult = my_json_encode($ArrValue);
echo $QueryResult; 

function my_json_encode($arr){
	//convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
	array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
	return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
}

include_once('./includes/dbclose.php');
?>