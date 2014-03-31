
<!DOCTYPE html>

<html>
	<head>
	<style>
		#game_board{visibility:visible; width:500px;height:100px;}
	</style>
	<script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="http://d3lp1msu2r81bx.cloudfront.net/kjs/js/lib/kinetic-v5.0.1.min.js"></script>
	<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
	
	<script>
		var otherUser = "<?= $otherUser->login ?>";
		var user = "<?= $user->login ?>";
		var status = "<?= $status ?>";
		var game_board;
		var turn_counter;
		$(function(){
			$('body').everyTime(1000,function(){
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

					var url = "<?= base_url() ?>board/getBoard";
					$.getJSON(url, function (data,text,jqXHR){
						if (data && data.status=='success') {
							game_board=(data.game_board);
							$('[name=game_board]').val(game_board);
						}
					});
					
					var user_color_id = <?= $user_color_id?>;
					var color = ['white', 'red', 'yellow'];
					var mouseover_enable = true;	
					  
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

					for(col=0;col<7;col=col+1){
					  for(row=0;row<6;row=row+1){
						(function()
							{
								var empty_spot = new Kinetic.Circle({
								x: col*100+ 150,
								y: row*80 + 50,
								radius: 30,
								//color the circle according to the number 
								//retrived from the gameboard[] data.
								fill: color[game_board[col*6+row]],
								stroke: 'black',
								strokeWidth: 1,
								id: col * 6 + row,
							});	

							circleGroup.add(empty_spot);	
										  
							empty_spot.on('click', function(evt){
								select_num = empty_spot.id();
								col_num = parseInt(select_num / 6);
								for(col_num_row =5;col_num_row>=0;col_num_row--){
									if(game_board[col_num*6+col_num_row] == 0){
										row_num = col_num_row;
										break;
									}else if(col_num_row == 0){
										row_num = -1;
									}					
								}
								if(row_num !=-1){
									turn_counter = counter_nonzero(game_board); 
									//alert(turn_counter);
									if((turn_counter % 2 == 0 && user_color_id == 1) ||
										(turn_counter % 2 == 1 && user_color_id == 2)){										
										//alert("color changed");
										game_board[col_num*6+row_num] = user_color_id;
										$('[name=game_board]').val(game_board);
										//alert($('[name=game_board]').val());
										//alert("send value");
										var arguments = $('[name=game_board]').serialize();
										//alert("serialized success");
										var url = "<?= base_url() ?>board/postBoard";
										//alert(arguments);
										$.post(url,arguments);
										return false;
									}									
								//circleGroup.get('#' + (col_num*6+row_num))[0].setFill(color[game_board[col_num*6+row_num]]);					
								}else{
									alert("This column is unavailable!");
								}
								layer.draw();
							});	
							
				
							})();	
						  }	  		  
					  }	  
					  
					  // add the shape to the layer
					  layer.add(rect);
					  layer.add(circleGroup);
					  
				
							circleGroup.on('mouseover', function() {
								   if(mouseover_enable){
									document.body.style.cursor = 'pointer';
								   }else{
									document.body.style.cursor = 'default';
								   }
							});
							circleGroup.on('mouseout', function() {
								  document.body.style.cursor = 'default';
							});
						
				
					  // add the layer to the stage
					  stage.add(layer);	
	  
					
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
			/*	
			$('#container').click(function(){
				alert("click?");
				var turn_counter = counter_nonzero(game_board); 
				alert("send?");
				if((turn_counter % 2 == 0 && user_color_id == 1) ||(turn_counter % 2 == 1 && user_color_id == 2)){	
					if($('[name=game_board]').val()!=""){
						alert("send value");
						var arguments = $('[name=game_board]').serialize();
						var url = "<?= base_url() ?>board/postBoard";
						$.post(url,arguments);
						return false;
					}else{

					}
				}else{

				}
			});	*/
			
		});

		function counter_nonzero(game_board){
			var count_num = 0;
			//alert("in the function");
			for (var i = 0; i < 42; i += 1){
				//alert(game_board[i]);
				if(game_board[i] != 0){
					count_num += 1;
					//alert("ffikkkdsklfjdskl");
				} 
			}

			return count_num;
		}
	
	</script>
	</head> 
<body>  
	<h1>Game Area</h1>
	<div>
	Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)') ?>  
	</div>
	<div>
	Your color is <?php echo $x = $user_color_id == 1? 'red' :  'yellow';?>
	<?php 
		if($user_color_id != 1 && $user_color_id != 2){
			header("Refresh:0");	
		}
	?>
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
<br>
		
	<script>



	</script>
	</div>
	<br/>
	
<?php 
	$att =  array('id'=>'game_board');
	echo form_open(NULL,$att);
	echo form_textarea('game_board');
	echo form_close();
	echo form_textarea('conversation');
	echo form_open();
	echo form_input('msg');
	echo form_submit('Send','Send');
	echo form_close();

	
	
?>
	
	
	
</body>

</html>

