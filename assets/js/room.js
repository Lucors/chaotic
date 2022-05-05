let sseRoomListenConnection = null;
// let sseRoomListenUSTGS = [];
// let sseRoomListenIgnore = false;

$(window).on("beforeunload", function() {
	if (sseRoomListenConnection){
		sseRoomListenConnection.close();
	}
});

function startRoomExitAnim(){
	stopRoomListener();
	startNotificationsListener();
	user.stage = 1;
	$("#room-list .list-refresh").click();
	// anime({
	// 	targets: "body", //цель анимации
	// 	top: "-100vh", 
	// 	duration: adur(1200), //адур - самописная, 
	// 	easing: "easeInOutCubic" //плавность анимации из библиотеки аниме
	// });
	
	// $("body").attr("class", "menu");
	bodygoto("menu");
}

function startRoom(){
	if (currentRoom.data.privacy == "0"){
		currentRoom["public_link"] = 
			`https://chaoticgame.ru/api/?route=room&op=join&room_id=${currentRoom.id}`;
		$("#public-link").addClass("active");
		$("#public-link .i1").val(currentRoom["public_link"]);
	}
	else {
		$("#public-link").removeClass("active");
	}
	startRoomListener();
}

function handleLobbyUsers(){
	$("#lobby .room-users .header .value")
		.html(currentRoom.users_list.length + "/4");
	$("#lobby .room-users > .users-list").empty();
	$.each(currentRoom.users_list, function(key, value){
		let user = $(getUserFromData(value));
		user.appendTo("#lobby .room-users > .users-list")
		// $("#lobby .room-users > .users-list").append(
		// 	getUserFromData(value)
		// );
		if (value.user_id == currentRoom.creator_id){
			user.addClass("creator");
			user.find(".actions").append(`<img class="user-stage host" src="assets/img/host.svg" title="Создатель">`);
		}
		else {
			if (value["ustage"] == 4){
				user
					.addClass("ready")
					.find(".actions")
					.append(`<img class="user-stage ready" src="assets/img/accept_2.svg" title="Готов">`);
			}
			else {
				user
					.addClass("not-ready")
					.find(".actions")
					.append(`<img class="user-stage not-ready" src="/assets/img/close.svg" title="Не готов">`);
			}
		}
	});
}


function startRoomListener(){
	console.log(`[START] слушатель комнаты #${currentRoom.id}`);
	sseRoomListenConnection = new EventSource(`api/daemon/room-listen.php?rid=${currentRoom.id}`);
	// Сервер присылает сообщение только при изменениях
	sseRoomListenConnection.onmessage = function(event) {
		// if (sseRoomListenIgnore){
		// 	return;
		// }
		if (event.data.length < 5){
			return;
		}
		var data = JSON.parse(event.data);
		// console.log(data);
		if (data.result){
			// $("#lobby .users-list > .user").each(function(index, el){});
			// console.log(data);
			currentRoom.users_list = data.users_list;
			handleLobbyUsers();
		}
		else {
			if (user.user_id != currentRoom.creator_id){
				// console.warn(data);
				switch(data.code) {
					case 1: //Создатель вышел из комнаты
						showWarning("Комната расформирована");
						sendExitRoom().always(startRoomExitAnim);
						break;
					case 2: //Создатель запустил игру
						stopRoomListener();
						location.reload();
						break;
					default: 
						break;
				}
			}
		}
	};
}
function stopRoomListener(){
	if (sseRoomListenConnection !== null && sseRoomListenConnection !== undefined){
		console.log("[END] слушатель комнаты");
		sseRoomListenConnection.close();
		sseRoomListenConnection = null;
	}
}

function canStartGame(){
	// return [true]; // Проверка работы со стороны PHP
	if (currentRoom.users_list.length < 2){
		return [false, "Недостаточно игроков"];
	}
	var output = [true];
	$.each(currentRoom.users_list, function(key, value){
		if (value["ustage"] == 3){
			output = [false, "Дождитесь готовности игроков!"];
			return;
		}
	});
	return output;
}


//-------------------------------------------------------------------------------------------------------
// DOCUMENT READY EVENT
//-------------------------------------------------------------------------------------------------------

$(document).ready(function(){
	$("#do-room-exit").click(function(){
		if (!$(this).attr('disabled')){
			$(this).attr("disabled", true);
			let confirmMessage = "Вы уверены что хотите покинуть комнату?";
			if (currentRoom.creator_id == user.user_id){
				confirmMessage += "<br>Комната будет расформирована"
			}
			generalModal.confirm({
				data: confirmMessage,
				apply: "Покинуть комнату",
				close: "Отмена"
			}).then(
				function(){
					sendExitRoom()
						.then(startRoomExitAnim)
				}
			).always(function(){
				$("#do-room-exit").attr("disabled", false);
			});
        }
	});


	$("#do-room-ready").click(function(){
		if (!$(this).attr('disabled')){
			$(this).attr("disabled", true);

			if (currentRoom.creator_id == user.user_id){
				let canStart = canStartGame();
				if (!canStart[0]){
					showCountdownMessage(canStart[1], 2000);
					sleep(adur(2000)).then(
						function(){
	            			$("#do-room-ready").attr("disabled", false);
						}
					)
					return;
				}
				apiRequest({
					route: "room",
					op: "start_room",
					room_id: currentRoom.id
				},{
					weight: -1
				}).then(
					function(data){
						// console.log(data);
						stopRoomListener();
						location.reload();
					},
					function(data){
						if (data.code == 1){
							showCountdownWarning(data.msg, 2000);
							sleep(adur(2000)).then(
								function(){
									$("#do-room-ready").attr("disabled", false);
								}
							)
						}
						else {
							if ("msg" in data){
                            	showError(data.msg);
							}
							$("#do-room-ready").attr("disabled", false);
						}
					}
				);
			}
			else {
				apiRequest({
					route: "room",
					op: "set_room_ready",
					ready: Boolean(user.stage == 3)
				}).then(
					function(data){
						let buttonValue = "Не Готов";
						if (user.stage == 4){
							user.stage = 3;
							buttonValue = "Готов";
						}
						else {
							user.stage = 4;
						}
						$("#do-room-ready .value").html(buttonValue);
						console.log(data);
					},
					function(data){
	            		// $(this).prop("disabled", false);
					}
				).always(
					function(data){
	            		$("#do-room-ready").attr("disabled", false);
					}
				);
			}
        }
	});
});