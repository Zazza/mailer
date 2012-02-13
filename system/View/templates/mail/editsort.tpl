{% if err %}
<div style="margin-bottom: 20px">
{% for part in err %}
<p style="color: red">{{ part }}</p>
{% endfor %}
</div>
{% endif %}

<form method="post" action="{{ registry.uri }}sort/?id={{ sort.0.sort_id }}">

<p><b>Сортировать по:</b></p>

{% for part in sort %}

{% if part.type == "from" %}
<p>Поле "От кого"</p>
<p>
	<input type="text" name="from" value="{{ part.val }}" style="width: 280px" />
</p>
{% endif %}

{% if part.type == "to" %}
<p>Поле "Кому"</p>
<p>
	<input type="text" name="to" value="{{ part.val }}" style="width: 280px" />
</p>
{% endif %}

{% if part.type == "subject" %}
<p>Поле "Тема" (содержит текст)</p>
<p>
	<input type="text" name="subject" value="{{ part.val }}" style="width: 280px" />
</p>
{% endif %}

{% endfor %}

<p><b>Действие:</b></p>

<p>
{% if not folders %}
	<a href="{{ registry.uri }}folder/">Создать папку</a>
{% else %}
	<label>
	<input type="radio" class="mail_action" name="mail_action" {% if sort.0.action == "move" %}checked="checked"{% endif %} value="move" />
	Переместить в
	</label>
	<select name="folder">
		{% for part in folders %}
		<option value="{{ part.id }}" {% if sort.0.folder_id == part.id %}selected="selected"{% endif %}>{{ part.folder }}</option>
		{% endfor %}
	</select>
{% endif %}
</p>

<p>
<label>
	<input type="radio" class="mail_action" name="mail_action" {% if sort.0.action == "remove" %}checked="checked"{% endif %} value="remove" />
	удалить
</label>
</p>

<p style="margin-top: 20px">
	<input type="submit" name="edit_sort" value="Редактировать сортировку" />
</p>

</form>

<script type="text/javascript">
{% if sort.0.action == "task" %}$("#addtask").show();{% endif %}
$(".mail_action").change(function(){
	if ($(this).val() == "task") {
		$("#addtask").show();
	} else {
		$("#addtask").hide();
	}
});
</script>