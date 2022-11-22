<tr>
                <td><?=$ListNumber?></td>
                <td>
                    <label class="check_label link">
                        <input type="checkbox" class="check_input" name="MemberID[]" id="MemberID_<?=$MemberID?>" value='<?=$MemberID?>' <?if ($OldPangramLinkCenterDeviceGroupID!=0){?>checked<?}?>>
                        <span class="check_bullet"></span>
                    </label>
                </td>
                <td><?=$MemberName?></td>
                <td><?=$MemberLoginID?></td>

				<td>
					<?// sun 2022 11-21?>
					
						<input type="hidden" id="PangramLinkCenterDeviceGroupID" name="PangramLinkCenterDeviceGroupID" value="<?=$PangramLinkCenterDeviceID?>">

						<select id="PangramLinkCenterDeviceID_<?=$MemberID?>" name="PangramLinkCenterDeviceName"  class="search_select" style="width:;" onchange="CheckedBox();">
							<option value="">등록 기기 </option>
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
					
					$Stmt2 = null;
					?>
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

    <div class="fixed_btn_wrap"><a href="javascript:FormSubmit();" class="btn_black_gold">연결하기</a></div>    
	
</div>

<script language="javascript">
// sun 2022.11.21
var PangramLinkCenterDeviceGroupID = "<?=$PangramLinkCenterDeviceGroupID?>";
var ArrCheckedData = "";

// cheked 확인
var MemberChecked = $("input:checkbox[name='MemberID[]']:checked");

var length = MemberChecked.length;


function FormSubmit(){
	for(i = 0; i <length; i++){
		MemberID = MemberChecked[i].value;
		var PangramLinkCenterDeviceID = $('#PangramLinkCenterDeviceID_'+MemberID+' option:selected').val();
	
		ArrCheckedData = ArrCheckedData + PangramLinkCenterDeviceID +','+MemberID+'|'
	}
	console.log(ArrCheckedData);

	url = "./ajax_set_link_student_class_room.php";
	
	$.ajax(url, {
		data: {
			PangramLinkCenterDeviceGroupID : PangramLinkCenterDeviceGroupID,
			ArrCheckedData: ArrCheckedData
		},
		success: function (data) {
			$.confirm({
				title: "안내",
				content: "변경 했습니다.",
				buttons: {
					확인: function () {
						location.reload();
					}
				}
			});	
		},
		error: function () {

		}
	});	

}