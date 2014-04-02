
<!DOCTYPE html>

<html>
	<head>
	<style>
		#game_board{visibility:visible; width:500px;height:100px;}
		#winner_id{visibility:visible; width:500px;height:100px;}
		#game_status{visibility:visible; width:500px;height:100px;}

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
		var game_over;
		var user_color_id;
		
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
					
					var url = "<?= base_url() ?>board/checkStatus";
					$.getJSON(url, function (data,text,jqXHR){
						if (data && data.status=='success') {
							game_status=(data.game_status);
							$('[name=game_status]').val(game_status);							
						}
					});

					if($('[name=game_status]').val()=='over'){
						window.location.href = '<?= base_url() ?>arcade/index';
					}
										
					user_color_id = <?= $user_color_id?>;
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
									if((turn_counter % 2 == 0 && user_color_id == 1) ||
										(turn_counter % 2 == 1 && user_color_id == 2)){										
										game_board[col_num*6+row_num] = user_color_id;
										$('[name=game_board]').val(game_board);
										var arguments = $('[name=game_board]').serialize();
										var url = "<?= base_url() ?>board/postBoard";
										$.post(url,arguments);
										//alert(game_board);
										var cur_index = col_num*6+row_num; 
										game_over = check_winner_status(cur_index, user_color_id, game_board);
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

					// displaying whose turn !!
					turn_counter = counter_nonzero(game_board);
					var cur_turn;
					if(turn_counter % 2 == 0){
						cur_turn = "Player 1";
					}else{
						cur_turn = "Player 2";
					}
					$("#myResults").html(cur_turn);

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
				
			$('#container').click(function(){
				alert(game_over);
				if(!game_over){
					var num_nonzero= counter_nonzero(game_board); 
					if(num_nonzero == 42){
						game_over = true;
						use_color_id = 100;
					}
				}
				if(game_over){
					$('[name=winner_id]').val(user_color_id);
					alert(user_color_id);
						var arguments = $('[name=winner_id]').serialize();
						var url = "<?= base_url() ?>board/checkWinner";
						$.post(url,arguments);					
				}
			});				
				
		});

		function counter_nonzero(game_board){
			var count_num = 0;
			for (var i = 0; i < 42; i += 1){
				if(game_board[i] != 0){
					count_num += 1;
				} 
			}

			return count_num;
		}

		function check_winner_status(cur_index, user_color_id, game_board){
			
			if(horizontalCheck(cur_index, user_color_id, game_board)||
				verticalCheck(cur_index, user_color_id, game_board)||
	  			diagonalCheck(cur_index, user_color_id, game_board)){
	  			return true;
	  		}else{
	  			return false;
	  		}
		}
		
		function horizontalCheck(cur_index, cur_player, game_board){
	  		var left_num = checkLX(cur_index, cur_player, game_board);
	  		var right_num = checkRX(cur_index, cur_player, game_board);
	  		var sum = left_num + right_num + 1;
	  		if (sum >= 4){
	  			return true;
	  		}else{
	  			return false;
	  		}
		}
		
		function checkLX(cur_index, cur_player, game_board){
	  		next_index= cur_index - 6;
	  		if(next_index >= 0 && game_board[next_index] == cur_player){
	  			var result  = 1 + (checkLX(next_index, cur_player, game_board));
	  			return result;
	  		}else{
	  			return 0;
	  		}
	  	}

	  	function checkRX(cur_index, cur_player, game_board){
	  		next_index= cur_index + 6;
	  		if(next_index <= 41 && game_board[next_index] == cur_player){
	  			var result  = 1 + (checkLX(next_index, cur_player, game_board));
	  			return result;
	  		}else{
	  			return 0;
	  		}
	  	}

	  	
	  	function verticalCheck(cur_index, cur_player, game_board){
	  		var upperbound = cur_index + (5 - (cur_index % 6));
	  		if((upperbound - cur_index) > 2){ 
	  			if(checkDY(cur_index, cur_player, game_board, upperbound, 1)){
	  				return true;
	  			}else{
	  				return false;
	  			}
	  		}else{
	  			return false;
	  		}
	  	}

	  	function checkDY(cur_index, cur_player, game_board, upperbound, counter){
	  		if (counter == 4){
	  			return true;
	  		}

	  		next_index = cur_index + 1;
	  		if(next_index <= upperbound && game_board[next_index] == cur_player){
	  			counter += 1;
	  			return checkDY(next_index, cur_player, game_board, upperbound, counter);
	  		}else{
	  			return false;
	  		}
	  	}
	  	
	  	function diagonalCheck(cur_index, cur_player, game_board){
	  		var left_down_num = checkLD(cur_index, cur_player, game_board);
	  		var right_up_num = checkRU(cur_index, cur_player, game_board);
	  		var sum1 = right_up_num + left_down_num + 1;

	  		var left_up_num = checkLU(cur_index, cur_player, game_board);
	  		var right_down_num = checkRD(cur_index, cur_player, game_board);
	  		var sum2 = right_down_num + left_up_num + 1;

	  		if ((sum1 >= 4) || (sum2 >= 4)){
	  			return true;
	  		}else{
	  			return false;
	  		}
	  	}

	  	function checkLD(cur_index, cur_player, game_board){
	  		
	  		next_index = cur_index - 5;
	  		if(next_index > 0 && game_board[next_index] == cur_player){
	  			var result  = 1 + checkLD(next_index, cur_player, game_board);
	  			return result;
	  		}else{
	  			return 0;
	  		}
	  	}

	  	function checkRU(cur_index, cur_player, game_board){
	  		
	  		next_index = cur_index + 5;
	  		if(next_index < 41 && game_board[next_index] == cur_player){
	  			var result  = 1 + checkLD(next_index, cur_player, game_board);
	  			return result;
	  		}else{
	  			return 0;
	  		}
	  	}

	  	function checkLU(cur_index, cur_player, game_board){
	  		
	  		next_index = cur_index - 7;
	  		if(next_index >= 0 && game_board[next_index] == cur_player){
	  			var result  = 1 + checkLU(next_index, cur_player, game_board);
	  			return result;
	  		}else{
	  			return 0;
	  		}
	  	}

	  	function checkRD(cur_index, cur_player, game_board){
	  		
	  		next_index = cur_index + 7;
	  		if(next_index <= 41 && game_board[next_index] == cur_player){
	  			var result  = 1 + checkRD(next_index, cur_player, game_board);
	  			return result;
	  		}else{
	  			return 0;
	  		}
	  	}

	</script>
	</head> 
<body>  
	<h1>Game Area</h1>
	<div>
	Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)') ?>  
	</div>
	<div> Your are: <?php echo $x = $user_color_id == 1? 'Player 1' : 'Player 2';?></div>
	<div>
	Your color: <?php echo $x = $user_color_id == 1? 'Red' :  'Yellow';?>
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

	<div> Current Turn: <span id="myResults"></span></div>
	</div>
		<div id="container"  style="background:grey; width:900px;height:500px">
<br>
		
	</div>
	<br/>
	
<?php 
	$att =  array('id'=>'game_board');
	echo form_open(NULL,$att);
	echo form_textarea('game_board');
	echo form_close();
	$att1 = array('id'=>'winner_id');
	echo form_open(NULL,$att1);
	echo form_textarea('winner_id');
	echo form_close();
	$att2 = array('id'=>'game_status');
	echo form_open(NULL,$att2);
	echo form_textarea('game_status');
	echo form_close();

	echo form_textarea('conversation');
	echo form_open();
	echo form_input('msg');
	echo form_submit('Send','Send');
	echo form_close();

	
	
?>
	
	
	
</body>

</html>

