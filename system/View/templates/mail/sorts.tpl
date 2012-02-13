<div style="overflow: hidden; margin-bottom: 20px">
<div class="button" style="float: left">
	<img src="{{ registry.uri }}img/left/mail-plus.png" alt="" border="0" style="vertical-align: middle" />
	<a href="{{ registry.uri }}sort/?add">Добавить сортировку</a>
</div>
</div>

{% if not list %}
<p>сортировок не найдено</p>
{% else %}

<table width="100%">

{% for part in list %}
<tr>

	<td style="text-align: center; padding: 2px">
		<a title="правка" style="cursor: pointer" href="{{ registry.uri }}sort/?id={{ part.0.sort_id }}"><img style="vertical-align: middle" src="{{ registry.uri }}img/edititem.gif" alt="правка" border="0" /></a>
	</td>
	
	<td style="text-align: center; padding: 2px">
		<a title="удаление" style="cursor: pointer" onclick="delSortConfirm({{ part.0.sort_id }})"><img style="vertical-align: middle" src="{{ registry.uri }}img/delete.png" alt="удаление" border="0" /></a>
	</td>
	
	<td style="padding: 2px">
		{% for parted in part %}
			<div class="par"><b>{{ parted.type }}:</b> {{ parted.val }}</div>
		{% endfor %}
	</td>
	
	{% if part.0.action == "move" %}
	<td style="padding: 2px"><b>переместить в: </b>{{ part.0.folder }}</td>
	{% elseif part.0.action == "remove" %}
	<td style="padding: 2px"><b>удалить</b></td>
	{% endif %}
	
</tr>
{% endfor %}
</table>

{% endif %}