let sseNotifListenConnection = null;
let sseNotifListenData = null;
let sseMsgListenConnection = null;
flags.inSelfUser = false;
flags.inMessenger = false;
flags.inRoomInfo = false;
// flags.inSettings = false;
// flags.inRating = false;
// flags.inRoomCreate = false;

$(window).on("beforeunload", function() {
	if (sseNotifListenConnection){
		sseNotifListenConnection.close();
	}
	if (sseMsgListenConnection){
		sseMsgListenConnection.close();
	}
});

// async function showMenuLeftside(){
// 	list = $(".mm-buttons > .b1, #self-user");
// 	for (var i = list.length; i >= 0; i--){
// 		if (!list[i].hasClass("hidden")){
// 			$(list[i]).addClass("active");
// 		}
// 		await sleep(80);
// 	}
// }
function startMenu(listenSSE = true){
    $("#menu .b1").attr("disabled", false);
    if (listenSSE){
		startNotificationsListener();
    }
	stagger($(".mm-buttons > .b1, #self-user"), 80, function(el){
		if(!$(el).hasClass("hidden")){
			$(el).addClass("active");
		}
	}, false);
	// showMenuLeftside();
	// anime({
 //        targets: ".mm-buttons > .b1parent, #self-user",
 //        translateX: ["-100%", 0],
 //        duration: adur(250),
 //        delay: anime.stagger(adur(80), {direction: 'reverse'}),
 //        easing: easeOut
 //    });

    anime({
        targets: "#room-list > .substrate",
        scaleX: {
        	value: [0, 1],
        	easing: elasticOut1,
        	duration: adur(1200)
        },
        opacity: {
        	// value: 0.46,
        	value: 1,
        	easing: "linear",
        	duration: adur(600)
        },
        complete: function(){}
        // duration: 1200,
        // easing: spring1,
        // delay: 100
    });
    anime({
        targets: "#room-list > .body",
        opacity: 1,
        duration: adur(700),
        easing: easeOut,
        delay: adur(300)
    });
}

//анимация перехода в комнату
function startRoomAnim(creator = false){
	stopNotificationsListener();
	stopMessagesListener();
	if (!creator){
		var buttonValue = "Готов";
		if (user.stage == 4){
			buttonValue = "Не Готов";
		}
		$("#do-room-ready")
			.find(".value").html(buttonValue);
	}
	if (flags.inSelfUser){
		$("#self-user .left").click();
	}
	handleLobbyUsers();
	$("#room-list .room").remove();
	// anime({
	// 	targets: "body", //цель анимации
	// 	top: 0, 
	// 	duration: adur(1200), //адур - самописная, 
	// 	easing: "easeInOutCubic" //плавность анимации из библиотеки аниме
	// });

	// $("body").attr("class", "room");
	bodygoto("room");
	sleep(adur(500)).then(startRoom);
}

function openRoomInfoBlock(){
	$(".room-info.menu, #room-list > .substrate").addClass("active");
	flags.inRoomInfo = true;
}
function closeRoomInfoBlock(clearRoomData = true){
	$(".room-info-msg").html('');
	if (clearRoomData){
		currentRoom.clear();
	}
	$(".room-info.menu, #room-list > .substrate").removeClass("active loading");
	flags.inRoomInfo = false;
}
function toogleRoomInfoBlock(clearRoomData = true){
	if (flags.inRoomInfo){
		closeRoomInfoBlock(clearRoomData);
	}
	else {
		openRoomInfoBlock();
	}
}

function setupLogoutPresets(){
	if (flags.inSelfUser){
		$("#self-user .left").click();
	}
	window.user = {};
	flags.inRoomInfo = false;
	currentRoom.clear();
	stopNotificationsListener();
	stopMessagesListener();
	closeRoomInfoBlock();

    $("#auth .inputs input").val("");
    $("#do-login, #auth .inputs input").prop("disabled", false);
    $("#auth, #chaotic-logo, #authparent, #intro > .content").removeAttr("style");
    $("#auth, #arrows").removeClass("active");
}
function startLogoutAnim(){
	setupLogoutPresets();
	// anime({
 //        targets: "body",
 //        top: "-200vh",
 //        duration: adur(1200),
 //        easing: "easeInOutCubic",
 //        complete: function(){
 //        	// $(".parallax-bg > .shapes").removeAttr("style");
 //            $(".mm-buttons > .b1parent, #self-user, \
 //            	#room-list > .substrate, #room-list > .body, .room-info").removeAttr("style");
	// 		$(".parallax-bg > .shapes").removeAttr("style");
	// 		$("#room-list .room").removeClass("active");
	// 		$(".do-logout").prop("disabled", false);
 //        }
 //    });
	// initArrows();

    // $("body").attr("class", "initial");
    bodygoto("initial").then(function(){
    	$("#room-list > .substrate, #room-list > .body, .room-info.menu").removeAttr("style");
		$(".parallax-bg > .shapes").removeAttr("style");
		$(".mm-buttons > .b1, #self-user, #room-list .room").removeClass("active");
		$(".do-logout").attr("disabled", false);
    });
    setTimeout(startIntro, adur(400));
}

function drawSettings(data){
	$("#settings .fields").empty();
	$("#apply-settings-msg").html("");

	var fieldsparams = {
		allow_animation: {
			name: "Разрешить анимации",
			type: "checkbox"
		},
	} 
	$.each(data.settings_data, function(key, value){
		var field = `
		<div class="field">
			<div class="key">${fieldsparams[key]["name"]}</div>
			<div class="value interactive-parent">
		`;
		switch (fieldsparams[key]["type"]){
			case "number":
				field += `<input class="num1 grey" type="number" name="${key}" value="${value}">`;
				break;
			case "checkbox":
				field += `<input class="tgb1 grey" type="checkbox" name="${key}"`;
				if (value != 0){
					field += " checked";
				} 
				field += ">";
				break;
			default:
				field += `<input class="i1 grey" type="text" name="${key}" value="${value}">`;
				break;
		}
		field += `
			</div>
		</div>
		`;

		$("#settings .fields").append(field);
	});

	//Устарело
		// TypeID => (InputType, расшифровка, значение, name) 
		// InputType: (number - Числовой выбор), (checkbox - Чекбокс), (text - Иное, текст) 
		// $.each(settings, function(key, value){
		// 	var field = `
		// 		<div class="field">
		// 		<div class="key">${value[1]}</div>
		// 		<div class="value interactive-parent">
		// 	`;
		// 	switch (value[0]){
		// 		case "number":
		// 			field += `<input class="num1 grey" type="number" name="${key}" value="${value[2]}">`;
		// 			break;
		// 		case "checkbox":
		// 			field += `<input class="tgb1 grey" type="checkbox" name="${key}"`;
		// 			if (value[2] != '0'){
		// 				field += " checked";
		// 			} 
		// 			field += ">";
		// 			break;
		// 		default:
		// 			field += `<input class="i1 grey" type="text" name="${key}" value="${value[2]}">`;
		// 			break;
		// 	}
		// 	field += "</div></div>";

		// 	$("#settings .fields").append(field);
		// });

	$("#settings .fields .tgb1").click(tgbHandler);
}


function handleRoomInfoData(room){
	let data = room.data;
	let roomBlock = $(".room-info"); 
	let creatorName = data.creator_name + " " + getFormattedID(data.creator_id);

	roomBlock.find(".title .value").html(data.name);
	roomBlock.find(".title .value.with-id").html(`${data.name} #${data.room_id}`);
	roomBlock.find(".title .preview").attr("src", data.sprites_path + data.map_preview);
	roomBlock.find(".map .value").html(data.map_name);
	roomBlock.find(".creator .value").html(creatorName).attr("title", creatorName);
	roomBlock.find(".users .value").html(`${data.playes_num} / 4`);
	roomBlock.find(".step-time .value").html(`${data.step_time} сек.`);

	// Далее логика обработки приватности комнаты
	roomBlock.find(".password").removeClass("active");
	let privacy = "Открытая";
	switch(data.privacy){
		case "1":
			privacy = "Закрытая";
			roomBlock.find(".password").addClass("active");
			break;
		case "2":
			privacy = "Для друзей";
			break;
	}
	roomBlock.find(".privacy .value").html(privacy);

	drawTopics(room.topics_list, roomBlock);
}
function handleRoomListElement(data){
	let privacyIcoPath = "privacy-open.svg";
	switch(data["privacy"]){
        case "1":
            privacyIcoPath = "privacy-password.svg";
        	break;
        case "2":
            privacyIcoPath = "privacy-friends.svg";
        	break;
	}
	return `	
		<span class="title">${data["name"] + " #" + data.room_id}</span>
		<div class="vline"></div>
		<img class="closeness" src="assets/img/${privacyIcoPath}">
		<div class="vline"></div>
		<span class="users">${data["playes_num"]} / 4</span>
		<div class="vline"></div>
		<span class="map">${data["map_name"]}</span>
	`;
}
function handleRoomsList(data){
	$.each(data.rooms_list, function(key, value){
		$("#room-list .body").append(`
			<div class="room" rid="${value.room_id}">
				${handleRoomListElement(value)}
			</div>
		`);
	});
	$("#room-list .list-refresh").attr("disabled", false);
	$("#room-list .room").click(function(){
		if (!$(this).hasClass("obsolete")){
			$("#room-list .room").removeClass("active");
			$(this).addClass("active");

			currentRoom.id = $(this).attr("rid");
			$(".room-info").addClass("loading");
			getRoomData(currentRoom.id, -1).then(
				function(responsed){
					responsed.users_list = null;
					currentRoom.setup(responsed);

					$(".room-info").removeClass("loading");
					$(`.room[rid=${currentRoom.id}]`).html(handleRoomListElement(currentRoom.data));
					handleRoomInfoData(currentRoom);
						// Object.assign(currentRoom.data, dataTopics))
					if(!flags.inRoomInfo){
						openRoomInfoBlock();
					}
				},
				function(){
					$(".room-info").removeClass("loading");
					$("#room-list .room").removeClass("active");
					$(`.room[rid=${currentRoom.id}]`).addClass("obsolete");
					closeRoomInfoBlock();
				}
			)

			// $.when(getRoomData(currentRoom.id, -1), getRoomTopics(currentRoom.id, -1)).then()
			// $(".room-info").html($("#room-list .room.active > .title").text() + " ID: " + currentRoomID);
		}
	});
}


function toogleMessenger(force = null){
	if (force !== null && force !== undefined){
		flags.inMessenger = !force;
	}
	if (flags.inMessenger){
		stopMessagesListener();
		if ($("#messenger").hasClass("active")){
			startNotificationsListener();
		}
	    $("#messenger").removeClass("active");
		$("#profile-container > .users-list .user").removeClass("active");
		$("#messenger .dialogue").empty();
		$("#msgr-input").val("");
		// stopMessagesListener();

		anime({
	        targets: "#messenger",
	        left: "-22vw",
	        duration: adur(600),
	        easing: ease
	    });
	}
	else {
		anime({
	        targets: "#messenger",
	        left: "22vw",
	        duration: adur(500),
	        easing: ease,
	        delay: adur(100),
	        complete: function(){
	    		$("#messenger").addClass("active");
	        }
	    });
	}
	flags.inMessenger = !flags.inMessenger;
}

function getUserFromData(value) {
	var fkey = getFormattedID(value.user_id);
	return  `
		<div class="user friend" uid="${value.user_id}">
			<img class="u-avatar" src="${value.avatar_path}">

			<div class="info">
				<span class="u-nick" title="${value.nick}">
					${value.nick}
				</span>
				<span class="u-id">
					${fkey}
				</span>
			</div>

			<div class='vline'></div>

			<div class="actions">
			</div>
		</div>
	`;
}


function handleFriendsData(data){
	$.each(data.friends_list, function(key, value){
		$("#profile-container > .users-list").append(
			getUserFromData(value)
		);

		$(`	<div class="icob1">
				<img class="do-open-msg action" src="assets/img/message-ico.svg">
			</div>

		`).appendTo(
			`#profile-container > .users-list .user.friend[uid=${value.user_id}] .actions`
		).click(handleOpenMessenger);
	});
	if ("msg" in data){
		$("#profile-container > .users-list").html(`<span class='msg'>${data.msg}</span>`);
	}

	handleNotifListenData();
}

function handleOpenMessenger(){
	$user = $(this).closest(".user");

	if (!$user.hasClass("active")){
		stopMessagesListener();
		$("#profile-container > .users-list .user").removeClass("active");

		$user.addClass("active");
		getAllPrivateMessages($user.attr("uid"))
			.then(function(){
				$user.removeClass("attention");
				if (!$("#profile-container .user.friend").hasClass("attention")){
					delete sseNotifListenData["msgs"];
					$("#get-friends-list").removeClass("attention");

					if (!$("#get-notif-list").hasClass("attention")){
						$("#self-user .left").removeClass("attention");
					}
				}
			});
		toogleMessenger(true);
	}
}


function getNotificationFromData(value) {
	var fkey = getFormattedID(value.user_id);
	var output =  `
		<div class="user notification" nid="${value.notif_id}">
		<img class="u-avatar" src="${value.path}">
		<div class="info">
	`;

	switch (value["type"]){
		case "0":
			output += value['content'];
			break;
		case "1":
			output +=  ` 
				Игрок ${value['nick']} (${fkey})<br> 
				Желает добавить вас в друзья.
			`;
			break;
		case "2":
			output +=  ` 
				Игрок ${value['nick']} (${fkey})<br> 
				Приглашает вас в игру.
			`;
			break;
	}

	output += `
		</div>
			<div class='vline'></div>
			<div class="actions">
			</div>
		</div>
	`;
	return output;
}
function handleNotificationsData(data){
	$.each(data.notif_list, function(key, value){
		$("#profile-container > .users-list").append(
			getNotificationFromData(value)
		);


		$(`<img class="do-notif-accept action icob1" src="assets/img/accept.svg">`).appendTo(
			`#profile-container > .users-list .user.notification[nid=${value.notif_id}] .actions`
		).click(sendAnswerNotification);
		if (value["type"] == "1"){
			$(`<img class="do-notif-deny action icob1" src="assets/img/close.svg">`).appendTo(
				`#profile-container > .users-list .user.notification[nid=${value.notif_id}] .actions`
			).click(sendAnswerNotification);
		}
	});
	if ("msg" in data){
		$("#profile-container > .users-list").html(`<span class='msg'>${data.msg}</span>`);
	}
}

function sendAddFriend(){
	var uid = $(this).closest(".user").attr("uid");
	var data = {
		"op": "set",
		"type": 1,
		"uid": uid
	};

	apiRequest({
		route: "notification",
		op: "set",
		target: uid,
		type: 1
	}, {
		method: "POST",
		success: function(data){
            if (data.result){
            	$(`#profile-container > .users-list .user[uid=${uid}] .actions`).empty();
            }
            else {
                if ("msg" in data){
                    showWarning(data.msg);
                }
                this.error();
            }
        },
        error: function(){
        }
	})

 	// $.ajax({
  //       url: "notifications.php",
  //       method: "POST",
  //       data: data,
  //       dataType: "json",
  //       success: function(data){
  //           if (data.result){
  //           	$(`#profile-container > .list .user[uid=${uid}] .actions`).empty();
  //           }
  //           else {
  //               if ("msg" in data){
  //                   showWarning(data.msg);
  //               }
  //               this.error();
  //           }
  //       },
  //       error: function(){
  //       }
  //   });
}


function sendAnswerNotification(){
	var nid = $(this).closest(".notification").attr("nid")
	var answer = 1;
	if ($(this).hasClass("do-notif-deny")){
		answer = 0;
	}

	apiRequest({
		route: "notification",
		op: "answer",
		answer: answer,
		notif_id: nid
	}, {
		method: "POST",
        success: function(data){
            if (data.result){
            	$(`#profile-container > .users-list .notification[nid=${nid}]`).remove();

            	if ($("#profile-container > .users-list").children().length == 0){
					delete sseNotifListenData["notifs"];
					$("#get-notif-list").removeClass("attention");

					if (!$("#get-friends-list").hasClass("attention")){
						$("#self-user .left").removeClass("attention");
					}
				}
            }
            else {
                if ("msg" in data){
                    showWarning(data.msg);
                }
                this.error();
            }
        },
        error: function(){
        }
	});

	// data = {
	// 	"op": "answer",
	// 	"answer": answer,
	// 	"nid": nid
	// }
	// $.ajax({
 //        url: "notifications.php",
 //        method: "POST",
 //        data: data,
 //        dataType: "json",
 //        success: function(data){
 //            if (data.result){
 //            	$(`#profile-container > .list .notification[nid=${nid}]`).remove();

 //            	if ($("#profile-container > .list").children().length == 0){
	// 				delete sseNotifListenData["notifs"];
	// 				$("#get-notif-list").removeClass("attention");

	// 				if (!$("#get-friends-list").hasClass("attention")){
	// 					$("#self-user .left").removeClass("attention");
	// 				}
	// 			}
 //            }
 //            else {
 //                if ("msg" in data){
 //                    showWarning(data.msg);
 //                }
 //                this.error();
 //            }
 //        },
 //        error: function(){
 //        }
 //    });
}


// ЗАПРОС!
function getAllPrivateMessages(uid){
	$("#messenger .dialogue").empty();
	$("#msgr-input").val("");

    return apiRequest({
        route: "message",
        op: "getall",
        target: uid
    }, {
    	success: function(data){
            if (data.result){
                var lastMsgID = handlePrivateMessagesList(data.msg_list);
                stopNotificationsListener();
				startMessagesListener($user.attr("uid"), lastMsgID);
            	this.resolve(data);
            }
            else {
                if ("msg" in data){
                    showError(data.msg);
                }
                this.error(data);
            }
        },
    });

	// var data = {
	// 	"op": "getall",
	// 	"uid": uid
	// }
	// return promisedAjax({
 //        url: "private-messages.php",
 //        data: data,
 //        success: function(data){
 //            if (data.result){
 //                var lastMsgID = handlePrivateMessagesList(data.list);
 //                stopNotificationsListener();
	// 			startMessagesListener($user.attr("uid"), lastMsgID);
 //            	this.resolve(data);
 //            }
 //            else {
 //                if ("msg" in data){
 //                    showError(data.msg);
 //                }
 //                this.error(data);
 //            }
 //        },
	// });
}
// ОБРАБОТКА ЗАПРОСА!
function handlePrivateMessagesList(list){
	lastMsgID = -1;
	$.each(list, function(index, value){
		$("#messenger .dialogue").append(
			getPrivateMessageFromData(value)
		);
		lastMsgID = value["private_message_id"];
	});
	scrollToBottom($("#messenger .dialogue"));
	return lastMsgID;
}
// ПОЛУЧЕНИЕ ВИДА ОДНОГО СООБЩЕНИЯ!
function getPrivateMessageFromData(value) {
	var type = "incoming";
	if (value["sender_id"] == user["user_id"]){
		type = "outgoing";
	}

	return  `
		<div class="msg ${type}" pmid="${value['private_message_id']}">
    		<div class="msg-body">${value['content']}</div>
    	</div>
	`;
}

function getRatingFromData(data){
	return `
		<div class="position">${data.pos}</div>
		<div class="vline"></div>
		<div class="name">${data.nick}</div>
		<div class="vline"></div>
		<div class="count-won">${data.won}</div>
		<div class="vline"></div>
		<div class="count-total">${data.total}</div>
	`;
}
function drawRatings(data){
	$("#ratings .fields .field:not(.header)").remove();
	$("#ratings .user-rating").empty();

	var leaderScore = 1;
	$.each(data.rating_list, function(index, value){
		if (value.won > leaderScore){
			leaderScore = value.won;
		}
	});
	$.each(data.rating_list, function(index, value){
		var fieldValue = getRatingFromData(value);
		$(`
			<div class="field">
				${fieldValue}
			</div>
		`).appendTo("#ratings .fields").attr("style", 
		"");
	});

	if (!("user_rating" in data)){
		$("#ratings .user-rating").html(`
			Вы не входите в первую сотню игроков
		`);
	}
	else {
		$("#ratings .user-rating").html(
			getRatingFromData(data.user_rating)
		);
	}
}

// [map_id, name]
function drawMaps(maps){
	$("#room-create-form .field.maps .value").empty();

	$.each(maps, function(index, value){
		var map = `
			<option value="${value.map_id}">${value.name}</option>
		`;

		$("#room-create-form .field.maps .value").append(map);
	});
};
// [topic_id, name, icon_path]
function drawTopics(topics, parent, selectable = false){
	parent.find(".field.topics > .value").empty();

	var topicClass = "topic";
	if (selectable){
		topicClass += " b2";
	} 
	$.each(topics, function(index, value){
		// Шаблон темы
		var topic = `
			<div class="${topicClass}" tid="${value.topic_id}" title="${value.name}">
				<img class="icon" src="${value.icon_path}">
				<div class="name">${value.name}</div>
			</div>
		`;

		parent.find(".field.topics .value").append(topic);
	});

	if (selectable){
		parent.find(".field.topics .topic").click(selectTopicHandler);
	}
};
function selectTopicHandler(){
	if ($(this).hasClass("selected")){
		$(this).removeClass("selected");
	}
	else {
		if ($("#room-create-form .topics .topic.selected").length < 4){
			$(this).addClass("selected");
		}
	}
}
function sendCreateRoom(data){
	return apiRequest({
		route: "room",
		op: "create",
		room_data: data
	}, {
		method: "POST",
		success: function(data){
	        if (!data.result){
	            if ("msg" in data){
					$("#room-create-msg").html(data.msg);
	            }
	            this.error(data);
	        }
	        this.resolve(data)
        }
	})

	// data = JSON.stringify(data);
	// return promisedAjax({
 //        url: "rooms.php",
 //        data: `op=create&data=${data}`,
 //        success: function(data){
	//         if (!data.result){
	//             if ("msg" in data){
	// 				$("#room-create-msg").html(data.msg);
	//             }
	//             this.error(data);
	//         }
	//         this.resolve(data)
 //        }
	// });
}


function startNotificationsListener(){
	console.log("[START] слушатель уведомлений");
	sseNotifListenConnection = new EventSource("api/daemon/notification-listen.php");
	sseNotifListenConnection.onmessage = function(event) {
		sseNotifListenData = JSON.parse(event.data);
		handleNotifListenData();
	};
}
function handleNotifListenData(){
	if (sseNotifListenData.result){
		// console.log(sseNotifListenData.msgs);
		$("#self-user .left").addClass("attention");

		if ("notifs" in sseNotifListenData){	
			$("#get-notif-list").addClass("attention");
		}
		if ("msgs" in sseNotifListenData){
			$("#get-friends-list").addClass("attention");

			$.each(sseNotifListenData.msgs, function(index, value){
				$("#profile-container .user.friend[uid="+value+"]").addClass("attention");
			})
		}
	}
	else {
		$("#self-user .left, #get-friends-list, #get-notif-list, #profile-container .user.friend").removeClass("attention");
	}
}
function stopNotificationsListener(){
	if (sseNotifListenConnection){
		console.log("[END] слушатель уведомлений");
		sseNotifListenConnection.close();
		sseNotifListenConnection = null;
	}
}



function startMessagesListener(uid, lastMsgID){
	console.log(`[START] слушатель диалога с ${uid}`);
	sseMsgListenConnection = new EventSource(`api/daemon/message-listen.php?uid=${uid}&lastid=${lastMsgID}`);
	sseMsgListenConnection.onmessage = function(event) {
		var data = JSON.parse(event.data);
		if (data.result){
			handlePrivateMessagesList(data.list);
		}
	};
}
function stopMessagesListener(){
	if (sseMsgListenConnection){
		console.log("[END] слушатель сообщений");
		sseMsgListenConnection.close();
		sseMsgListenConnection = null;
	}
}


//-------------------------------------------------------------------------------------------------------
// DOCUMENT READY EVENT
//-------------------------------------------------------------------------------------------------------

$(document).ready(function(){
	window.onbeforeunload = function(){
		stopNotificationsListener();
		stopMessagesListener();
	}


    $(".u-id").click(function(){
    	$("body").append(`<input class="forcopy" value="${$(this).html()}">`);
    	$(".forcopy").get(0).select();
    	document.execCommand("copy");
    	$(".forcopy").remove();

    	// showMessage("Скопировано!", true);
    	showCountdownMessage("Скопировано!", 800);
    })

	$("#self-user .left").click(function(){
        if (!$(this).is('.disabled')){
			$("#self-user .left").addClass("disabled");
			if (flags.inSelfUser){
				toogleMessenger(false);
				anime({
			        targets: "#profile-container",
			        left: "-22vw",
			        duration: adur(500),
			        easing: ease,
					complete: function(){
						$("#profile-container > .users-list").empty();
					}
			    });

				dummies.close().then(function(){
					$("#self-user").css({
						zIndex: 1
					});
					$("#self-user .left").removeClass("disabled");
				})
			}
			else {
				$("#get-friends-list").click();
				$("#self-user").css({
					zIndex: 102
				});
				anime({
			        targets: "#profile-container",
			        left: 0,
			        duration: adur(500),
			        easing: ease,
			        delay: adur(100)
			    });

				dummies.left().then(function(){
					$("#self-user .left").removeClass("disabled");
				})
			}
			flags.inSelfUser = !flags.inSelfUser;
        }
	});


    onEnter($("#query-input"), function(){
        $("#do-search-users").click();
        $(this).focus();
    })

	$("#do-search-users").click(function(){
		apiRequest({
	        route: "user",
	        op: "getby_query",
	       	query: $("#query-input").val()
	    }, {
	        success: function(data){
				$("#profile-container > .users-list").empty();

	            if ("msg" in data){
	                if ("msg" in data){
	                    $("#profile-container > .users-list").html(`<span class='msg'>${data.msg}</span>`);
	                }
	                this.error();
	            }
	            else {
	            	$.each(data.users_list, function(key, value){
						$("#profile-container > .users-list").append(
							getUserFromData(value)
						);
						if (value["friend"] == 0){
							$(`<img class="do-send-addfriend action icob1" src="assets/img/add-friend-ico.svg">`).appendTo(
								`#profile-container > .users-list .user.friend[uid=${value.user_id}] .actions`
							).click(sendAddFriend);
						}
					});
	            }
	        },
	        error: function(){
	        }
	    });

		// var data = {
		// 	"op": "getbyquery",
		// 	"query": $("#query-input").val()
		// };
	 	// $.ajax({
	  //       url: "users.php",
	  //       method: "POST",
	  //       data: data,
	  //       dataType: "json",
	  //       success: function(data){
			// 	$("#profile-container > .list").empty();

	  //           if (data.result){
			// 		$.each(data.list, function(key, value){
			// 			$("#profile-container > .list").append(
			// 				getUserFromData(value)
			// 			);
			// 			if (value["friend"] == 0){
			// 				$(`<img class="do-send-addfriend action icob1" src="assets/img/add-friend-ico.svg">`).appendTo(
			// 					`#profile-container > .list .user.friend[uid=${key}] .actions`
			// 				).click(sendAddFriend);
			// 			}
			// 		});
	  //           }
	  //           else {
	  //               if ("msg" in data){
	  //                   $("#profile-container > .list").html(`<span class='msg'>${data.msg}</span>`);
	  //               }
	  //               this.error();
	  //           }
	  //       },
	  //       error: function(){
	  //       }
	  //   });
	});

	$("#get-friends-list").click(function(){
		$("#profile-container > .users-list").empty();
		getAllFriends().then(handleFriendsData);
		toogleMessenger(false);
	});

	$("#get-notif-list").click(function(){
		$("#profile-container > .users-list").empty();
		getAllNotifications().then(handleNotificationsData);
		toogleMessenger(false);
	});


	onEnter($("#msgr-input"), function(){
        $("#do-send-msg").click();
        $(this).focus();
    });

	$("#do-send-msg").click(function(){
		var message = $("#msgr-input").val();
		if (message.length == 0){
			return;
		}
		apiRequest({
			route: "message",
			op: "set",
			target: $(".user.friend.active").attr("uid"),
			content: message
		}, {
			method: "POST",
	        success: function(data){
	            if (data.result){
	            	$("#msgr-input").val("");
	            	$("#messenger .dialogue").append(
						getPrivateMessageFromData({
							"private_message_id": data.message_id,
							"sender_id": user.user_id,
							"content": message
						})
					);
					scrollToBottom($("#messenger .dialogue"));
	            }
	            else {
	                if ("msg" in data){
	                 	showError(data.msg);
	                }
	                this.error();
	            }
	        },
	        error: function(){
	        }
		})
		// var rdata = {
		// 	"op": "set",
		// 	"content": $("#msgr-input").val(), 
		// 	"uid": $(".user.friend.active").attr("uid")
		// };
		// $.ajax({
	 //        url: "private-messages.php",
	 //        method: "POST",
	 //        data: rdata,
	 //        dataType: "json",
	 //        success: function(data){
	 //            if (data.result){
	 //            	$("#msgr-input").val("");
	 //            	$("#messenger .dialogue").append(

		// 				getPrivateMessageFromData({
		// 					"private_message_id": data.private_message_id,
		// 					"sender_id": user["user_id"],
		// 					"content": rdata["content"]
		// 				})
		// 			);
		// 			scrollToBottom($("#messenger .dialogue"));
	 //            }
	 //            else {
	 //                if ("msg" in data){
	 //                 	showError(data.msg);
	 //                }
	 //                this.error();
	 //            }
	 //        },
	 //        error: function(){
	 //        }
	 //    });
	});


	$("#room-list .list-refresh").click(function(){
        if (!$(this).attr('disabled')){
        	$(this).attr("disabled", true);
			$("#room-list .room").remove();
			closeRoomInfoBlock();

			// UPD ROOMS HERE
			// MAXIMUM 40 ROOMS
			getRoomsList().then(handleRoomsList, function(){
            	$("#room-list .list-refresh").attr("disabled", false);
			});
        }
	})

	$("#q-edit").click(function(){
		if (!$(this).attr('disabled')){
            $(this).attr("disabled", true);
			showModal($("#temp-unav"), function(){
				$("#q-edit").attr("disabled", false);
			});
		}
	});

	// $("#quick-game-modal .do-retry").click(function(){
    // 	$("#quick-game-msg").html("");
    // 	$("#quick-game-modal .loading-ico").attr("class", "loading-ico searching");
    // 	$("#quick-game-modal").find(".modal-close, .hline, .actions").addClass("hide");

    // 	// ЗАПРОС ТУТ

    //     //Вместо таймера использовать событие reject от запроса
    //     sleep(5000).then(function(){
    //     	$("#quick-game-msg").html("Не найдена подходящая игра");
    //     	$("#quick-game-modal .loading-ico").attr("class", "loading-ico fail");
    //     	$("#quick-game-modal").find(".modal-close, .hline, .actions").removeClass("hide");
    //     });
	// });
	function searchQuickGame(){
		sleep(adur(5000)).then(function(){
			generalModal
				.set({
					data: "Не найдена подходящая игра"
				})
				.loadingState(false);
		})
	}
	$("#do-quick-game").click(function(){
        if (!$(this).attr('disabled')){
            $(this).attr("disabled", true);
			generalModal.loading({
				title: 		 "Поиск игры",
				apply: 		 "Повторить попытку",
				applyHandler: function(){
					generalModal
						.set({
							data: ""
						})
						.loadingState(true);
					searchQuickGame();
				}
			}).fail(function(){
				$("#do-quick-game").attr("disabled", false);
			})
			searchQuickGame();

            // showModal($("#quick-game-modal"), function(){
            // 	$("#do-quick-game").attr("disabled", false);
            // });
            // $("#quick-game-modal .do-retry").click();
        }
	});

	$("#show-room-create").click(function(){
		if (!$(this).attr('disabled')){
            $(this).attr("disabled", true);

			$("#room-create-msg").html("");

            getAllTopics()
	            .then(function(data){
	        		drawTopics(data.topics_list, $("#room-create-form "), true);
		    		getAllMaps().then(function(data){
	        			drawMaps(data.maps_list);
		        		showModal($("#room-create-form"), function(){
			            	$("#show-room-create").attr("disabled", false);
			            });
		    		},
		        	function(data){
						$("#show-room-create").attr("disabled", false);
		        	})
	        	},
	        	function(data){
					$("#show-room-create").attr("disabled", false);
	        	});
        		
        }
	});
	$("#room-create-form .field.privacy .value").mouseup(function(){
		var $fieldPassword = $("#room-create-form .field.password");
		if ($(this).val() == 1){
			$fieldPassword.addClass("active");
			return;
		}
		$fieldPassword.removeClass("active");
		scrollToBottom($("#room-create-form .fields"));
	});
	$("#room-create-form .modal-close, #room-create-form .close").click(function(){
		$("#do-create-room").attr("disabled", false);
		$("#room-create-form .field.topics .value").empty();
		$("#room-create-msg").html("");
	});
	$("#room-create-form .field.password .postfix").click(function(){
		var $passwordValue = $("#room-create-form .field.password .value");
		if ($(this).hasClass("showed")){
			$(this).removeClass("showed");
			$passwordValue.attr("type", "password");
		}
		else {
			$(this).addClass("showed");
			$passwordValue.attr("type", "text");
		}
	});
	$("#do-create-room").click(function(){
		if (!$(this).is(':disabled')){
            $(this).prop("disabled", true);
			$("#room-create-msg").html("");

			var $form = $("#room-create-form");
			var $fieldPassword = $form.find(".field.password");
			var data = {
				name: $form.find(".field.name .value").val(),
				step_time: $form.find(".field.step-time .value").val(),
				privacy: $form.find(".field.privacy .value").val(),
				password: $fieldPassword.find(".value").val(),
				map_id: $form.find(".field.maps .value").val(),
				topics: [],
	        }


	        $("#room-create-form .field.topics .topic.selected").each(function(){	
	        	data.topics.push($(this).attr("tid"));
	        });

	        try {
	            if (!data.name){
	                throw new Error("Название комнаты");
	            }
	            else if (!data.step_time){
	                throw new Error("Время ответа");
	            }
	            else if (!data.map_id){
	                throw new Error("Карта");
	            }
	            else if (!data.topics.length){
	                throw new Error("Темы вопросов");
	            }
	            else if (!data.privacy){
	                throw new Error("Приватность");
	            }
	            else if ($fieldPassword.hasClass("active") && !data.password){
	                throw new Error("Пароль");
	            }
	            else if ($fieldPassword.hasClass("active") && data.password.length < 4){
		            $("#room-create-msg").html("Пароль обязан содержать не менее 4-х символов");
		            $(this).prop("disabled", false);
		            return;
	            }
	        }
	        catch (error){
	            $("#room-create-msg").html(`Заполните поле "${error.message}"`);
	            $(this).prop("disabled", false);
	            return;
	        }
			if (data.name.length > 25){
	            $("#room-create-msg").html("Название комнаты слишком длинное");
	            $(this).prop("disabled", false);
	            return;
			}

	        sendCreateRoom(data)
		        .done(function(responsed){
		        	currentRoom.setup(responsed);
		        	user.stage = 4;

					handleRoomInfoData(currentRoom);
		        	$("#room-create-form .close").click();
		        	$("#do-room-ready")
		        		.find(".value").html("Начать игру");
		        	startRoomAnim(true);
		        })
		        .fail(function(data){
		        	currentRoom.clear();
		        	$("#do-create-room").prop("disabled", false);
		        });
		}
	});

	$("#do-room-enter").click(function(){
		if (!$(this).attr('disabled')){
            $(this).attr("disabled", true);
			$(".room-info-msg").html('');

			apiRequest({
				route: "room",
				op: "enter",
				room_id: currentRoom.id,
				password: $("#room-info-parent .field.password input").val()
			},{
				weight: -1
			}).then(
				function(data){
					$(".room-info-msg").html("");
		        	user.stage = 3;
					currentRoom.users_list = data.users_list;
					startRoomAnim();
					sleep(adur(100)).then(function(){
						closeRoomInfoBlock(false);
            			$("#do-room-enter").attr("disabled", false);
					})
				},
				function(data){
					$(".room-info-msg").html(data.msg);
            		$("#do-room-enter").attr("disabled", false);
				}
			);
		}
	})


	$("#do-rating").click(function(){
		if (!$(this).attr('disabled')){
            $(this).attr("disabled", true);

        	getAllRatings()
	        	.then(function(data){
	        		drawRatings(data);
	        		showModal($("#ratings"), function() {
	            		$("#do-rating").attr("disabled", false);
	        		});
	        	}, function(){
            		$("#do-rating").attr("disabled", false);
	        	})
        }
	});
	$("#ratings .modal-close").click(function(){
		$("#ratings .fields .field:not(.header)").remove();
		$("#ratings .user-rating").empty();
	});


	$(".do-settings").click(function(){
        if (!$(this).attr('disabled')){
            $(this).attr("disabled", true);
        	getAllSettings()
	        	.then(function(data){
	            	$(".do-settings").attr("disabled", false);
	        		drawSettings(data);
	        		showModal($("#settings"));
	        	}, function(){
	            	$(".do-settings").attr("disabled", false);
	        	});
			// flags.inSettings = !flags.inSettings;
        }
	});
	$("#settings .modal-close").click(function(){
		$("#settings .fields").empty();
		$("#apply-settings-msg").html("");
	});
	$("#do-apply-settings").click(function(){
        if (!$(this).attr('disabled')){
            $(this).attr("disabled", true);

			$("#apply-settings-msg").html("");
			var settings = {};
			var value = null;
			$("#settings .field input").each(function(){
				value = null;
				switch ($(this).attr("type")){
					case "checkbox":
						value = 0;
						if ($(this).prop("checked")){
							value = 1;
						}
						break;
					default:
						value = $(this).val();
						break;
				}

				settings[$(this).attr("name")] = value;
			});


		    apiRequest({
		        route: "profile",
		        op: "set_settings",
		        settings_data: settings
		    }, {
		    	method: "POST",
		    	weight: 1
		    }).then(
		    	function(data){
            		$("#do-apply-settings").attr("disabled", false);
			    	$("#apply-settings-msg").html("Настройки сохранены");
					user.settings_data = settings;
					applySettings(user.settings_data);
		    	}, 
		    	function(data){
		    		if ("msg" in data){
						$("#apply-settings-msg").html(data.msg);
	                }
            		$("#do-apply-settings").attr("disabled", false);
		    	}
		    );

			// promisedAjax({
		 //        url: "settings.php",
		 //        data: `op=set&data=${JSON.stringify(settings)}`,
		 //        weight: 1
		 //    })
		 //    .then(
		 //    	function(data){
			//     	$("#apply-settings-msg").html("Настройки сохранены");
			// 		applySettings(settings);
		 //    	}, 
		 //    	function(data){
		 //    		if ("msg" in data){
			// 			$("#apply-settings-msg").html(data.msg);
	  //               }
		 //    	}
		 //    );
		}
	});

	$(".do-logout").click(function(){
        if (!$(this).attr('disabled')){
        	$(this).attr("disabled", true);
            $("#auth-fail-msg").html("");

            apiRequest({
            	route: "auth",
            	op: "logout"
            }, {
            	success: function(data){
					if (data.result){
						startLogoutAnim();
					}
				},	
            })
   //  		$.get({
			// 	url: "logout.php",
			// 	success: function(data){
			// 		if (data.result){
			// 			startLogoutAnim();
			// 		}
			// 	},
			// 	dataType: "json"
			// });
        }
	});

    $("#messenger .close").click(function(){
        toogleMessenger(false);
    });
});