let sseGameListenConnection = null;
let currentMap 	 = {};
let users 		 = [];
let canvas 		 = null;
let modalLoading = true;
let endgame 	 = false;
let colors 		 = ["#8bcbef", "#ef8b8b", "#8eef8b", "#f1ec7b"];
flags.myturn 	 = false;
flags.giveUp	 = false;
flags.moveBusy 	 = false;


$(window).on("beforeunload", function() {
	if (sseGameListenConnection){
		sseGameListenConnection.close();
	}
});


// CANVAS
function canvasResize(){
	canvas[0].width		= $("#canvas-parent").width();
	canvas[0].height 	= $("#canvas-parent").height();
	canvas.drawLayers();
}
function updateBgSize(){
	// currentMap.background.sizeY = 360*bg.size/bg.line;
	// currentMap.background.sizeX = 360*bg.line;
	currentMap.background.offset.x = canvas.width()/2  - currentMap.background.sizeX/2;
	currentMap.background.offset.y = canvas.height()/2 - currentMap.background.sizeY/2;
}
function drawMap(){
	let bg = currentMap.background;
	bg.sizeY = 360*bg.size/bg.line;
	bg.sizeX = 360*bg.line;
	bg.offset = {
		x: canvas.width()/2  - bg.sizeX/2,
		y: canvas.height()/2 - bg.sizeY/2
	}
	if (bg.chunks){
		let counter = 1;
		for (let i = 0; i < bg.size/bg.line; i++) {
			for (let j = 0; j < bg.line; j++) {
				canvas.drawImage({
					layer: true,
					fromCenter: false,
					draggable: true,
					name: `bg-c${counter}`,
					groups: ['bg-chunks'],
					dragGroups: ['bg-full', 'bg-chunks', 'nodes', 'players'],
					source: `${currentRoom.data.sprites_path}bg-c${counter}.png`,
					x: bg.offset.x + (360*j),
					y: bg.offset.y + (360*i)
				});
				counter++;
			}
			// counter += i*bg.line;
		}
	}
	else {
		canvas.drawImage({
			layer: true,
			draggable: true,
			fromCenter: false,
			name: "bg-full",
			dragGroups: ['bg-full', 'bg-chunks', 'nodes', 'players'],
			source: `${currentRoom.data.sprites_path}bg-full.png`,
			x: bg.offset.x,
			y: bg.offset.y
		});
	}

	let nodeSpritePath = null;
	$.each(currentMap.nodes, function(key, value){
		nodeSpritePath = `${currentRoom.data.sprites_path}${nodeTypes[value.type][1]}`; 
		canvas.drawImage({
			layer: true,
			draggable: true,
			name: `node-${key}`,
			groups: ['nodes'],
			dragGroups: ['bg-full','bg-chunks','nodes', 'players'],
			source: nodeSpritePath,
			x: bg.offset.x + value.x,
			y: bg.offset.y + value.y
		});
	});
	
	canvas.drawLayers();
}
function drawPlayers(list){
	list.sort(function(a,b){return a.uid-b.uid;});
	let node = null;
	let nodeOffset = [[30, 30], [-30, -30], [-30, 30], [30, -30]];
	// users = list;
	$.each(list, function(key, value){
		node = currentMap.nodes[value.position];
		users[value.uid] = {
			"color": 	value.color,
			"offset": nodeOffset[key]
		}
		// users[key]["offset"] = nodeOffset[key];
		canvas.drawArc({
			layer: true,
			draggable: true,
			fillStyle: colors[value.color-1],
			name: `player-${value.uid}`,
			groups: ['players'],
			dragGroups: ['bg-full','bg-chunks','nodes', 'players'],
			x: currentMap.background.offset.x + node.x + nodeOffset[key][0], 
			y: currentMap.background.offset.y + node.y + nodeOffset[key][1], 
			radius: 20
		});
	});
}
async function movePlayer(movement){
	// list.sort(function(a,b){return a.uid-b.uid;});
	$("#canvas-parent").addClass("locked");
	let node = null;
	$.each(movement.path, async function(key, value){
		node = $('canvas').getLayer(`node-${value}`);
		$('canvas').animateLayer(`player-${user.user_id}`, {
			x: node.x + users[user.user_id]["offset"][0], 
			y: node.y + users[user.user_id]["offset"][1]
		}, 500);
		await sleep(500);
	});
	$("#canvas-parent").removeClass("locked");
}
function pmovePlayer(movement){
	// list.sort(function(a,b){return a.uid-b.uid;});
	// let node = currentMap.nodes[movement.position];
	$("#canvas-parent").addClass("locked");
	let node = $('canvas').getLayer(`node-${movement.position}`);
	$('canvas').animateLayer(`player-${movement.uid}`, {
		x: node.x + users[movement.uid]["offset"][0], 
		y: node.y + users[movement.uid]["offset"][1]
	}, 500*movement.dice, function(){
		$("#canvas-parent").removeClass("locked");
	});
}


// START
function dropGame(msg){
	if (sseGameListenConnection){
		sseGameListenConnection.close();
	}

	generalModal.set({
		data: msg
	}).loadingState(false).always(function(){
		sendExitRoom()
			.then(function(){
				location.reload();
			});
	});
}
function prepareGame(){
	// Не ждем анимацию
	dummies.current = dummies.top;
	$(".dummies.top > .dummy").css({
		top: "100vh"
	})
	generalModal.loading({
		title: "Загрузка",
		data: "Получение пользовательских настроек",
		cancel: "Покинуть игру" 
	})
	generalModal.element.find(".apply").addClass("hide");
	
	getAllSettings().then(function(data){
		user.settings_data = data.settings_data;
		applySettings(user.settings_data);
	}).always(prepareRoom);
}
function prepareRoom(){
	generalModal.set({data: "Получение данных комнаты"});
	getRoomData(currentRoom.id, -1).then(
        function(responsed){
            currentRoom.setup(responsed);
			// $("#canvas-parent .title").html(`${currentRoom.data.name} #${currentRoom.id}`);
			$("#canvas-parent .title").html(`${currentRoom.data.name}`);
			$(".room-users .title .value").html(`${currentRoom.users_list.length}/4`);
			// $(".step-time .time .value").html(`${currentRoom.data.step_time}`);
			handleGameTopics(currentRoom.topics_list);
			handleGameUsers(currentRoom.users_list);
			
			console.log("getRoomData [Готово]");
			getUsersOrder();
        },
        function(){
			dropGame("Ошибка получения данных комнаты");
        }
    );
}
function getUsersOrder(){
	generalModal.set({data: "Получение очередности хода"});
	apiRequest({
		route: 	 "room",
		op: 	 "get_order",
		room_id: currentRoom.id
	},{
		weight: -1
	}).then(
		function(data){
			handleUsersOrder(data.order_list)

			console.log("getUsersOrder [Готово]");
			getMapScheme();
		}, 
		function(data){
			dropGame("Ошибка получения очередности хода");
		}
	);
}
function getMapScheme(){
	generalModal.set({data: "Получение схемы карты"});
	apiRequest({
		route: 	"map",
		op: 	"get_scheme",
		map_id:	currentRoom.data.map_id
	},{
		weight: -1
	}).then(
		function(data){
			currentMap 	= JSON.parse(data.scheme);
			nodeTypes 	= data.types_list;

			console.log("getMapScheme [Готово]");
			renderMapScheme();
		}, 
		function(data){
			dropGame("Ошибка получения данных карты");
		}
	);
}
function renderMapScheme(){
	generalModal.set({
		title: 	"Подготовка",
		data: 	"Отрисовка карты",
	});
	handleMapColorTheme();
	drawMap();
	console.log("renderMapScheme [Готово]");
	getPlayersPositions();
}
function getPlayersPositions(){
	generalModal.set({data: "Получение позиций игроков"});
	apiRequest({
		route: 	"map",
		op: 	"get_positions",
		room_id: currentRoom.id
	},{
		weight: -1
	}).then(
		function(data){
			drawPlayers(data.pos_list);

			console.log("getPlayersPositions [Готово]");
			sendUserReady();
		}, 
		function(data){
			dropGame("Ошибка получения позиций игроков");
		}
	);
}
function sendUserReady(){
	generalModal.set({
		title: 	"Ожидание",
		data: 	"Ожидание игроков",
	});

	apiRequest({
		route: 	"room",
		op: 	"set_game_ready"
	},{
		weight: -1
	}).then(
		function(){
			console.log("sendUserReady [Готово]");
			// gameStart();
			startGameListener();
		}, 
		function(data){
			dropGame("Ошибка отправки состояния");
		}
	);
}
function gameStart(){
	// sleep(adur(500)).then(function(){
	// 	generalModal
	// 		.apply(function(){	
	// 		})
	// 		.element.find(".apply").removeClass("hide");
	// });
	generalModal.element.find(".apply").removeClass("hide");
	if (currentModal){
		if (currentModal.is("#general-modal")){
			hideModal();
		}
	}	

	apiRequest({
		route: 	 "room",
		op: 	 "start_game",
		room_id: currentRoom.id
	},{
		weight: -1
	}).then(
		function(){
			console.log("gameStart [Готово]");
		}, 
		function(data){
			dropGame("Ошибка начала игры");
		}
	);
}
function endLoading(){
	modalLoading = false;
	generalModal.element.find(".apply").removeClass("hide");
	if (currentModal){
		if (currentModal.is("#general-modal")){
			hideModal();
		}
	}	
	if (endgame){
		return hideBanner();
	}
	if (!flags.myturn){
		showBanner("Игра началась!");
	}
}


// TOOLS
function showBanner(textValue){
	var banner = $("#banner");
	banner.find(".value").html(textValue);
	if (banner.hasClass("active")){
		return;
	}

	banner
		.css({
			zIndex: ""
		})
		.addClass("active");
	if (flags.allowAnimations){
		sleep(1600).then(function(){
			banner.removeClass("active");
			sleep(1000).then(function(){
				banner.css({
					zIndex: "-1"
				})
			})
		})
	}
	else {
		// 1600 + 1000 = 2300?
		sleep(2300).then(hideBanner)
	}
}
function hideBanner(){
	$("#banner")
		.removeClass("active")
		.css({
			zIndex: "-1"
		})
}
function isCreator(){
	return user.user_id == currentRoom.creator_id;
}
function shuffle(array){
	array.sort(() => Math.random() - 0.5);
}



// HANDLERS
function handleMapColorTheme() {
	if ("theme" in currentMap){
		let gameColorStyle = "";
		$.each(currentMap.theme, function(key, value) {
			gameColorStyle += `${key}: ${value};`
		});
		gameColorStyle += $("body").attr("style");
		$("body").attr("style", gameColorStyle);
	}
}
function getTopicFromData(value){
	return `
		<div class="topic" tid="${value.topic_id}" title="${value.name}">
			<img class="icon" src="${value.icon_path}">
		</div>
	`;
}
function handleGameTopics(list){
	let topicsElement = $(".side-topics > .value.topics");
	topicsElement.empty();
	$.each(list, function(key, value){
		topicsElement.append(getTopicFromData(value));
	});
}
function getUserFromData(value){
	var fkey = getFormattedID(value.user_id);
	return  `
		<div class="user" uid="${value.user_id}" title="${value.nick}">
			<div class="left">
				<img class="u-avatar" src="${value.avatar_path}">
				<div class="substrate"></div>
			</div>
			<div class="info right">
				<span class="u-nick">
					${value.nick}
				</span>
				<span class="u-id">
					${fkey}
				</span>
			</div>
		</div>
	`;
}
function handleGameUsers(list){
	let usersElement = $(".room-users .users-list");
	usersElement.empty();
	$.each(list, function(key, value){
		usersElement.append(getUserFromData(value));
	});
}
function handleUsersOrder(list){
	let orderElement = $(".step-order .value");
	orderElement.empty();
	$.each(list, function(key, value){
		// .attr("color", value.color)
		$(`.user[uid="${value.uid}"]`)
			.css({
				order: value.order_value,
			})
			.find(".substrate")
			.css({
				backgroundColor: colors[value.color-1]
			})

		// color=${value.color} 
		orderElement.append(`
			<div class="circle" uid=${value.uid}
				style="background-color: ${colors[value.color-1]};">
			</div>
		`);
	});
}
function killUsers(list){
	let targetElements = $(`.user, .circle`);
	$.each(list, function(key, value){
		targetElements = targetElements.not(`[uid=${value.uid}]`);
	});
	targetElements.remove();
}
function handleOrder(uid){
	$(".room-users .user").removeClass("step");
	$(`.room-users .user[uid="${uid}"]`).addClass("step");
	
	if (flags.myturn && (uid != user.user_id)){
		hideModal();
	}

	// Мой ход
	flags.myturn = (uid == user.user_id);
	if (flags.myturn){
		$(".step-info.right").removeClass("active");
		return flags.myturn;
	}
	$(".step-info.right").addClass("active");
	return flags.myturn;
}
function handlePing(ping){
	$(".step-time .time .value").html(currentRoom.data.step_time - ping);
}
function handleTimeout(ping){
	handlePing(ping);
	if (flags.myturn){
		flags.myturn = false;
		hideModal();
		showBanner("Время вышло!");
	}
	else {
		showCountdownMessage("Время вышло!", 2000);
	}
}
function handleQuestion(question){
	let qElement = $("#question");
	qElement.attr("qid", question.qid);
	let topicSrc = $(`.topic[tid=${question.tid}] img`).attr("src");
	qElement.find(".topic")
		.attr("tid", question.tid)
		.find("img").attr("src", topicSrc);
	qElement.find(".question-value").html(question.value);
	// let ans = [[question.cans, 1], [question.ians1, 0], [question.ians2, 0], [question.ians3, 0]];
	// shuffle(ans);
	let ansElement = qElement.find(".fields.answers");
	ansElement.empty();
	question.answers.forEach(value => {
		if (!!value){
			ansElement.append(`
				<div class="field answer b1 grey">
                    <div class="value">${value}</div>
                </div>
			`);
		}
	});
	qElement.find(".answer.b1").click(function (){
		flags.giveUp = false;
		answerQuestion($(this).find(".value").html());
	});
	return qElement;
}
function answerQuestion(answer){
	apiRequest({
		route: 	 "question",
		op: 	 "answer",
		qid: 	 $("#question").attr("qid"),
		room_id: currentRoom.id,
		answer:	 answer,
		give_up: flags.giveUp
	},{
		weight: -1
	}).then(
		function(data){
			console.log("question.answer [ОК]");
			// console.log(`question.answer => ${data}`);
			hideModal();
			flags.myturn = false; //Костыль
			if (flags.giveUp){
				return showBanner("Сдался!");
			}
			if (data.answer){
				return showBanner("Верно!");
			}
			return showBanner("Неверный ответ!");
		}, 
		function(data){
			console.error("question.answer [BAD]");
			showCountdownWarning(data.msg, 2000);
			hideModal();
		}
	);
}
function handleAnswer(uid, result){
	hideModal();
	if (!result){
		// showCountdownMessage("Игрок ошибся!", 1000);
		return stepOrderPush();
	}
	// ЛОГИКА ПЕРЕМЕЩЕНИЙ
	if (uid == user.user_id){
		apiRequest({
			route: 	 "map",
			op: 	 "move",
			room_id: currentRoom.id,
			map_id:	 currentRoom.data.map_id
		},{
			weight: -1
		}).then(
			function(data){
				console.log("map.move [ОК]");
				movePlayer(data.movement);
				// console.log(data.movement.path);
				// console.log(data.movement.dice);
			}, 
			function(data){
				console.error("map.move [BAD]");
				showCountdownError(data.msg, 2000);
				// console.error(data.msg);
			}
		);
	}
}
function handleMove(movement){
	flags.moveBusy = true;
	pmovePlayer(movement);
	sleep(1000 + (500*movement.dice)).then(function(){
		flags.moveBusy = false;
		stepOrderPush();
	});
	// if (isCreator()){
	// }
}
function stepOrderPush(){
	if (isCreator()){
		apiRequest({
			route: 	 "room",
			op: 	 "push_order",
			room_id: currentRoom.id
		},{
			weight: -1
		}).then(
			function(){
				console.log("push_order [ОК]");
			}, 
			function(){
				console.error("push_order [BAD]");
			}
		);
	}
}
function stepQueryQuestion(){
	apiRequest({
		route: 	 "question",
		op: 	 "get_unique",
		room_id: currentRoom.id
	},{
		weight: -1
	}).then(
		function(data){
			console.log("question.get_unique [ОК]");
			handleQuestion(data.question);
			swapModal($("#question"));
		}, 
		function(data){
			console.error("question.get_unique [BAD]");
			showCountdownError(data.msg, 2000);
		}
	);
}
function handleFinish(data){
	let endgameModal = $("#endgame");
	if ("winner" in data){
		endgameModal.find(".avatar > .value").attr("src", data.winner.avatar);
		endgameModal.find(".u-nick").html(data.winner.nick);
		endgameModal.find(".u-id").html(getFormattedID(data.winner.uid));
		if (user.user_id == data.winner.uid){
			endgameModal.find(".about").html(`
				Поздравляем!<br>
				Вы победили в текущей игре
			`);
			$("#do-end-game .value").html("Спасибо!");
		}
	}
	$("#do-end-game").click(function(){
		sendExitRoom()
			.then(
				function(){
					location.reload();
				},
				function(){
					location.reload();
				},
			)
	});
	$(".step-info.right")
		.removeClass("active")
		.css({"zIndex": 1})
		.find(".time")
		.empty();
	$(".step-order.side-info").remove();
	swapModal(endgameModal);
}



function syncRoomHandler(data){
	switch(data.code) {
		case 0: // Ошибка со стороны php
			console.log("SYNC_GENERAL_ERROR");
			dropGame(`Мы сломались! value="${data.msg}"`);
			if (sseGameListenConnection){
				sseGameListenConnection.close();
			}
			break;
		case 2: // Комната утеряна
			console.log("SYNC_ROOM_DEAD");
			if (sseGameListenConnection){
				sseGameListenConnection.close();
			}
			if (!endgame){
				let modalMessage = "Комната расформирована";
				if (isCreator()){
					modalMessage = "Недостаточно игроков для продолжения игры";
				}
				generalModal.alert({
					data: modalMessage
				}).always(function(){
					if (isCreator()){
						location.reload();
					}
					else {
						sendExitRoom().always(function(){
							location.reload();
						})
					}
				})
				// swapModal();
			}
			break;
		case 3: // Игроки не готовы
			// PLAYERS WAIT
			console.log("SYNC_ROOM_PLAYERS_NREADY");
			break;
		case 4: // Игроки готовы, чел, начни уже игру
			console.log("SYNC_ROOM_PLAYERS_READY");
			if (isCreator()){
				gameStart();
			}
			break;
		case 5: // Проверка выхода игроков
			console.log("SYNC_ROOM_PLAYER_LIST");
			killUsers(data.ulist);
			break;
		case 6: // SYNC_ROOM_OK
			console.log("SYNC_ROOM_OK");
			if (modalLoading){
				endLoading();
			}
			break;
		case 7: // SYNC_ROOM_FINISH
			endgame = true;
			console.log("SYNC_ROOM_FINISH");
			if (modalLoading){
				endLoading();
			}
			if (sseGameListenConnection){
				sseGameListenConnection.close();
			}
			handleFinish(data);
			break;
		default: 
			break;
	}
}
function syncStepHandler(data){
	switch(data.code) {
		case 2: // Ping
			handlePing(data.ping);
			break;
		case 3: // Timeout
			handleTimeout(data.ping);
			console.log("SYNC_STEP_TIMEOUT");
			sleep(1000).then(stepOrderPush);
			break;
		case 4+1: // SSTAGE_START
			// handleQuestion(data.uid);
			console.log("SSTAGE_START");
			sleep(2500).then(stepOrderPush);
			break;
		case 4+2: // SSTAGE_ORDER
			if (handleOrder(data.uid)){
				stepQueryQuestion();
			}
			console.log("SSTAGE_ORDER");
			break;
		case 4+3: // SSTAGE_ANSWERING
			handleOrder(data.uid);
			console.log("SSTAGE_ANSWERING");
			break;
		case 4+4: // SSTAGE_YES
			handleAnswer(data.uid, true);
			console.log("SSTAGE_YES");
			break;
		case 4+5: // SSTAGE_NO
			handleAnswer(data.uid, false);
			console.log("SSTAGE_NO");
			break;
		// case 4+6: // SSTAGE_MOVING
		// 	console.log("SSTAGE_MOVING");
		// 	break;
		case 4+7: // SSTAGE_MOVE_END
			console.log("SSTAGE_MOVE_END");
			break;
		case 4+8: // SSTAGE_QUESTION_OVERFLOW
			console.error("SSTAGE_QUESTION_OVERFLOW");
			showCountdownError("SSTAGE_QUESTION_OVERFLOW", 2500);
			break;
		default: 
			break;
	}
}
function syncPositionHandler(data){
	switch(data.code) {
		case 2: // SYNC_POS_MOVE
			handleMove(data.movement);
			console.log("SYNC_POS_MOVE");
			console.log("uid:" + data.movement.uid);
			console.log("position:" + data.movement.position);
			console.log("dice:" + data.movement.dice);
			showCountdownMessage(`Выпало: <b>${data.movement.dice}</b>`, 1500);
			break;
		case 3: // SYNC_POS_DATA
			$.each(data.movements, function (key, value) { 
				pmovePlayer(value);
			});
			console.log("SYNC_POS_DATA");
			break;
		default: 
			break;
	}
}
function sseResponseHandler(value){
	if ("room" in value){
		syncRoomHandler(value.room);
		if ((value.room.code >= 5) && ("step" in value)){
			syncStepHandler(value.step);
		}
		if ("position" in value){
			syncPositionHandler(value.position);
		}
	}
}


//SSE 
function startGameListener(){
	console.log(`[START] слушатель игры [room #${currentRoom.id}]`);
	sseGameListenConnection = new EventSource(`api/daemon/game-listen.php?room_id=${currentRoom.id}`);
	// Сервер присылает сообщение только при изменениях
	sseGameListenConnection.onmessage = function(event) {
		// if (event.data.length < 5){
		// 	return;
		// }
		var data = JSON.parse(event.data);
		sseResponseHandler(data);
	}
}
function stopRoomListener(){
	if (sseGameListenConnection !== null && sseGameListenConnection !== undefined){
		console.log("[END] слушатель игры");
		sseGameListenConnection.close();
		sseGameListenConnection = null;
	}
}


//-------------------------------------------------------------------------------------------------------
// DOCUMENT READY EVENT
//-------------------------------------------------------------------------------------------------------

$(document).ready(function(){
	canvas = $("canvas");
	$(window).resize(canvasResize);
	canvasResize();


    $("#do-game-exit").click(function(){
		if (!$(this).attr('disabled')){
			$(this).attr("disabled", true);
			let confirmMessage = "Вы уверены что хотите покинуть игру?";
			if (isCreator()){
				confirmMessage += "<br>Игра будет расформирована";
			}
			generalModal.confirm({
				data: confirmMessage,
				apply: "Покинуть игру",
				close: "Отмена"
			}).then(
				function(){
					if (sseGameListenConnection){
						sseGameListenConnection.close();
					}
					sendExitRoom()
						.then(function(){
							location.reload();
						})
				}
			).always(function(){
                $("#do-game-exit").attr("disabled", false);
			});
        }
	});

	$("#question .give-up").click(function(){
		flags.giveUp = true;
		answerQuestion(null);
	})

	$(".room-users .fold").click(function(){
		$(".room-users").toggleClass("full");
		$(this).toggleClass("full");
	});

	$(".scale .scale-p").click(function(){
		canvas.scaleCanvas({
			scale: 1.25
		}).drawLayers();
		updateBgSize();
	});
	$(".scale .scale-n").click(function(){
		canvas.scaleCanvas({
			scale: 0.8
		}).drawLayers();
		updateBgSize();
	});

	prepareGame();
});