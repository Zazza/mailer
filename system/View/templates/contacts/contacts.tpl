<h2>Контакты</h2>

<span class="button" style="margin-bottom: 10px">
	<img src="{{ registry.uri }}img/users.png" alt="" style="vertical-align: middle" />
	<a href="{{ registry.uri }}contact/add/">
	новый контакт</a>
</span>

<div style="margin-bottom: 20px">

добавить:&nbsp;<input type="text" id="name" name="name" style="width: 150px; margin-right: 20px" />

<input type="button" value="Добавить" onclick="addTree()" />

</div>

<div id="litree"></div>

<div title="Правка" id="editCat" style="display: none">
    <input type="text" id="catname" style="width: 150px" />
</div>

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


<script type="text/javascript">
$(document).ready(function(){
    renderTree();
});

function renderTree() {
    var data = "action=getTree";
	$.ajax({
		type: "POST",
		url: "{{ registry.uri }}ajax/contacts/",
		data: data,
		success: function(res) {
            $("#litree").html(res);            
            $("#structure").treeview();
		}
	})
};

function addTree() {
    var data = "action=addTree&name=" + $("#name").val();
	$.ajax({
		type: "POST",
		url: "{{ registry.uri }}ajax/contacts/",
		data: data,
		success: function(res) {
            renderTree();
		}
	})
};

function delGroup(id) {
    $('<div title="Удаление">Действительно удалить?<div>').dialog({
		modal: true,
	    buttons: {
            "Да": function() {
                delGroupOK(id);
                $(this).dialog("close");
            },
			"Нет": function() {
                 $(this).dialog("close");
            }
		},
		width: 200,
        height: 140
	});
};

function delGroupOK(id) {
    var data = "action=delGroup&id=" + id;
    $.ajax({
    	type: "POST",
    	url: "{{ registry.uri }}ajax/contacts/",
    	data: data,
		success: function(res) {
            renderTree();
		}
    })
};

function editGroup(id) {
    var data = "action=getGroupName&id=" + id;
	$.ajax({
		type: "POST",
		url: "{{ registry.uri }}ajax/contacts/",
		data: data,
		success: function(res) {
            $("#catname").val(res);
		}
	});
    
    $("#editCat").dialog({
		modal: true,
	    buttons: {
            "Готово": function() {
                var data = "action=editGroup&id=" + id + "&name=" + $("#catname").val();
            	$.ajax({
            		type: "POST",
            		url: "{{ registry.uri }}ajax/contacts/",
            		data: data,
            		success: function(res) {
                        renderTree();
            		}
            	});
                
                $(this).dialog("close");
            }
		},
		width: 200,
        height: 140
	});
}
</script>