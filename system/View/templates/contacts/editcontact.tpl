<form action="{{ registry.uri }}contact/edit/" method="post">
<h2>Новый контакт</h2>

<div style="color: green">{{ msg }}</div>

<div class="par"><b>Email:</b></div>
<div class="par"><input type="text" name="email" value="{{ email }}" readonly="readonly" /></div>
<div class="par"><b>Группа, куда добавляем контакт:</b></div>
<div class="par">
<select name="group">
	<option value="0">Без группы</option>
	{% for part in groups %}
	<option value="{{ part.id }}">{{ part.name }}</option>
	{% endfor %}
</select>
</div>

{% for part in post %}
<div class="par"><b>{{ part.name }}:</b></p>
<div class="par"><input name="{{ part.fid }}" type="text" value="{{ part.val }}" /></div>
{% endfor %}

<input type="submit" name="submit" value="Сохранить" />
</form>