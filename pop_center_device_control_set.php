<?php
include_once('./includes/dbopen.php');
include_once('./includes/common.php');
include_once('./includes/member_check.php');

$UseMain = 0;
$UseSub = 1;

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





if ($CenterID!="") {

	$Sql = "    
        SELECT 
        A.CenterName,
        B.*
        FROM Centers A
        inner JOIN CenterDeviceCategories B on B.CenterID = A.CenterID
        WHERE B.CenterID=:CenterID   
        ";
	$Stmt = $DbConn->prepare($Sql);
	$Stmt->bindParam(':CenterID', $CenterID);
	$Stmt->execute();
	$Stmt->setFetchMode(PDO::FETCH_ASSOC);
	$Row = $Stmt->fetch();
	$Stmt = null;

	$CenterName = $Row["CenterName"];
    
	$CenterDeviceCategoryID = $Row["CenterDeviceCategoryID"];
	$CenterDeviceCategoryName = $Row["CenterDeviceCategoryName"];
	$CenterDeviceCategoryState = $Row["CenterDeviceCategoryState"];
    $CenterDeviceCategoryOrder = $Row["CenterDeviceCategoryOrder"];
}else{

    // $CenterDeviceCategoryID = $Row["CenterDeviceCategoryID"];
	// $CenterDeviceCategoryName = $Row["CenterDeviceCategoryName"];
	// $CenterDeviceCategoryState = $Row["CenterDeviceCategoryState"];
    // $CenterDeviceCategoryOrder = $Row["CenterDeviceCategoryOrder"];
}


?>
<h2 class="sub_title popup" style="text-align:left;">기기 접속 관제
    <?if ($CenterName!=""){
        echo "( ".$CenterName." )";
    }?>
</h2>

<?php
$AddSqlWhere = "1=1";
$ListParam = "1=1";
$PaginationParam = "1=1";


$CurrentPage = isset($_REQUEST["CurrentPage"]) ? $_REQUEST["CurrentPage"] : "";
$PageListNum = isset($_REQUEST["PageListNum"]) ? $_REQUEST["PageListNum"] : "";
$SearchText = isset($_REQUEST["SearchText"]) ? $_REQUEST["SearchText"] : "";
$SearchState = isset($_REQUEST["SearchState"]) ? $_REQUEST["SearchState"] : "";
$SearchCenterDeviceCategoryID = isset($_REQUEST["SearchCenterDeviceCategoryID"]) ? $_REQUEST["SearchCenterDeviceCategoryID"] : "";

	

$CenterDeviceCode = isset($_REQUEST["CenterDeviceCode"]) ? $_REQUEST["CenterDeviceCode"] : "";
$MemberCenterDeviceCode = isset($_REQUEST["MemberCenterDeviceCode"]) ? $_REQUEST["MemberCenterDeviceCode"] : "";

// sun
// $MemberID = isset($_REQUEST["MemberID"]) ? $_REQUEST["MemberID"] : ""; 
// $MemberName = isset($_REQUEST["MemberName"]) ? $_REQUEST["MemberName"] : "";
// $CenterDeviceID = isset($_REQUEST["CenterDeviceID"]) ? $_REQUEST["CenterDeviceID"] : "";




if (!$CurrentPage){
	$CurrentPage = 1;	
}
if (!$PageListNum){
	$PageListNum = 10;
}

if ($SearchState==""){
	$SearchState = "100";
}


if ($PageListNum!=""){
	$ListParam = $ListParam . "&PageListNum=" . $PageListNum;
}

if ($SearchText!=""){
	$ListParam = $ListParam . "&SearchText=" . $SearchText;
	$AddSqlWhere = $AddSqlWhere . " and A.CenterDeviceName like '%".$SearchText."%' ";
}

if ($SearchState!="100"){
	$ListParam = $ListParam . "&SearchState=" . $SearchState;
	$AddSqlWhere = $AddSqlWhere . " and A.CenterDeviceState=".$SearchState." ";
}
$AddSqlWhere = $AddSqlWhere . " and A.CenterDeviceState<>0 ";
$AddSqlWhere = $AddSqlWhere . " and B.CenterID=$CenterID ";

if ($SearchCenterDeviceCategoryID!=""){
	$ListParam = $ListParam . "&SearchCenterDeviceCategoryID=" . $SearchCenterDeviceCategoryID;
	$AddSqlWhere = $AddSqlWhere . " and B.CenterDeviceCategoryID=".$SearchCenterDeviceCategoryID." ";
}

$PaginationParam = $ListParam;
//$PaginationParam = $PaginationParam . "&ListParam=".$ListParam;
if ($CurrentPage!=""){
	$ListParam = $ListParam . "&CurrentPage=" . $CurrentPage;
}

$ListParam = str_replace("&", "^^", $ListParam);


$Sql = "select 
			count(*) TotalRowCount 
		from CenterDevicies A 
			inner join CenterDeviceCategories B on A.CenterDeviceCategoryID=B.CenterDeviceCategoryID 
		where ".$AddSqlWhere." ";
$Stmt = $DbConn->prepare($Sql);
$Stmt->execute();
$Stmt->setFetchMode(PDO::FETCH_ASSOC);
$Row = $Stmt->fetch();
$Stmt = null;

$TotalRowCount = $Row["TotalRowCount"];

$TotalPageCount = ceil($TotalRowCount / $PageListNum);
$StartRowNum = $PageListNum * ($CurrentPage - 1 );

// echo $AddSqlWhere."==".$StartRowNum."===".$PageListNum."==태양 작업중";

// SQL 수정 OUTER JOIN 추가 
$Sql = "
select 
	A.*
	,ifnull(B.CenterDeviceCategoryID, 0) as CenterDeviceCategoryID
	,ifnull(B.CenterDeviceCategoryName, '미등록') as CenterDeviceCategoryName
	,C.MemberID
	,C.MemberName
	,DATE_FORMAT(C.C.CenterDeviceLoginModiDateTime, '%H:%i:%s')AS CenterDeviceLoginModiDateTime
	,DATE_FORMAT(C.CenterDeviceLoginDateTime, '%T')AS CenterDeviceLoginDateTime
	,TIMESTAMPdiff(second, CenterDeviceLoginDateTime ,CenterDeviceLoginModiDateTime)AS TimeDiffSec
from CenterDevicies A 
	INNER JOIN CenterDeviceCategories B on A.CenterDeviceCategoryID=B.CenterDeviceCategoryID
	LEFT OUTER JOIN(SELECT 
		A.*,
		B.MemberName
		FROM CenterDeviceLogs A
		INNER JOIN Members B ON B.MemberID = A.MemberID
		INNER JOIN CenterDevicies C ON C.CenterDeviceID = A.CenterDeviceID
		WHERE A.CenterDeviceLogState=0) C 
	ON C.CenterDeviceID = A.CenterDeviceID
where ".$AddSqlWhere." 
order by A.CenterDeviceOrder asc, B.CenterDeviceCategoryOrder asc limit $StartRowNum, $PageListNum";

$Stmt = $DbConn->prepare($Sql);
$Stmt->execute();
$Stmt->setFetchMode(PDO::FETCH_ASSOC);


?>
        <div class="search_area">
            <!-- <select id="SearchState" name="SearchState" class="search_select" style="width:;" onchange="SearchFormSubmit()">
                <option value="">전체</option>
                <option value="1" <?if ($SearchState=="1"){?>selected<?}?>>승인</option>
                <option value="2" <?if ($SearchState=="2"){?>selected<?}?>>미승인</option>
            </select>

            <input type="text" name="SearchText" value="<?=$SearchText?>" class="search_input">
            <a href="javascript:SearchFormSubmit();" class="search_btn" style="border-left:0;"></a> -->
        </div>
	


	
	
<div class="right" style="float:left; width:50%;">
	<h4>도면</h4>
	<div class="bg_logo">
		<input type="hidden" name="CourseImage" value="<?=$CourseImage?>" />
		<img src="<?=$StrCourseImage?>" id="CourseImage" onerror="this.src=''" class="img">
		<div class="btn_area"><a href="javascript:PopupUploadImage('CourseImage','RegForm.CourseImage','../uploads/course_images');" class="rtn white">도면 업로드</a></div>
	</div>
	<ul>
		<!--
		<li>이미지 권장사이즈 : 256 × 128 (단위:픽셀)</li>
		<li>이미지는 되도록 가로형태로 올려주시기 바랍니다.</li>
		<li>파일형식 : .jpg / .png</li>
		<li>표지를 올리지 않으면 연결교재 이미지를 사용합니다.</li>
		-->
	</ul>
	<div>
		<a href="javascript:PopupCenterClassRoom('<?=$CenterDeviceCode?>');" class="class_btn student">그룹 관리</a>
	</div>
</div>



<div class="sub_common_wrap">
<form name="SearchForm" method="get" class="search_full">
	<table class="list_table">
		<col width="10%">
		<col width="30%">
		<col width="30%">
		<col width="30%">
		<tr>
			<td></td>
			<td>기기 설명</td>
			<td>
				접속 시간
				<br>
				접속 학생
			</td>
			<td>
				<select id="SearchCenterDeviceCategoryID" name="SearchCenterDeviceCategoryID" class="search_select" style="width:;" onchange="SearchFormSubmit()">
					<option value="">전체 등록 반</option>
					<?
					$Sql2 = "select 
									A.*
							from CenterDeviceCategories A 
								inner join Centers B on A.CenterID=B.CenterID 
							where A.CenterDeviceCategoryState<>0 and A.CenterID=$CenterID order by A.CenterDeviceCategoryOrder ASC";// limit $StartRowNum, $PageListNum";
					$Stmt2 = $DbConn->prepare($Sql2);
					$Stmt2->execute();
					$Stmt2->setFetchMode(PDO::FETCH_ASSOC);
					while($Row2 = $Stmt2->fetch()) {
						$CenterDeviceCategoryID = $Row2["CenterDeviceCategoryID"];
						$CenterDeviceCategoryName = $Row2["CenterDeviceCategoryName"];
					?>
					<option value="<?=$CenterDeviceCategoryID?>" <?if ($CenterDeviceCategoryID==$SearchCenterDeviceCategoryID){?>selected<?}?>><?=$CenterDeviceCategoryName?></option>
					<?
					}
					$Stmt2 = null;
					?>
				</select>
				<!-- <input type="text" name="SearchText" value="<?=$SearchText?>" class="search_input">
				<a href="javascript:SearchSubmit();" class="search_btn" style="border-left:0;"></a> -->
			</td>
		</tr>
		<?
		$ListCount = 1;
		
		while($Row = $Stmt->fetch()) {

			$ListNumber = $TotalRowCount - $PageListNum * ($CurrentPage - 1) - $ListCount + 1;
			

			$CenterDeviceID = $Row["CenterDeviceID"];
			$CenterDeviceCode = $Row["CenterDeviceCode"];
			$CenterDeviceName = $Row["CenterDeviceName"];
			$CenterDeviceDescription = $Row["CenterDeviceDescription"];
			$CenterDeviceRegDateTime = $Row["CenterDeviceRegDateTime"];
			$CenterDeviceState = $Row["CenterDeviceState"];

			$CenterDeviceCategoryID = $Row["CenterDeviceCategoryID"];
			$CenterDeviceCategoryName = $Row["CenterDeviceCategoryName"];
			
			
			$MemberID = $Row["MemberID"];
			$CenterDeviceLoginModiDateTime = $Row["CenterDeviceLoginModiDateTime"];
			$CenterDeviceLoginDateTime = isset($Row["CenterDeviceLoginDateTime"]) ? $Row["CenterDeviceLoginDateTime"] : "-";
			
			// $MemberID = isset($Row["MemberID"]) ? $Row["MemberID"] : "";
			// $CenterDeviceLoginModiDateTime = isset($Row["CenterDeviceLoginModiDateTime"]) ? $Row["CenterDeviceLoginModiDateTime"] : "-"
			;
			$TimeDiffSec = isset($Row["TimeDiffSec"]) ? $Row["TimeDiffSec"] : "-";
			$MemberName = isset($Row["MemberName"]) ? $Row["MemberName"] : "-";
			echo "MemberID: ".$MemberID."\nMemberName : ".$MemberName."\nCenterDeviceID : ".$CenterDeviceID."<br>";
			

			if ($CenterDeviceState==1){
				$StrCenterDeviceState = "Active";
			}else{
				$StrCenterDeviceState = "Inactive";
			}

			
		
		?>  
		<tr>
			<td><?=$ListCount?></td>
			<td><?=$CenterDeviceDescription?></td>

			<td>
				<!-- sun 로그인로그 데이터 출력  -->
				<div id="LoginTime_<?=$ListCount-1?>" name="LoginTime" value="<?=$MemberID?>,<?=$CenterDeviceID?>,<?=$CenterDeviceLoginDateTime?>,<?=$CenterDeviceLoginModiDateTime?>" style="width:;" ><?=$CenterDeviceLoginDateTime?></div>
				<div id="LoginMember_<?=$ListCount-1?>" name="LoginMember" value="<?=$MemberName?>" style="width:;" ><?=$MemberName?></div>
				

			</td>
			<td>
				<?if ($CenterDeviceCode!==""){?>
					<a href="javascript:CenterDeviceLogin('<?=$CenterDeviceCode?>');" class="class_btn student">로그인</a>
					<a href="javascript:CenterDeviceLogout('<?=$CenterDeviceCode?>',<?=$MemberID?>,<?=$CenterDeviceID?>);" class="class_btn teacher">로그아웃</a>
				<?}else{?>
					-
				<?}?>
			</td>
			
		</tr>
		<?
			$ListCount++;
			
		}
		$Stmt = null;
		?>


	</table>  

	<?
	include_once('./inc_pagination.php');
	?>  
</form>      
</div>



<script>
function SearchFormSubmit(){
	document.SearchForm.action = "pop_center_device_control_set.php";
	document.SearchForm.submit();
}
</script>




<script type="text/javascript">
	var obj_ws;

	let ArrMemberResult = new Array();
	let ArrTimerResult = new Array();
	let arrLoginTime = document.getElementsByName('LoginTime');
			
	// DB에 저장된 타이머 값 계산 후 출력
	for (var i = 0; i < arrLoginTime.length; i++){ 
		LoginDate = arrLoginTime[i].attributes.value.value 

		if(LoginDate.charAt(0) !== ","){  // 로그인을 하지않은 리스트는 값이 없어 , 로 시작됨 
			
			LoginDateArr = LoginDate.split(",");
			
			MemberID = LoginDateArr[0];
			CenterDeviceID = LoginDateArr[1];
			CenterDeviceLoginDateTime = LoginDateArr[2];
			CenterDeviceLoginModiDateTime = LoginDateArr[3];

			let CenterDeviceLoginDateTimeSec = FormatTimeSec(CenterDeviceLoginDateTime);
			let CenterDeviceLoginModiDateTimeSec = FormatTimeSec(CenterDeviceLoginModiDateTime);
			connectSec = FormatSecMinus(CenterDeviceLoginDateTimeSec,CenterDeviceLoginModiDateTimeSec);

			result = FormatTimeHHMMSS(connectSec);
			document.getElementById('LoginTime_' + i).innerText = result;
			
			ArrMemberResult = ArrMemberResult+MemberID+'|'+CenterDeviceID+',';
			ArrTimerResult = ArrTimerResult+connectSec+'|'+'LoginTime_'+i+',';

		}
		
	}
	

	setInterval(function(){
		ArrTimerResult = ListViewTimer(ArrTimerResult);
	}, 1000);
	
	// setInterval(function(){
	// 	TimeUpdateSec(ArrMemberResult);
	// }, 5000);
	

	// 배열 파싱 -> DB 시간 업데이트
	function TimeUpdateSec(ArrMemberResult){
		MemberDateArr = ArrMemberResult.split(",");
		// console.log(TimerDate);

		for(var i =0; i<MemberDateArr.length-1; i++){
			MemberDate = MemberDateArr[i].split("|");
			MemberID = parseInt(MemberDate[0]);
			CenterDeviceID = parseInt(MemberDate[1]);
			
			console.log(MemberID,CenterDeviceID);
			SetTimeConnLog(MemberID,CenterDeviceID,99);
		}
	}

	//  +1 씩 해서 리스트에 출력
	function ListViewTimer(arrResult){
		let ArrTimerResult = new Array();
		TimerDateArr = arrResult.split(",");

		for(var i = 0; i < TimerDateArr.length-1; i++){

			TimerDate = TimerDateArr[i].split("|");
			connectSec = parseInt(TimerDate[0]);
			connectID = TimerDate[1];
			connectSec = connectSec+1;

			result = FormatTimeHHMMSS(connectSec);
			document.getElementById(connectID).innerText = result;
			

			ArrTimerResult = ArrTimerResult+connectSec+'|'+connectID+','; 
		}
		
		
		return ArrTimerResult;
	}


	// 시계열 데이터 가져와서 초로 변경 
	function FormatTimeSec(time){
		var time = time.split(":");
		var hour = time[0];
		var min = time[1];
		var sec = time[2];
		
		var hour = parseInt(hour*3600);
		var min = parseInt(min*60);
		var sec = parseInt(sec);
		
		var TimeSec = hour + min + sec;
		
		return TimeSec;
	}

	// 수정시간 - 로그인시간 계산
	function FormatSecMinus(LoginTime,ModiTime){
		var connectSec = ModiTime - LoginTime;

		return connectSec
	}


	// 계산한 시계열 데이터 다시 HHMMSS 형식으로 변경
	function FormatTimeHHMMSS(connectSec){
		hour = parseInt(connectSec/3600);
		min = parseInt((connectSec%3600)/60);
		sec = connectSec%60;
		
		if(hour.toString().length==1) hour = "0"+hour;
		if(min.toString().length==1) min = "0"+min;
		if(sec.toString().length==1) sec = "0"+sec;
		
		var result = hour + ":" + min + ":" + sec;
		
		return result;
	}

	// function ShowTimeLogin(LoginDate){
		
		
			
	// 	var connectDate = NowDate.getTime() - LoginDate.getTime();
	// 	var connectSec = Math.ceil(connectDate / 1000);
	// 	// var result = Math.ceil((currentDate - connectedDate) / 1000);
		
	// 	hour = parseInt(connectSec/3600);
	// 	min = parseInt((connectSec%3600)/60);
	// 	sec = connectSec%60;
		
	// 	if(hour.toString().length==1) hour = "0"+hour;
	// 	if(min.toString().length==1) min = "0"+min;
	// 	if(sec.toString().length==1) sec = "0"+sec;
		
	// 	var result = hour + ":" + min + ":" + sec;

	// 	document.getElementById('LoginTime_' + ArrOrderMsg[0]).innerText = result;
	
	
	// 	setTimeout(ShowTimeLogin,1000);
	// }
	
	
	
	function init_ws() {
	
	// Connect to Web Socket
	obj_ws = new WebSocket('wss://pangramlink.com:9996');
	
	// Set event handlers.
	obj_ws.onopen = function() {
		console.log("onopen");
	};
	
	obj_ws.onmessage = function(e) {
		// JSON.parse(e.data).msg contains received string.
		console.log("onmessage control_set: " + JSON.parse(e.data).msg);
		
		msg_parsed = JSON.parse(e.data).msg;
		ArrOrderMsg = msg_parsed.split("|");
		
		if (ArrOrderMsg[1]=="0"){//로그인 상태

			// $("#LoginMember_"+ArrOrderMsg[0]).html(ArrOrderMsg[3]+"("+ArrOrderMsg[2]+")");
			// document.getElementById('LoginMember_' + ArrOrderMsg[0]).innerText = ArrOrderMsg[2];
			// $('#LoginMember_'+ArrOrderMsg[0]).val(ArrOrderMsg[2]);
			
			// SetTimeConnLog(<?=$MemberID?>,<?=$CenterDeviceID?>,99);
			
		}else if(ArrOrderMsg[1]=="1"){//로그아웃 상태
			
			$("#LoginMember_"+ArrOrderMsg[0]).html("-");
			$("#LoginTime_"+ArrOrderMsg[0]).html("-");
			exit();
			
		}
	};
	
	
	obj_ws.onclose = function() {
		console.log("onclose");
	};
	
	obj_ws.onerror = function(e) {
		console.log("onerror: " + e);
	};
	
}// init_ws


function SendWsMessage(msg) {
	obj_ws.send(JSON.stringify({type: "status", action: "online", rid: '', receiver: [], msg: msg, uid: "_my_id", uData: []}));
}

// not use
function CloseWs() {
	obj_ws.close();
}




function CenterDeviceLogout(CenterDeviceCode,MemberID,CenterDeviceID){
	SendWsMessage(CenterDeviceCode+"|"+100+"||");

	SetConnLog(MemberID,CenterDeviceID, 100);

	$("#LoginMember_"+CenterDeviceCode).html("-");
	$("#LoginTime_"+CenterDeviceCode).html("-");
	DelayRoad();
	
}

function CenterDeviceLogin(CenterDeviceCode){
	
	var OpenUrl = "pop_center_device_login_member_list.php?CenterDeviceCode="+CenterDeviceCode;
	
	$.colorbox({	
		href:OpenUrl
		,width:"100%" 
		,height:"100%"
		,maxWidth: "1000"
		,maxHeight: "800"
		,title:""
		,iframe:true 
		,scrolling:true
		,overlayClose:true
		//,onClosed:function(){location.reload(true);}
		//,onComplete:function(){alert(1);}
	}); 
	
	
}


	// sun
	$().ready(function() {
		init_ws();
		
		
	});

function PopupUploadImage(ImgID,FormName,UpPath){
	alert(ImgID+FormName+UpPath);// CourseImageRegForm.CourseImage../uploads/course_images
	openurl = "./pop_image_upload_form.php?ImgID="+ImgID+"&FormName="+FormName+"&UpPath="+UpPath;
	$.colorbox({	
		href:openurl
		,width:"500" 
		,height:"300"
		,title:""
		,iframe:true 
		,scrolling:false
		//,onClosed:function(){location.reload(true);}   
	}); 
}

function PopupCenterClassRoom(){
	openurl = "./organ_class_room_list.php?";
	$.colorbox({	
		href:openurl
		,width:"800" 
		,height:"500"
		,title:""
		,iframe:true 
		,scrolling:false
		//,onClosed:function(){location.reload(true);}   
	}); 
}


function DelayRoad(){
	setTimeout(function(){
		location.reload();
	},500);
	
}
</script>

<?php
include_once('./inc_member_wsconn_log.php');
include_once('./includes/common_footer.php');

// include_once('./inc_websocket_conn_MemberName.php');

?>

</body>
</html>
<?php
include_once('./includes/dbclose.php');
?>