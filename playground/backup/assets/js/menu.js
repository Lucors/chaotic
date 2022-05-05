let sseMenuConnection = null;
let sseMsgListenConnection = null;
let currentRoomID = -1;
flags.inSelfUser = false;
flags.inMessenger = false;
flags.inRoomInfo = false;
// flags.inSettings = false;
// flags.inRating = false;
// flags.inRoomCreate = false;


function startMenu(){
	anime({
        targets: ".mm-buttons > .b1parent, #self-user",
        translateX: ["-100%", 0],
        duration: adur(250),
        delay: anime.stagger(adur(80), {direction: 'reverse'}),
        easing: easeOut
    });

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
        complete: function(){
        	$(".parallax-bg > .shapes:nth-child(1)").css({
        		animation: "200s ease-in-out 0s infinite normal none running pp-bg1"
        	});
        	$(".parallax-bg > .shapes:nth-child(2)").css({
        		animation: "190s ease-in-out 0s infinite normal none running pp-bg2"
        	});
        	$(".parallax-bg > .shapes:nth-child(3)").css({
        		animation: "170s ease-in-out 0s infinite normal none running pp-bg3"
        	});
        }
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
function startRoomAnim (){   //вызываем именно создание комнаты 
	anime({
		targets: "body", //цель анимации
		top: 0, 
		duration: adur(1200), //адур - самописная, 
		easing: "easeInOutCubic" //плавность анимации из библиотеки аниме
	});
}


function openRoomInfoBlock(){
	$("#room-list > .substrate").css({
		borderRadius: "0.7vw 0 0 0.7vw"
	});
	$("#room-info").css({
		left: "calc(0% + var(--authsub-b-w))"
	});
	flags.inRoomInfo = true;
}
function closeRoomInfoBlock(){
	$("#room-list > .substrate").css({
		borderRadius: "0.7vw"
	});
	$("#room-info").css({
		left: "-100%"
	});
	flags.inRoomInfo = false;
}
function toogleRoomInfoBlock(){
	if (flags.inRoomInfo){
		closeRoomInfoBlock();
	}
	else {
		openRoomInfoBlock();
	}
}

function startLogoutAnim(){
	if (flags.inSelfUser){
		$("#self-user .left").click();
	}
	window.user = {};
	flags.inRoomInfo = false;
	currentRoomID = -1;
	stopGeneralListener();
	stopMessagesListener();

    $("#auth .inputs input").val("")
    $("#do-login, #auth .inputs input").prop("disabled", false);
	anime({
        targets: "body",
        top: "-200vh",
        duration: adur(1200),
        easing: "easeInOutCubic",
        complete: function(){
        	// $(".parallax-bg > .shapes").removeAttr("style");
            $(".mm-buttons > .b1parent, #self-user, \
            	#room-list > .substrate, #room-list > .body, #room-info").removeAttr("style");
			$(".parallax-bg > .shapes").removeAttr("style");
			$("#room-list .room").removeClass("active");
			$(".do-logout").prop("disabled", false);
        }
    });
	initArrows();
    setTimeout(startIntro, adur(400));
}

function drawSettings(settings){
	$("#settings .fields").empty();
	$("#apply-settings-msg").html("");

	// TypeID => (InputType, расшифровка, значение, name) 
	// InputType: (number - Числовой выбор), (checkbox - Чекбокс), (text - Иное, текст) 
	$.each(settings, function(key, value){
		var field = `
			<div class="field">
			<div class="key">${value[1]}</div>
			<div class="value interactive-parent">
		`;
		switch (value[0]){
			case "number":
				field += `<input class="num1 grey" type="number" name="${key}" value="${value[2]}">`;
				break;
			case "checkbox":
				field += `<input class="tgb1 grey" type="checkbox" name="${key}"`;
				if (value[2] != '0'){
					field += " checked";
				} 
				field += ">";
				break;
			default:
				field += `<input class="i1 grey" type="text" name="${key}" value="${value[2]}">`;
				break;
		}
		field += "</div></div>";

		$("#settings .fields").append(field);
	});

	$("#settings .fields .tgb1").click(tgbHandler);
}


function handleRoomsList(list){
	$.each(list, function(key, value){
		var privacyIcoPath = "privacy-open.svg";

		switch(value["privacy"]){
	        case "1":
	            privacyIcoPath = "privacy-password.svg";
	        	break;
	        case "2":
	            privacyIcoPath = "privacy-friends.svg";
	        	break;
		}

		$("#room-list .body").append(`
			<div class="room" rid="${key}">
				<span class="title">${value["name"]}</span>
			    	<div class="vline"></div>
				<img class="closeness" src="assets/img/${privacyIcoPath}">
			    	<div class="vline"></div>
			    <div class="users">
			     	<span class="phantom">Игроки</span>
					<span class="data">${value["playes_num"]} / 4</span>
			    </div>
			</div>
		`);
	});

	$("#room-list .list-refresh").prop("disabled", false);

	$("#room-list .room").click(function(){
		$("#room-list .room").removeClass("active");

		$(this).addClass("active");
		currentRoomID = $(this).attr("rid");

		// GET AND HANDLE ROOM DATA HERE
		$("#room-info").html($("#room-list .room.active > .title").text() + " ID: " + currentRoomID);

		if(!flags.inRoomInfo){
			openRoomInfoBlock();
			currentRoomID = $(this).attr("rid");
		}
	});
}



function toogleMessenger(force = null){
	if (force !== null && force !== undefined){
		flags.inMessenger = !force;
	}
	if (flags.inMessenger){
		$("#profile-container > .list .user").removeClass("active");
		$("#messenger .dialogue").empty();
		$("#msgr-input").val("");
		stopMessagesListener();

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
	        delay: adur(100)
	    });
	}
	flags.inMessenger = !flags.inMessenger;
}

function getUserFromData(key, value) {
	var fkey = getFormattedID(key);
	return  `
		<div class="user friend" uid="${key}">
			<img class="u-avatar" src="${value['path']}">

			<div class="info">
				<span class="u-nick">
					${value['nick']}
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


function handleFriendsList(list){
	$.each(list, function(key, value){
		$("#profile-container > .list").append(
			getUserFromData(key, value)
		);

		$(`<img class="do-open-msg action icob1" src="assets/img/message-ico.svg">`).appendTo(
			`#profile-container > .list .user.friend[uid=${key}] .actions`
		).click(handleOpenMessenger);
	});
}

function handleOpenMessenger(){
	$user = $(this).closest(".user");

	if (!$user.hasClass("active")){
		stopMessagesListener();
		$("#profile-container > .list .user").removeClass("active");

		$user.addClass("active");
		getAllPrivateMessages($user.attr("uid")).done(
		);
		toogleMessenger(true);
	}
}


function getNotificationFromData(key, value) {
	var fkey = getFormattedID(value["user_id"]);
	var output =  `
		<div class="user notification" nid="${key}">
		<img class="u-avatar" src="${value['path']}">
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
function handleNotificationsList(list){
	$.each(list, function(key, value){
		$("#profile-container > .list").append(
			getNotificationFromData(key, value)
		);


		$(`<img class="do-notif-accept action icob1" src="assets/img/accept.svg">`).appendTo(
			`#profile-container > .list .user.notification[nid=${key}] .actions`
		).click(sendAnswerNotification);
		if (value["type"] == "1"){
			$(`<img class="do-notif-deny action icob1" src="assets/img/close.svg">`).appendTo(
				`#profile-container > .list .user.notification[nid=${key}] .actions`
			).click(sendAnswerNotification);
		}
	});
}

function sendAddFriend(){
	var uid = $(this).closest(".user").attr("uid");
	var data = {
		"op": "set",
		"type": 1,
		"uid": uid
	};

 	$.ajax({
        url: "notifications.php",
        method: "POST",
        data: data,
        dataType: "json",
        success: function(data){
            if (data.result){
            	$(`#profile-container > .list .user[uid=${uid}] .actions`).empty();
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
}


function sendAnswerNotification(){
	var nid = $(this).closest(".notification").attr("nid")
	var answer = "accept";
	
	if ($(this).hasClass("do-notif-deny")){
		answer = "deny";
	}

	data = {
		"op": "answer",
		"answer": answer,
		"nid": nid
	}

	$.ajax({
        url: "notifications.php",
        method: "POST",
        data: data,
        dataType: "json",
        success: function(data){
            if (data.result){
            	$(`#profile-container > .list .notification[nid=${nid}]`).remove();
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
}


// ЗАПРОС!
function getAllPrivateMessages(uid){
	$("#messenger .dialogue").empty();
	$("#msgr-input").val("");

	var data = {
		"op": "getall",
		"uid": uid
	}

    return $.ajax({
        url: "private-messages.php",
        method: "POST",
        data: data,
        dataType: "json",
        success: function(data){
            if (data.result){
                var lastMsgID = handlePrivateMessagesList(data.list)
				startMessagesListener($user.attr("uid"), lastMsgID)
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
    });
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


// Получить от сервера все рейтинги
function getAllRatings(callback){
    return $.ajax({
        url: "rating.php",
        method: "POST",
        data: "get=all",
        dataType: "json",
        success: function(data){
	        if (data.result){
	        	// Функция переданная на вход, как аргумент getAllRatings
	            callback(data);
	        }
	        else {
	            if ("msg" in data){
	            	showCountdownMessage(data.msg, 1000);
	            }
	            this.error();
	        }
        },
        error: function(){
            $("#do-rating").prop("disabled", false);
        }
    });
}
function drawRatings(data){
	$("#ratings .fields, #ratings .user-rating").empty();

	// [ratings] = array(
	//     array(1, "Никнейм", 150, 200) //Позиция, Никнейм, Выигранные игры, Всего игр
	// )
	$.each(data.ratings, function(index, value){
		// Шаблон данных
		var field = `
			<div class="field">
				<div class="position">${value[0]}</div>
				<div class="vline"></div>
				<div class="name">${value[1]}</div>
				<div class="vline"></div>
				<div class="count-won">${value[2]}</div>
				<div class="vline"></div>
				<div class="count-total">${value[3]}</div>
			</div>
		`;

		$("#ratings .fields").append(field);
	});

	var field = `
		<div class="position">${data["user_rating"][0]}</div>
		<div class="vline"></div>
		<div class="name">${data["user_rating"][1]}</div>
		<div class="vline"></div>
		<div class="count-won">${data["user_rating"][2]}</div>
		<div class="vline"></div>
		<div class="count-total">${data["user_rating"][3]}</div>
	`;
	$("#ratings .user-rating").html(field);
}

// [topic_id, name, icon_path]
function drawTopics(topics){
	$("#room-create-form .field.topics .value").empty();
	$("#room-create-msg").html("");

	$.each(topics, function(index, value){
		// Шаблон данных
		var field = `
			<div class="topic icob1" tid="${value[0]}">
				<img class="icon" src="${value[2]}">
				<div class="name">${value[1]}</div>
			</div>
		`;

		$("#room-create-form .field.topics .value").append(field);
	});

	$("#room-create-form .field.topics .topic").click(selectTopicHandler);
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
function sendCreateRoom(data, callback){
	data = JSON.stringify(data);

	return $.ajax({
        url: "rooms.php",
        method: "POST",
        data: `op=create&data=${data}`,
        dataType: "json",
        success: function(data){
	        if (data.result){
	        	// Функция переданная на вход, как аргумент getAllRatings
	            callback();
	        }
	        else {
	            if ("msg" in data){
					$("#room-create-msg").html(data.msg);
	            }
	            this.error();
	        }
        },
        error: function(){
	        $("#do-create-room").prop("disabled", false);
        }
    });

	        	
	            
}


function startGeneralListener(){
	// sseMenuConnection = new EventSource("daemon-general-listen.php");
	sseMenuConnection = new EventSource("time.php?test=15");
	sseMenuConnection.onmessage = function(event) {
		data = JSON.parse(event.data);
		if ("dt" in data){
			$("#profile-container").html("Время сервера: <br>" + data.dt);
		}
		else {
			if ("msg" in data){
				showWarning(data.msg);
			}
		}
	};
}
function stopGeneralListener(){
	if (sseMenuConnection !== null && sseMenuConnection !== undefined){
		sseMenuConnection.close();
	}
}



function startMessagesListener(uid, lastMsgID){
	sseMsgListenConnection = new EventSource(`daemon-msg-listen.php?uid=${uid}&lastid=${lastMsgID}`);
	sseMsgListenConnection.onmessage = function(event) {
		var data = JSON.parse(event.data);
		if (data.result){
			handlePrivateMessagesList(data.list);
		}
	};
}
function stopMessagesListener(){
	if (sseMsgListenConnection !== null && sseMsgListenConnection !== undefined){
		sseMsgListenConnection.close();
	}
}


//-------------------------------------------------------------------------------------------------------
// DOCUMENT READY EVENT
//-------------------------------------------------------------------------------------------------------

$(document).ready(function(){
	window.onbeforeunload = function(){
		stopGeneralListener();
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
				$("#profile-container > .list").empty();
				anime({
			        targets: "#profile-container",
			        left: "-22vw",
			        duration: adur(500),
			        easing: ease
			    });
				toogleMessenger(false);

				leftDummies(false, function(){
					$("#self-user").css({
						zIndex: 1
					});
					$("#self-user .left").removeClass("disabled");
				});
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

				leftDummies(true, function(){
					$("#self-user .left").removeClass("disabled");
				});
			}
			flags.inSelfUser = !flags.inSelfUser;
        }
	});


    onEnter($("#query-input"), function(){
        $("#do-search-users").click();
        $(this).focus();
    })

	$("#do-search-users").click(function(){
		var data = {
			"op": "getbyquery",
			"query": $("#query-input").val()
		};
		// var query = encodeURI($("#query-input").val());
	 	$.ajax({
	        url: "users.php",
	        method: "POST",
	        data: data,
	        dataType: "json",
	        success: function(data){
				$("#profile-container > .list").empty();

	            if (data.result){
					$.each(data.list, function(key, value){
						$("#profile-container > .list").append(
							getUserFromData(key, value)
						);
						if (value["friend"] == 0){
							$(`<img class="do-send-addfriend action icob1" src="assets/img/add-friend-ico.svg">`).appendTo(
								`#profile-container > .list .user.friend[uid=${key}] .actions`
							).click(sendAddFriend);
						}
					});
	            }
	            else {
	                if ("msg" in data){
	                    $("#profile-container > .list").html(`<span class='msg'>${data.msg}</span>`);
	                }
	                this.error();
	            }
	        },
	        error: function(){
	        }
	    });
	});

	$("#get-friends-list").click(function(){
		$("#profile-container > .list").empty();
		getAllFriends(handleFriendsList);
		toogleMessenger(false);
	});

	$("#get-notif-list").click(function(){
		$("#profile-container > .list").empty();
		getAllNotifications(handleNotificationsList);
		toogleMessenger(false);
	});


	onEnter($("#msgr-input"), function(){
        $("#do-send-msg").click();
        $(this).focus();
    });

	$("#do-send-msg").click(function(){
		var rdata = {
			"op": "set",
			"content": $("#msgr-input").val(), 
			"uid": $(".user.friend.active").attr("uid")
		};

		$.ajax({
	        url: "private-messages.php",
	        method: "POST",
	        data: rdata,
	        dataType: "json",
	        success: function(data){
	            if (data.result){
	            	$("#msgr-input").val("");
	            	$("#messenger .dialogue").append(

						getPrivateMessageFromData({
							"private_message_id": data.private_message_id,
							"sender_id": user["user_id"],
							"content": rdata["content"]
						})
					);
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
	    });
	});


	$("#room-list .list-refresh").click(function(){
        if (!$(this).is(':disabled')){
        	$(this).prop("disabled", true);
			$("#room-list .room").remove();
			closeRoomInfoBlock();
			currentRoomID = -1;

			// UPD ROOMS HERE
			// MAXIMUM 40 ROOMS
			getRoomsList(handleRoomsList);
        }
	})


	$(" #q-edit, #do-quick-game").click(function(){
        if (!$(this).is(':disabled')){
            showModal($("#temp-unav"));
        	// if (flags.inRating){
        	// 	leftDummies(false);
        	// }
        	// else {
        	// 	leftDummies(true, function(){
         //            $("#temp-unav").addClass("active");
         //        });
        	// }
			// flags.inRating = !flags.inRating;
        }
	});

	$("#room-create").click(function(){
		if (!$(this).is(':disabled')){
            $(this).prop("disabled", true);

            getAllTopics(function(data){
            	// Отрисует данные в модальное окно
        		drawTopics(data);
        		showModal($("#room-create-form"), function(){
	            	$("#room-create").prop("disabled", false);
	            });
        	})
            
        }
	});
	$("#room-create-form .field.privacy .value").change(function(){
		var $fieldPassword = $("#room-create-form .field.password");
		if ($(this).val() == 1){
			$fieldPassword.addClass("active");
			return;
		}
		$fieldPassword.removeClass("active");
		scrollToBottom($("#room-create-form .fields"));
	});
	$("#room-create-form .close").click(function(){
		$("#do-create-room").prop("disabled", false);
		$("#room-create-form .field.topics .value").empty();
		$("#room-create-msg").html("");
	});
	$("#room-create-form .field.password .postfix").click(function(){
		var $passwordValue = $("#room-create-form .field.password .value");
		if ($(this).hasClass("showed")){
			$passwordValue.attr("type", "password");
			$(this).removeClass("showed");
		}
		else {
			$passwordValue.attr("type", "text");
			$(this).addClass("showed");
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
				topics: []
	        }

	        $("#room-create-form .field.topics .topic").each(function(){	
	        	if ($(this).hasClass("selected")){
	        		data.topics.push($(this).attr("tid"));
	        	}
	        });

	        try {
	            if (!data.name){
	                throw new Error("Название");
	            }
	            else if (!data.step_time){
	                throw new Error("Время ответа");
	            }
	            else if (!data.topics.length){
	                throw new Error("Темы вопросов");
	            }
	            else if (!data.step_time){
	                throw new Error("Время ответа");
	            }
	            else if ($fieldPassword.hasClass("active") && !data.password){
	                throw new Error("Пароль");
	            }
	            else if (data.password.length < 4){
		            $("#room-create-msg").html("Пароль обязан содержать не менее 4-х символов");
		            $(this).prop("disabled", false);
	            }
	        }
	        catch (error){
	            $("#room-create-msg").html(`Заполните поле "${error.message}"`);
	            $(this).prop("disabled", false);
	            return;
	        }

	        sendCreateRoom(data, function(){
	        	$("#room-create-form .close").click();
	        	startRoomAnim();
	        });
		}
	});


	$("#do-rating").click(function(){
		if (!$(this).is(':disabled')){
            $(this).prop("disabled", true);

        	getAllRatings(function(data){
            	// Отрисует данные в модальное окно
        		drawRatings(data);
        		showModal($("#ratings"), function() {
            		$("#do-rating").prop("disabled", false);
        		});
        	})
        }
	});
	$("#ratings .modal-close").click(function(){
		$("#ratings .fields, #ratings .user-rating").empty();
	});


	$(".do-settings").click(function(){
        if (!$(this).is(':disabled')){
            $(this).prop("disabled", true);
        	getAllSettings(function(data){
            	$(".do-settings").prop("disabled", false);
        		drawSettings(data);
        		showModal($("#settings"));
        	})
			// flags.inSettings = !flags.inSettings;
        }
	});
	$("#settings .modal-close").click(function(){
		$("#settings .fields").empty();
		$("#apply-settings-msg").html("");
	});
	$("#do-apply-settings").click(function(){
		$("#apply-settings-msg").html("");
		var data = {};
		var value = "";
		$("#settings .field input").each(function(){
			value = "";
			switch ($(this).attr("type")){
				case "checkbox":
					value = "0";
					if ($(this).prop("checked")){
						value = "1";
					}
					break;
				case "number":
				default:
					value = $(this).val();
					break;

			}

			data[$(this).attr("name")] = value;
		});
		data = JSON.stringify(data);

		$.ajax({
	        url: "settings.php",
	        method: "POST",
	        data: `get=all&set=${data}`,
	        dataType: "json",
	        success: function(data){
	            if (data.result){
					$("#apply-settings-msg").html("Настройки сохранены");
					if ("settings" in data){
						applySettings(data.settings);
					}
	            }
	            else {
	                if ("msg" in data){
						$("#apply-settings-msg").html(data.msg);
	                }
	                this.error();
	            }
	        },
	        error: function(){
	        }
	    });
	});

	$(".do-logout").click(function(){
        if (!$(this).is(':disabled')){
        	$(this).prop("disabled", true);
            $("#auth-fail-msg").html("");
    		$.get({
				url: "logout.php",
				success: function(data){
					if (data.result){
						startLogoutAnim();
					}
				},
				dataType: "json"
			});
        }
	});

    $("#messenger .close").click(function(){
        toogleMessenger(false);
    });
});