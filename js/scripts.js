var url;
var loadCheck = false;
var i = 0;

function init(path) {
    url = path;
}

$(document).ready(function(){
	if ($("#arfiles").width()) {
		$("#arfiles").prepend('<div id="fa-uploader"></div>');
		createUploaderFA();
	}
});

function checkMail() {
	$("#checkingMail").dialog({ modal: true, width: 580, height: 400 });	

    var data = "action=getMailboxes";
    $.ajax({
    	type: "POST",
    	url: url + "ajax/mail/",
    	data: data,
    	dataType: 'json',
    	success: function(res) {
    		$("#checkingMail").everyTime(500, "timer", function() {
    			if (window.i < (res.length)) {

    				if (window.loadCheck == false) {
    					window.loadCheck = true;

    					$("#ajaxCheckMail").remove();
    					
    					$("#checkingMail").append("<span style='float: left'>Проверяется: " + res[window.i] + "</span>");
    					$("#checkingMail").append('<p id="ajaxCheckMail" style="text-align: center"><img src="' + url + 'img/ajaxCheckMail.gif" alt="ajax-loader.gif" border="0" /></p>');
    					
    					var objDiv = document.getElementById("checkingMail");
    					objDiv.scrollTop = objDiv.scrollHeight;
    					
    					checkMbox(res[window.i]);
    				}
    			} else {
    				$("#checkingMail").stopTime("timer");
    				
    				$("#checkingMail").oneTime(1000, function() {
    					$("#ajaxCheckMail").hide();
    					$("#checkingMail").append("<p style='font-weight: bold; margin-top: 10px'>Завершение...</p>");

    					document.location.href = document.location.href;
    				});
    			}
    		});
        }
    });

};

function checkMbox(mbox) {
    var data = "action=checkMboxes&mbox=" + mbox;
    var request = $.ajax({
    	type: "POST",
    	url: url + "ajax/mail/",
    	data: data
    });
    
    request.done(function(msg) {
    	$("#ajaxCheckMail").hide();
    	
    	if (msg == "false") {
    		$("#checkingMail").append("<span style='float: right; color: red; font-weight: bold'>Ошибка</span><br />");
    		$("#checkingMail").append("<p style='margin-left: 20px; font-size: 11px'>Не удаётся подключиться: " + mbox + "</p>");
    	} else if (msg == "true") {
    		$("#checkingMail").append("<span style='float: right; color: green; font-weight: bold'>OK</span><br />");
    	} else {
    		$("#checkingMail").append("<span style='float: right; color: red; font-weight: bold'>Ошибка</span><br />");
    		$("#checkingMail").append("<div style='margin: 10px; font-size: 10px; overflow: hidden'>" + msg + "</div>");
    	}
    	
    	var objDiv = document.getElementById("checkingMail");
		objDiv.scrollTop = objDiv.scrollHeight;
    	
    	window.loadCheck = false;
		window.i++;
    });
    
    request.fail(function(jqXHR, textStatus) {
    	$("#checkingMail").append("<span style='float: right; color: red; font-weight: bold'>Ошибка</span><br />");
		$("#checkingMail").append("<p style='margin-left: 20px; font-size: 11px'>Не удаётся подключиться: " + mbox + "</p>");
    	
    	var objDiv = document.getElementById("checkingMail");
		objDiv.scrollTop = objDiv.scrollHeight;
		
    	window.loadCheck = false;
		window.i++;
	});
};

function gourl(go) {
	document.location.href = url + go;
}

function htmlarea() {
    $("#jHtmlArea").htmlarea({
        toolbar: [
            ["bold", "italic", "underline", "|", "forecolor"],
            ["p", "h1", "h2", "h3", "h4", "h5", "h6"],
            ["link", "unlink", "|", "image"]
            ]});
}

function clearHtmlArea() {
    $("#jHtmlArea").text("");
    $("#jHtmlArea").htmlarea("dispose");
}

function delSortConfirm(sid) {
	$('<div title="Удаление правила сортировки">Удалить?</div>').dialog({
		modal: true,
	    buttons: {
			"Нет": function() { $(this).dialog("close"); },
			"Да": function() { delSort(sid); $(this).dialog("close"); }
		},
		width: 280
	});
}

function delSort(sid) {
    var data = "action=delSort&sid=" + sid;
    $.ajax({
            type: "POST",
            url: url + "ajax/mail/",
            data: data,
            success: function(res) {
    			document.location.href = document.location.href;
            }
    });
}

function delMailDirConfirm(fid) {
	$('<div title="Удаление папки">Удалить?</div>').dialog({
		modal: true,
	    buttons: {
			"Нет": function() { $(this).dialog("close"); },
			"Да": function() { delMailDir(fid); $(this).dialog("close"); }
		},
		width: 280
	});
}

function delMailDir(fid) {
    var data = "action=delMailDir&fid=" + fid;
    $.ajax({
            type: "POST",
            url: url + "ajax/mail/",
            data: data,
            success: function(res) {
    			document.location.href = url + 'mail/';
            }
    });
}

function sendMailCommentConfirm(email, cid) {
	$('<div title="Отправка письма">Вы уверены, что хотите отправить письмо?</div>').dialog({
		modal: true,
	    buttons: {
			"Нет": function() { $(this).dialog("close"); },
			"Да": function() { sendMailComment(email, cid); $(this).dialog("close"); }
		},
		width: 280
	});
}

function sendMailComment(email, cid) {
    var data = "action=sendMailComment&email=" + email + "&cid=" + cid;
    $.ajax({
            type: "POST",
            url: url + "ajax/mail/",
            data: data,
            success: function(res) {
    			$("#smail" + cid).attr("src", url + "img/mail--exclamation.png");
    			$("#shref" + cid).attr("onclick", "");
    			$("#shref" + cid).css("cursor", "default");
    			$("#d" + cid).css("background-color", "#DFD");
            }
    });
}

function flushAttaches() {
	$("#attach_files").html('');
}

function delContactConfirm(email) {
	$('<div title="Удаление">Вы уверены, что хотите удалить контакт?</div>').dialog({
		modal: true,
	    buttons: {
			"Нет": function() { $(this).dialog("close"); },
			"Да": function() { delContact(email); $(this).dialog("close"); }
		},
		width: 280
	});
}

function delContact(email) {
	var data = "action=delContact&email=" + email;
    $.ajax({
            type: "POST",
            url: url + "ajax/mail/",
            data: data,
            success: function(res) {
    			document.location.href = document.location.href;
            }
    });
}

function getInfo(email) {
    var data = "action=getInfo&email=" + email;
    $.ajax({
            type: "POST",
            url: url + "ajax/contacts/",
            data: data,
            success: function(res) {
            	$('<div title="Контакт">' + res + '</div>').dialog({
            		modal: true,
            	    buttons: {
            			"Закрыть": function() { $(this).dialog("close"); }
            		},
            		width: 280
            	});
            }
    });
}

function createUploaderFA() {
	var uploader = new qqfa.FileUploader({
		element: document.getElementById('fa-uploader'),
		action: url + 'ajax/fa/',
		params: {
			action: 'save'
		},
        onComplete: function(id, fileName, responseJSON){
            $('#' + id + '').fadeOut('slow');

            addElementFA(parseInt($('#fa_lastIdRow').val()) + id + 1, fileName);
            
            $('#fa_empty').fadeOut('medium');
        }
	})
};

function addElementFA(id, fileName) {
    var file = "<input type='hidden' name='attaches[]' value='" + fileName + "' /><p><img border='0' src='" + url + "img/paper-clip-small.png' alt='attach' style='position: relative; top: 4px; left: 1px' />" + fileName + "</p>";

    $("#attach_files").append(file);
};

function sign(val) {
	var text = null;
	var sign = null;

	var data = "action=getSign&bid=" + val;
    $.ajax({
    	type: "POST",
    	url: url + "ajax/mail/",
    	data: data,
    	success: function(res) {
    		text = $("#jHtmlArea").htmlarea('toHtmlString');
    		var pos = text.indexOf('<div id="mailsign">');
    		text = text.substr(0, pos + 6);
    		text = text + '<br /><div id="mailsign">' + res + '</div>';
	
    		$("#jHtmlArea").htmlarea("dispose");
    	    $("#jHtmlArea").val(text);
    	    htmlarea();
        }
    });
};