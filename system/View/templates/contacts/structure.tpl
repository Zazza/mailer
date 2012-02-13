{% for part in tree %}
<div class="contact">
<div class="title">
	<a href="{{ registry.uri }}contact/?groups={{ part.id }}">{{ part.name }}</a>
	
	<span style="vertical-align: middle; margin-left: 10px">
	<a style='cursor: pointer' onclick='editGroup("{{ part.id }}")' title='правка'>
	<img src='{{ registry.uri }}img/edititem.gif' alt='правка' />
	</a>
	
	<a style='cursor: pointer' onclick='delGroup("{{ part.id }}")' title='удалить'>
	<img src='{{ registry.uri }}img/delete.png' alt='удалить' />
	</a>
	</span>
</div>
</div>
{% endfor %}