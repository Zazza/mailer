{% if err %}
{% for part in err %}
<p style="color: red">{{ part }}</p>
{% endfor %}
{% endif %}


<form method="post" action="{{ registry.uri }}compose/">

<h2 style="margin: 10px 0 20px 0"><b>Новое сообщение</b></h2>

<div class="pre"><b>Кому:</b></div>
<div class="pre"><input name="to" type="text" style="width: 400px" value="{{ post.to }}" /></div>

<div class="pre"><b>Тема:</b></div>
<div class="pre"><input name="subject" type="text" style="width: 400px" value="{{ post.subject }}" /></div>

<div id="arfiles">
<div class="pre" style="color: black">
Прикреплённые файлы
<span style="color: green">(<a style="color: green; cursor: pointer" onclick="flushAttaches()">очистить</a>)</span>:
</div>
<div id="attach_files" style="margin-top: 10px"></div>
</div>

<!-- jhtmlarea -->
<div style="overflow: hidden; margin-bottom: 10px">

<div id="text_area" style="float: left">
    <textarea id="jHtmlArea" name="textfield" style="width: 700px; height: 300px">{{ post.textfield }}</textarea>
</div>

</div>
<!-- /jhtmlarea -->

<div class="pre"><b>Почтовый ящик для отправки:</b></div>
<select name="mailbox" id="mailbox">
{% if post.email %}
	{% for mailbox in mailboxes %}
	<option value="{{ mailbox.id }}" {% if post.email == mailbox.name %}selected="selected"{% endif %}>{{ mailbox.name }}</option>
	{% endfor %}
{% else %}
	{% for mailbox in mailboxes %}
	<option value="{{ mailbox.id }}" {% if mailbox.default %}selected="selected"{% endif %}>{{ mailbox.name }}</option>
	{% endfor %}
{% endif %}
</select>

<div class="pre" style="margin-top: 30px">
<input type="submit" name="submit" value="Написать" />
</div>

</form>

<script type="text/javascript">
$(document).ready(function(){
	htmlarea();
    
    sign($("#mailbox option:selected").val());
    
    $("#mailbox").change(function(){
    	sign($("#mailbox option:selected").val());
    });
});
</script>