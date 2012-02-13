<h2>Правка почтового ящика</h2>

<form method="post" action="{{ registry.uri }}boxes/?email={{ post.email }}">

{% if err %}
<div style="width: 150px; padding: 6px 3px; margin: 10px 0; border: 1px solid red; background-color: #FDD">
Заполнены не все поля!
</div>
{% endif %}

<div style="margin-bottom: 20px">
	<div class="par"><b>Почтовый ящик</b></div>
	<div class="par"><input type="text" name="email" value="{{ post.email }}" /></div>
</div>

<div style="height: 50px; margin: 5px 0 20px 0">
	{% if not post.clear %}
	<div style="margin-bottom: 10px"><input type="checkbox" name="clear" id="clear" /><label for="clear">Оставлять письма на сервере</label></div>	
	{% else %}
	<div style="margin-bottom: 10px"><input type="checkbox" name="clear" id="clear" checked="checked" /><label for="clear">Оставлять письма на сервере</label></div>
	{% endif %}
	<div id="div_clear_days">Удалять письма через: <input name="clear_days" id="clear_days" type="text" style="width: 30px" value="{{ post.clear_days }}" /> дней</div>
</div>

<div style="overflow: hidden">
<div style="float: left; margin-right: 100px">
	<div class="par"><b>Входящая почта</b></div>
	
	<div class="par">Сервер</div>
	<div class="par"><input type="text" name="in_server" id="in_server" value="{{ post.in_server }}" /></div>
	<div class="par">Логин</div>
	<div class="par"><input type="text" name="in_login" id="in_login" value="{{ post.in_login }}" /></div>
	<div class="par">Пароль</div>
	<div class="par"><input type="password" name="in_password" id="in_password" value="{{ post.in_password }}" /></div>
	<div class="par">Протокол</div>
	<div class="par">
		<select name="in_protocol" id="in_protocol">
			<option {% if post.in_protocol == "POP3" %}selected="selected"{% endif %}>POP3</option>
			<option {% if post.in_protocol == "IMAP" %}selected="selected"{% endif %}>IMAP</option>
		</select>
	</div>
	<div class="par">Порт</div>
	<div class="par"><input type="text" name="in_port" id="in_port" value="{{ post.in_port }}" /></div>
	<div class="par">SSL</div>
	<div class="par">
		<select name="in_ssl" id="in_ssl">
			<option value="notls" {% if post.in_ssl == "notls" %}selected="selected"{% endif %}>NO SSL</option>
			<option value="ssl" {% if post.in_ssl == "ssl" %}selected="selected"{% endif %}>SSL</option>
		</select>
	</div>
</div>

<div style="float: left">
	<div class="par"><b>Исходящая почта</b></div>
	
	<div class="par">Сервер</div>
	<div class="par"><input type="text" name="out_server" id="out_server" value="{{ post.out_server }}" /></div>
	
	<div class="par">Аутентификация</div>
	<div class="par">
		<select name="out_auth" id="out_auth">
			<option value="0" {% if post.out_auth == "0" %}selected="selected"{% endif %}>Не требуется</option>
			<option value="1" {% if post.out_auth == "1" %}selected="selected"{% endif %}>Как и для входящей почты</option>
			<option value="2" {% if post.out_auth == "2" %}selected="selected"{% endif %}>Задать логин и пароль</option>
		</select>
	</div>
	
	<div id="out_auth_param">
		<div class="par">Логин</div>
		<div class="par"><input type="text" name="out_login" id="out_login" value="{{ post.out_login }}" /></div>
		<div class="par">Пароль</div>
		<div class="par"><input type="password" name="out_password" id="out_password" value="{{ post.out_password }}" /></div>
	</div>
	
	<div class="par">Порт</div>
	<div class="par"><input type="text" name="out_port" id="out_port" value="{{ post.out_port }}" /></div>
	<div class="par">SSL</div>
	<div class="par">
		<select name="out_ssl" id="out_ssl">
			<option value="notls" {% if post.out_ssl == "notls" %}selected="selected"{% endif %}>NO SSL</option>
			<option value="ssl" {% if post.out_ssl == "ssl" %}selected="selected"{% endif %}>SSL</option>
		</select>
	</div>
</div>
</div>


<!--  SIGNATURE -->
<div style="overflow: hidden; margin-top: 30px">
<h3>Подпись</h3>
<!-- jhtmlarea -->
<div style="overflow: hidden; margin-bottom: 10px">

<div id="text_area" style="float: left">
    <textarea id="jHtmlArea" name="textfield" style="width: 700px; height: 300px">{{ signature }}</textarea>
</div>

</div>
<!-- /jhtmlarea -->
</div>
<!--  /SIGNATURE -->

<div style="margin-top: 20px"><input type="submit" name="submit" value="Изменить" /></div>

</form>

<script type="text/javascript">
	$(document).ready(function(){
		htmlarea();
	});

	if ($("#clear").attr('checked')) {
		$("#div_clear_days").hide();
	} else {
		if ($("#in_protocol").val() == "IMAP") {
			$("#div_clear_days").show();
		}
	}

	$("#clear").change(function() {
		if ($("#clear").attr('checked')) {
			$("#div_clear_days").hide();
		} else {
			if ($("#in_protocol").val() == "IMAP") {
				$("#div_clear_days").show();
			}
		}
	});

	var out_auth = '{{ post.out_auth }}';
	if (out_auth == "0") { $("#out_auth_param input").attr("disabled", "disabled"); };
	if (out_auth == "1") { $("#out_auth_param input").attr("disabled", "disabled"); };
	if (out_auth == "2") { $("#out_auth_param input").removeAttr("disabled"); };

	$("#in_protocol").change(function() {
		if ($("#in_protocol").val() == "POP3") {
			$("#in_port").val("110");
			$("#div_clear_days").hide();
			$("#clear_days").val("0");
		};
		if ($("#in_protocol").val() == "IMAP") {
			$("#in_port").val("143");
			if (!$("#clear").attr('checked')) {
				$("#div_clear_days").show();
			}
		};
	});

	$("#out_auth").change(function() {
		if ($("#out_auth").val() == "0") { $("#out_auth_param input").attr("disabled", "disabled"); };
		if ($("#out_auth").val() == "1") { $("#out_auth_param input").attr("disabled", "disabled"); };
		if ($("#out_auth").val() == "2") { $("#out_auth_param input").removeAttr("disabled"); };
	});
</script>