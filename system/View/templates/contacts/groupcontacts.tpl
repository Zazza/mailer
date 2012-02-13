<h2>Контакты</h2>

<p>
<span class="button">
	<a href="{{ registry.uri }}contact/">
	<img style="vertical-align: middle" src="{{ registry.uri }}img/back.png" alt="назад">
	вернуться на уровень выше</a>
</span>
</p>

{% for part in contacts %}
<div class="contact">
	<div class="title">
		{{ part.email }}
		<span style="vertical-align: middle; margin-left: 10px">
		<a href="{{ registry.uri }}contact/edit/?email={{ part.email }}"><img src="{{ registry.uri }}img/edititem.gif" alt="правка" /></a>
		<a style="cursor: pointer" onclick="delContactConfirm('{{ part.email }}')"><img src="{{ registry.uri }}img/delete.png" alt="удалить" /></a>
		</span>
	</div>
		{% for contact in part %}
			{% if contact.val %}
			<div class="par"><b>{{ contact.name }}: </b>{{ contact.val }}</div>
			{% endif %}
		{% endfor %}
</div>
{% endfor %}