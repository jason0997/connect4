
<!DOCTYPE html>

<html>
	<head>
		<style>
			input {
				display: block;
			}
		</style>

	</head> 
<body>  
	<h1>Login</h1>
<?php 
	if (isset($errorMsg)) {
		echo "<p>" . $errorMsg . "</p>";
	}


	echo form_open('account/login');
	echo form_label('Username'); 
	echo form_error('username');
	echo form_input('username',set_value('username'),"required");
	echo form_label('Password'); 
	echo form_error('password');
	echo form_password('password','',"required");
	echo form_label('<img id="captcha" src="' . base_url() . '/securimage/securimage_show.php" alt="CAPTCHA Image"/>');
	echo form_label('<input type="text" name="captcha_code" size="10" maxlength="6" />');
	echo form_label("<a href=\"#\" onclick=\"document.getElementById('captcha').src = '" . base_url() . "/securimage/securimage_show.php?' + Math.random(); return false\">[ Different Image ]</a>");
	echo form_submit('submit', 'Login');
	//optional: to give user more flexibility to see different imgs.
	

	echo "<p>" . anchor('account/newForm','Create Account') . "</p>";

	echo "<p>" . anchor('account/recoverPasswordForm','Recover Password') . "</p>";
	
	
	echo form_close();
?>	
</body>

</html>

