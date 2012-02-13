<h3 style="color: #048">{{ email }}</h3>

<div class="par">
	<a href="{{ registry.uri }}contact/edit/?email={{ email }}"><img src="{{ registry.uri }}img/highlighter-small.png" alt="правка" /></a>
	<a style="cursor: pointer" onclick="delContactConfirm('{{ email }}')"><img src="{{ registry.uri }}img/minus-small.png" alt="удалить" /></a>
</div>
	{% for part in contact %}
		{% if part.val %}
		<div class="par"><b>{{ part.name }}: </b>{{ part.val }}</div>
		{% endif %}
	{% endfor %}