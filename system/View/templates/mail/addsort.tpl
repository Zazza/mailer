{% if err %}
<div style="margin-bottom: 20px">
{% for part in err %}
<p style="color: red">{{ part }}</p>
{% endfor %}
</div>
{% endif %}

{% if mail.0.id %}
<form method="post" action="{{ registry.uri }}sort/?mid={{ mail.0.id }}">
{% else %}
<form method="post" action="{{ registry.uri }}sort/">
{% endif %}

<h3><b>Сортировать по:</b></h3>

<p>Поле "От кого"</p>
<p>
	<input type="checkbox" name="checkbox_from" />
	<input type="text" name="from" value="{{ mail.0.email }}" style="width: 280px" />
</p>

<p>Поле "Кому"</p>
<p>
	<input type="checkbox" name="checkbox_to" />
	<input type="text" name="to" value="{{ mail.0.to }}" style="width: 280px" />
</p>

<p>Поле "Тема" (содержит текст)</p>
<p>
	<input type="checkbox" name="checkbox_subject" />
	<input type="text" name="subject" value="{{ mail.0.subject }}" style="width: 280px" />
</p>

<h3 style="margin-top: 30px"><b>Действие:</b></h3>

<p>
{% if not folders %}
	<a href="{{ registry.uri }}folder/">Создать папку</a>
{% else %}
	<label>
	<input type="radio" class="mail_action" name="mail_action" checked="checked" value="move" />
	Переместить в
	</label>
	<select name="folder">
		{% for part in folders %}
		<option value="{{ part.id }}">{{ part.folder }}</option>
		{% endfor %}
	</select>
{% endif %}
</p>

<p style="margin-top: 10px">
<label>
	<input type="radio" class="mail_action" name="mail_action" value="remove" />
	удалить
</label>
</p>

<p style="margin-top: 20px">
	<input type="submit" name="submit" value="Создать сортировку" />
</p>

</form>

<script type="text/javascript">
$(".mail_action").change(function(){
	if ($(this).val() == "task") {
		$("#addtask").show();
	} else {
		$("#addtask").hide();
	}
});
</script>