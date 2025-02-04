'use strict'

/*
*   CallCenterHandler.js
*   Author: Hieu Nguyen
*   Date: 2018-10-05
*   Purpose: To handle events from Call Center
*/

jQuery(function($) {
	var socket = null;

    // Init socket client only if user is logged in
    if(!NotificationHelper.isAtLoginPage()) {
        // Check if socket.io is ready
        if(typeof io !== 'undefined') {
            var user = NotificationHelper.getUserInfo();

            // User with empty ext number is not valid
            if(user.ext_number == null || user.ext_number == '') {
                return false;
            }

            // Init notification when pushServerUrl is available
            if(_VOICE_COMMAND_PROXY_SERVER_URL != '') {
                initSocketClient(_VOICE_COMMAND_PROXY_SERVER_URL);
            }
        }
    }

    // Init the real-time client.
    function initSocketClient(serverUrl) {
		const ssl = serverUrl.indexOf('wss') >= 0;
		socket = io.connect(serverUrl, {path: '/socket.io', transports: ['websocket'], secure: ssl, reconnect: true, rejectUnauthorized: false});

		socket.on('connect', function(data) {
			socket.on('result', function(transcript) {
				if(transcript) {
					var command = transcript.toLowerCase();
			
					if (command.indexOf('tìm') >= 0) {
						var input = $('.search-link ').find('.keyword-input');
						input.focus().val(transcript.replace('tìm', ''));
						input.trigger($.Event('keypress', {which: 13, keyCode: 13}));
					}
			
					if (command.indexOf('tạo khách hàng') >= 0) {
						location.href = 'index.php?module=Accounts&view=Edit&accountname=' + transcript.replace('tạo khách hàng', '');
					}
			
					if (command.indexOf('tạo đơn hàng') >= 0) {
						location.href = 'index.php?module=SalesOrder&view=Edit&subject=' + transcript.replace('tạo đơn hàng', '');
					}
			
					if (command.indexOf('tạo hóa đơn') >= 0) {
						location.href = 'index.php?module=Invoice&view=Edit&subject=' + transcript.replace('tạo hóa đơn', '');
					}
			
					if (command.indexOf('tạo người liên hệ') >= 0) {
						location.href = 'index.php?module=Contacts&view=Edit&firstname=' + transcript.replace('tạo người liên hệ', '');
					}
			
					if (command.indexOf('xem lịch làm việc') >= 0) {
						location.href = 'index.php?module=Calendar&view=Calendar';
					}
			
					if (command.indexOf('xem báo cáo') >= 0) {
						location.href = 'index.php?module=Reports&view=List';
					}
			
					console.log("final results: " + transcript);   //Of course – here is the place to do useful things with the results.
				}
			});
			
			window.onbeforeunload = function() {
				if(streamStreaming) { 
					socket.emit('command', {command: 'stop_recognition'}); 
				}
			};
		});
	}
	
	$('#voice-command').click(function(e) {
		if($(this).data('recording') != 1) {
			$(this).data('recording', 1);
			$(this).css('color', 'red');
			startRecording();
		}
		else {
			$(this).data('recording', 0);
			$(this).css('color', 'black');
			stopRecording();
		}
		
        return false;
	});
	
	// Stream Audio config
	let bufferSize = 2048,
		AudioContext,
		context,
		processor,
		input,
		globalStream,
		streamStreaming = false;

	const constraints = {
		audio: true,
		video: false
	};

	// Recording
	function initRecording() {
		socket.emit('command', {command: 'start_recognition'}); 
		streamStreaming = true;
		AudioContext = window.AudioContext || window.webkitAudioContext;
		context = new AudioContext();
		processor = context.createScriptProcessor(bufferSize, 1, 1);
		processor.connect(context.destination);
		context.resume();

		var handleSuccess = function(stream) {
			globalStream = stream;
			input = context.createMediaStreamSource(stream);
			input.connect(processor);

			processor.onaudioprocess = function(e) {
				microphoneProcess(e);
			};
		};

		navigator.mediaDevices.getUserMedia(constraints)
			.then(handleSuccess);

	}

	function microphoneProcess(e) {
		var left = e.inputBuffer.getChannelData(0);
		var left16 = convertFloat32ToInt16(left);
		socket.emit('voice_data', left16);
	}

	function startRecording() {
		initRecording();
	}

	function stopRecording() {
		streamStreaming = false;
		socket.emit('command', {command: 'stop_recognition'}); 


		let track = globalStream.getTracks()[0];
		track.stop();

		input.disconnect(processor);
		processor.disconnect(context.destination);
		context.close().then(function() {
			input = null;
			processor = null;
			context = null;
			AudioContext = null;
		});

		// context.close();
		// audiovideostream.stop();

		// microphone_stream.disconnect(script_processor_node);
		// script_processor_node.disconnect(audioContext.destination);
		// microphone_stream = null;
		// script_processor_node = null;

		// audiovideostream.stop();
		// videoElement.srcObject = null;
	}

	// Util functions
	function convertFloat32ToInt16(buffer) {
		let l = buffer.length;
		let buf = new Int16Array(l / 3);

		while(l--) {
			if(l % 3 == 0) {
				buf[l / 3] = buffer[l] * 0xFFFF;
			}
		}
		
		return buf.buffer
	}
});