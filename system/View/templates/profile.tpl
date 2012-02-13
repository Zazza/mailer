<h2>Профиль</h2>

<form method="post" action="{{ registry.uri }}profile/profile/">

{% if err %}
{% for part in err %}
<p style="color: red">{{ part }}</p>
{% endfor %}
{% endif %}

<div class="par"><b>Логин</b></div>
<div class="par"><input name='login' type='text' size='21' value="{{ post.login }}" /></div>
<div class="par"><b>Пароль</b></div>
<div class="par"><input name='pass' size='21' type='password' value="{{ post.pass }}" /></div>

<div class="par"><b>Имя</b></div>
<div class="par"><input name='name' type='text' size='21' value="{{ post.name }}" /></div>
<div class="par"><b>Фамилия</b></div>
<div class="par"><input name='soname' type='text' size='21' value="{{ post.soname }}" /></div>

<div class="par"><input type="submit" name='editprofile' value='Готово' /></div>

</form>