<?php
header('Content-Type: application/json; charset=UTF-8');
include_once('./includes/dbopen.php');
include_once('./includes/common.php');
include_once('./includes/member_check.php');

$MemberID = $_LINK_MEMBER_ID_;
$MemberLevelID = $_LINK_MEMBER_LEVEL_ID_;



$PangramLinkCenterDeviceGroupID = isset($_REQUEST["PangramLinkCenterDeviceGroupID"]) ? $_REQUEST["PangramLinkCenterDeviceGroupID"] : "";
$ArrData = isset($_REQUEST["Arr_DeviceID_MemberID"]) ? $_REQUEST["Arr_DeviceID_MemberID"] : "";

$explodeArrData = explode("|", $ArrData);

for ($ii=0; $ii<=count($explodeArrData)-2; $ii++){

	$ArrMemberDeviceID = explode(",", $explodeArrData[$ii]);

	$PangramLinkCenterDeviceID = $ArrMemberDeviceID[0];
	$MemberID = $ArrMemberDeviceID[1];
	
	// echo "DeviceID : ".$PangramLinkCenterDeviceID." MemberID : ".$MemberID."\n";
	
	// Details 테이블에 값 체크
	$Sql2 = "SELECT
			A.MemberID as OldMemberID
			, A.PangramLinkCenterDeviceID AS OldDeviceID
		from PangramLinkCenterDeviceDetails A 
		where MemberID = $MemberID and PangramLinkCenterDeviceGroupID = $PangramLinkCenterDeviceGroupID ";
	
	$Stmt2 = $DbConn->prepare($Sql2);
	$Stmt2->execute();
	$Stmt2->setFetchMode(PDO::FETCH_ASSOC);
	$Row2 = $Stmt2->fetch();
	$Stmt2 = null;
	$OldMemberID = $Row2["OldMemberID"];
	$OldDeviceID = $Row2["OldDeviceID"];

	// 등록
	// DevcieID 가 0 이면 선택 되지 않았다고 판단, 
	if ($MemberID != "" && $PangramLinkCenterDeviceID != -1){

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

		}else if($PangramLinkCenterDeviceID != $OldDeviceID){ 
			/* update
			선택한 디바이스ID와 DB에 저장된 디바이스 ID가 다르면 */
			$Sql = " UPDATE 
					PangramLinkCenterDeviceDetails A
				SET
					A.PangramLinkCenterDeviceID =:PangramLinkCenterDeviceID
					,A.PangramLinkCenterDeviceDetailModiDateTime = NOW()
				WHERE MemberID =:MemberID";
		
			$Stmt = $DbConn->prepare($Sql);
			$Stmt->bindParam(':PangramLinkCenterDeviceID', $PangramLinkCenterDeviceID);
			$Stmt->bindParam(':MemberID', $MemberID);
			$Stmt->execute();
			$Stmt = null;

		}


	}else if($MemberID==$OldMemberID && $PangramLinkCenterDeviceID == -1){
		//삭제
		$Sql = "delete from PangramLinkCenterDeviceDetails where MemberID=$MemberID and PangramLinkCenterDeviceGroupID=$PangramLinkCenterDeviceGroupID";
		$Stmt = $DbConn->prepare($Sql);
		$Stmt->execute();
		$Stmt = null;
	}

} // for



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