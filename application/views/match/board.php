
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
				
			$('#container').click(function(){
				$('[name=game_board]').val(game_board);
				if($('[name=game_board]').val()!=""){
					var arguments = $('[name=game_board]').serialize();
					var url = "<?= base_url() ?>board/getClickon";
					$.post(url,arguments );
					return false;
				}else{
				}
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
	var game_board = new Array();
	game_board = eval("<?php echo json_encode($game_board)?>");
	$('[name=game_board]').val(game_board);
	  var color = ['white', 'red', 'yellow'];
	  var	  mouseover_enable = true;
	  
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
			(
			 function(){  var empty_spot = new Kinetic.Circle({
				x: col*100+ 150,
				y: row*80 + 50,
				radius: 30,
				fill: color[game_board[col][row]],
				stroke: 'black',
				strokeWidth: 1,
				id: col * 7 + row,
			  });	
			  circleGroup.add(empty_spot);	
			  
			  //Send row and col to controller, controller check if it is valid, if it is send back json
			  
			  empty_spot.on('click', function(evt){
				 select_num = empty_spot.id();
				col_num = parseInt(select_num / 7);
				for(col_num_row =5;col_num_row>=0;col_num_row--){
					if(game_board[col_num][col_num_row] == 0){
						row_num = col_num_row;
						break;
					}else if(col_num_row == 0){
						row_num = -1;
					}					
				}
				if(row_num !=-1){
										
					game_board[col_num][row_num] = 1;
					circleGroup.get('#' + (col_num*7+row_num))[0].setFill(color[game_board[col_num][row_num]]);					
				}else{
					alert("This column is unavailable!");
				}
				layer.draw();
				//alert("select_num: " + select_num + "cl_num" + col_num + "row_num" + row_num);
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

