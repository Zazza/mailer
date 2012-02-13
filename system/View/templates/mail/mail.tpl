<div id="mail{{ mail.0.id }}">

<div class="emailhead">

<div style="background-color: #DFE4EA; border-bottom: 1px solid #FFF; padding: 4px 7px">
{% if not task %}
<span style="margin-right: 10px"><b>Дата: </b>
{% if mail.0.date != "0000-00-00 00:00:00" %}
{{ mail.0.date }}
{% else %}
{{ mail.0.timestamp }}
{% endif %}
</span>
{% endif %}
<span style="margin-right: 10px"><b>Тема:</b> {{ mail.0.subject }}</span>
</div>

{% if not task %}
<div style="background-color: #DFE4EA; border-bottom: 1px solid #FFF; padding: 4px 7px">

<b>Отправитель:</b>
{% if mail.0.personal %}({{ mail.0.personal }})&nbsp;{% endif %}
<a href="mailto: {{ mail.0.email }}" style="margin-right: 10px">{{ mail.0.email }}</a>

<span style="float: right">
{% if mail.0.contact %}
	<a style="cursor: pointer; margin-right: 2px" onclick="getInfo('{{ mail.0.email }}')"><img src="{{ registry.uri }}img/information-button.png" title="полные данные" alt="info" border="0" style="position: relative; top: 1px" /></a>
{% else %}
	<a href="{{ registry.uri }}contact/add/?email={{ mail.0.email }}" style="margin-right: 2px" title="добавить контакт"><img src="{{ registry.uri }}img/plus-button.png" alt="" border="0" style="position: relative; top: 1px" /></a>
{% endif %}
</span>

</div>
{% endif %}

{% if not task %}
<div style="background-color: #DFE4EA; border-bottom: 1px solid #FFF; padding: 4px 7px">
<b>Получатель:</b>
<a href="mailto: {{ mail.0.email }}">{{ mail.0.to }}</a>
</div>
{% endif %}

<div style="background-color: #DFE4EA; padding: 6px 4px; border-bottom: 1px solid #FFF">
{% set i = 0 %}
{% for part in mail %}
{% set i = i + 1 %}
<a style="cursor: pointer; text-decoration: none" onclick="showText('{{ mail.0.id }}', '{{ i }}')"><span class="button">{{ part.type }}</span></a>
{% endfor %}
</div>

{% if mail.0.attach %}
<div style="background-color: #DFE4EA; border-bottom: 1px solid #FFF; padding: 4px 7px">
{% for part in mail.0.attach %}
<a class="attach" style="margin-right: 10px; cursor: pointer" href="{{ registry.uri }}attach/?mid={{ mail.0.id }}&filename={{ part.filename }}">{{ part.filename }}</a>
{% endfor %}
</div>
{% endif %}


<div style="background-color: #DFE4EA; padding: 4px 7px">
<div style="float: right">
	<span class="button" style="margin-right: 5px">
		<img src="{{ registry.uri }}img/left/mail-plus.png" alt="" border="0" style="position: relative; top: 4px" />
		<a href="{{ registry.uri }}sort/?mid={{ mail.0.id }}" style="text-decoration: none">сортировка</a>
	</span>
	
	<span class="button" style="cursor: pointer">
		<img style="position: relative; top: 5px" src="{{ registry.uri }}img/delete.png" alt="удаление" border="0" />
		<a title="удаление" style="cursor: pointer; text-decoration: none" onclick="delMailConfirm()">удалить</a>
	</span>
</div>

	<span class="button" style="margin-right: 5px">
		<img src="{{ registry.uri }}img/mail-reply.png" alt="" border="0" style="position: relative; top: 4px" />
		<a href="{{ registry.uri }}compose/?action=reply&mid={{ mail.0.id }}" style="text-decoration: none; margin-right: 10px">ответить</a>
	</span>
</div>


</div>

{% set i = 0 %}

{% for part in mail %}
{% set i = i + 1 %}
	<iframe style="display: none" class="mailtext" id="text{{ i }}" src="{{ registry.siteName }}{{ registry.uri }}load/?mid={{ mail.0.id }}&part={{ i }}" frameborder="0" width="100%" height="90%"></iframe>
{% endfor %}

</div>

<script type="text/javascript">
var height = document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;
$(".mailtext").height(height - 180 - $(".emailhead").height());

$(document).keyup(function(e) {
	switch(e.keyCode) {
		case 37: backtolist(); break;
	};
});

showText('{{ mail.0.id }}', '1');

function showText(mid, id) {
	$("#mail" + mid + " > .mailtext").hide();
	$("#mail" + mid + " > #text" + id).show();
};

function addTaskFromMail(mid) {
    var data = "action=addTaskFromMail&mid=" + mid;
    $.ajax({
    	type: "POST",
    	url: "{{ registry.uri }}ajax/mail/",
    	async: false,
    	data: data,
    	success: function(res) {
    		document.location.href = "{{ registry.uri }}tt/edit/" + res  + "/";
        }
    });
};
</script>