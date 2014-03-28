
<!DOCTYPE html>

<html>
	<head>
	<script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="http://d3lp1msu2r81bx.cloudfront.net/kjs/js/lib/kinetic-v5.0.1.min.js"></script>
	<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
	

	<script>

		var otherUser = "<?= $otherUser->login ?>";
		var user = "<?= $user->login ?>";
		var status = "<?= $status ?>";
		
		$(function(){
			$('body').everyTime(2000,function(){
					if (status == 'waiting') {
						$.getJSON('<?= base_url() ?>arcade/checkInvitation',function(data, text, jqZHR){
								if (data && data.status=='rejected') {
									alert("Sorry, your invitation to play was declined!");
									window.location.href = '<?= base_url() ?>arcade/index';
								}
								if (data && data.status=='accepted') {
									status = 'playing';
									$('#status').html('Playing ' + otherUser);
								}
								
						});
					}
					var url = "<?= base_url() ?>board/getMsg";
					$.getJSON(url, function (data,text,jqXHR){
						if (data && data.status=='success') {
							var conversation = $('[name=conversation]').val();
							var msg = data.message;
							if (msg.length > 0)
								$('[name=conversation]').val(conversation + "\n" + otherUser + ": " + msg);
						}
					});
			});

			$('form').submit(function(){
				var arguments = $(this).serialize();
				var url = "<?= base_url() ?>board/postMsg";
				$.post(url,arguments, function (data,textStatus,jqXHR){
						var conversation = $('[name=conversation]').val();
						var msg = $('[name=msg]').val();
						$('[name=conversation]').val(conversation + "\n" + user + ": " + msg);
						});
				return false;
				});	
		});
	
	</script>
	</head> 
<body>  
	<h1>Game Area</h1>
	<div>
	Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)') ?>  
	</div>
	
	<div id='status'> 
	<?php 
		if ($status == "playing")
			echo "Playing " . $otherUser->login;
		else
			echo "Waiting on " . $otherUser->login;
	?>
	</div>
		<div id="container"  style="background:grey; width:900px;height:500px">
	<script>
      var stage = new Kinetic.Stage({
        container: 'container',
        width: 900,
        height: 500,
      });
	   
      var layer = new Kinetic.Layer();
	  var circleGroup = new Kinetic.Group({});
		  
		  
      var rect = new Kinetic.Rect({
        x: 50,
        y: 10,
        width: 800,
        height: 480,
        fill: '#000099',
        stroke: 'black',
        strokeWidth: 4
      });

	  for($col=0;$col<7;$col=$col+1){
		  for($row=0;$row<6;$row=$row+1){
			  var empty_spot = new Kinetic.Circle({
				x: $col*100+ 150,
				y: $row*80 + 50,
				radius: 30,
				fill: 'white',
				stroke: 'black',
				strokeWidth: 1
			  });	
			  circleGroup.add(empty_spot);				
		  }	  		  
	  }	  
      // add the shape to the layer
      layer.add(rect);
	  layer.add(circleGroup);
	  
 		circleGroup.on('mouseover', function() {
			  document.body.style.cursor = 'pointer';
			});
		  circleGroup.on('mouseout', function() {
			  document.body.style.cursor = 'default';
			});

      // add the layer to the stage
      stage.add(layer);	
	</script>
	</div>
	<br/>
<?php 
	
	echo form_textarea('conversation');
	
	echo form_open();
	echo form_input('msg');
	echo form_submit('Send','Send');
	echo form_close();
	
?>
	
	
	
	
</body>

</html>

