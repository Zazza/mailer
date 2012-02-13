<form method="post" action="{{ registry.uri }}folder/?id={{ folder.id }}" style="margin-bottom: 20px">
<p>Название папки:</p>
<p><input type="text" name="folder" value="{{ folder.folder }}" /></p>
<p><input type="submit" name="edit_submit" value="Изменить" /></p>
</form>