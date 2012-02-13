<div class="emailhead">

<div style="background-color: #DFE4EA; border-bottom: 1px solid #FFF; padding: 2px 4px">
<span style="margin-right: 10px"><b>Дата: </b>
{{ mail.0.timestamp }}
</span>
<span style="margin-right: 10px"><b>Тема:</b> {{ mail.0.subject }}</span>
</div>
<div style="background-color: #DFE4EA; border-bottom: 1px solid #FFF; padding: 2px 4px">
<b>Отправитель:</b>
<a href="mailto: {{ mail.0.email }}">{{ mail.0.email }}</a>
</div>
<div style="background-color: #DFE4EA; border-bottom: 1px solid #FFF; padding: 2px 4px">
<b>Получатель:</b>
<a href="mailto: {{ mail.0.email }}">{{ mail.0.to }}</a>
</div>
<div style="background-color: #DFE4EA; padding: 6px 4px; border-bottom: 1px solid #FFF">
{% set i = 0 %}
{% for part in mail %}
{% set i = i + 1 %}
<a style="cursor: pointer; text-decoration: none" onclick="showText('{{ i }}')"><span class="button">{{ part.type }}</span></a>
{% endfor %}
</div>
{% if mail.0.attach %}
<div style="background-color: #DFE4EA; border-bottom: 1px solid #FFF; padding: 2px 4px">
{% for part in mail.0.attach %}
<a class="attach" style="margin-right: 10px; cursor: pointer" href="{{ registry.uri }}attach/?mid={{ mail.0.id }}&filename={{ part.filename }}&type=out">{{ part.filename }}</a>
{% endfor %}
</div>
{% endif %}
<div style="background-color: #DFE4EA; padding: 2px 4px; overflow: hidden">
<div style="float: right">
	<span class="button">
	<img style="position: relative; top: 5px" src="{{ registry.uri }}img/delete.png" alt="удаление" border="0" />
	<a title="удаление" style="cursor: pointer" onclick="delMailConfirm()">удалить</a>
	</span>
</div>
</div>

</div>

{% set i = 0 %}

{% for part in mail %}
{% set i = i + 1 %}
	<iframe style="display: none" class="mailtext" id="text{{ i }}" src="{{ registry.siteName }}{{ registry.uri }}load/?out=1&mid={{ mail.0.id }}&part={{ i }}" frameborder="0" width="100%" height="90%"></iframe>
{% endfor %}

<script type="text/javascript">
var height = document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;
$(".mailtext").height(height - 180 - $(".emailhead").height());

$(document).keyup(function(e) {
	switch(e.keyCode) {
		case 37: backtolist(); break;
	};
});

showText('1');

function showText(id) {
	$(".mailtext").hide();
	$("#text" + id).show();
};
</script>