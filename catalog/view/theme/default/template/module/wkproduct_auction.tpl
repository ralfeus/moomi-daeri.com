<?php
 
   $user=0;
   $buser=0;
   $wuser=0;
    if(isset($_SESSION['customer_id']))
    {
      $user=$_SESSION['customer_id'];
    }
   
    /*else if(isset($_SESSION['user_id'])){
      $user=$_SESSION['user_id'];
    }*/
   

   if($timeout==0){
     
      foreach($my_bids as $ibid){
       
       if($ibid['user_id']==$user && $ibid['winner']==1)
      { 
         $wuser=$user;
      }
    }
      if($wuser!=0){?>
          <div class="wkauction">

          <div id="aumain" style="color:green;background-color: #F5F5F5;"><?php echo $entry_winner; ?></div>
          </div>
          <?php 
      }
   }
   else{

      foreach($my_bids as $ibid){

      if($ibid['user_id']==$user)
      {
         $buser=$user;
      }
      
    }
   
   $tbids=count($my_bids);
 
   if($auction_id){
  
   	$sa=explode(" ",$end);

   	$dat=explode("-",$sa[0]);
   	$tim=explode(":",$sa[1]);
   
   


?>

<script type="text/javascript">
function check()
{
  location.reload(); 
}
$(function () {
	var current= new Date();
	var austDay = new Date(<?php echo $dat[0].','.$dat[1].'-1'.','.$dat[2].','.$tim[0].','.$tim[1].','.$tim[2]; ?>);
 
	//austDay = new Date(current.getFullYear() +austDay.getFullYear() );
	//'{{yl} {y>}{o<} {on} {ol} {o>}' + 

	jQuery('#defaultCountdown').Wkcountdown({until: austDay,compact: true, format: 'dHMS', description: '',compactLabels : ['y', 'm', 'w', ' days '],onExpiry:check});


});
</script>

<div class="wkauction">

    <div id="aumain"><?php echo $entry_auction; ?></div>
	<div class="subheadcont">
	    <div id="btimer"><?php echo $entry_time_left; ?><div id="defaultCountdown"></div><div id="defaultCountdown_hover"></div></div>
	    <div id="rangearea">
		<div id="ra1"><?php echo $entry_bids; ?></div><div id="rt1"><?php echo $tbids;?> (<a>See All Bids</a>)</div>
		<div id="ra2"><?php echo $entry_min_price; ?></div><div id="rt2"><?php echo $min;?></div>
		<div id="ra3"><?php echo $entry_max_price; ?></div><div id="rt3"><?php echo $max;?></div>
		<div id="ra4"><?php echo $entry_start_time; ?></div><div id="rt4"><?php echo substr($start,0,11);?></div>
		<div id="ra5"><?php echo $entry_close_time; ?></div><div id="rt5"> <?php echo substr($end,0,11);?></div>
	    </div>
	    <div id="tarea">
	    <?php if($buser==0){?>
		<label id="amountlable"><?php echo $entry_your_price; ?> <?php echo $min;?>) </label><br/>
		<div id="msg"></div>
		<input type="text" id="bidamount" name="bidmount"/>
		<input type="button" id="bidbutton" class="button" value="Bid"/>
	    <?php } else {?>
		<div id="bidamount" style="width:133px;"><?php echo $entry_thnaks; ?></div>
	    <?php }?>
	</div>
    </div>
	
    <div class="subbids">
	<div id="cross"><img id="cimg" src="catalog/view/theme/default/image/crossButton.png"></div>
	<div class="bidme"></div>
    </div>

</div> 
<style type="text/css">    
  
.aligncenter {
    display: block;
    margin: 1em auto;
    text-align: center;
}

</style>

<script>
$("#rt1").click(function(){
      syml = '<?php echo $this->currency->getSymbolLeft($_SESSION["currency"]);?>';
     
      symr = '<?php echo $this->currency->getSymbolRight($_SESSION["currency"]);?>';
     
      val_c = '<?php echo $this->currency->getValue($_SESSION["currency"]);?>';

      var auction=<?php echo $auction_id;?>;
      
      $(".subbids").css('display','block');
      $(".subheadcont").slideUp();
          
      $.ajax({
          type: 'post',
          url: 'index.php?route=module/wkproduct_auction/wkauctionbids',
          data: 'auction='+auction+'&bids='+1+'&left='+syml+'&right='+symr+'&value='+val_c,
          dataType: 'json',
          success: function(json) {
                if(json['success']) {
                      $(".bidme").html(json['success']);
                     
                }else{
                      $(".bidme").html('<div class="bids" style="font-size:11px;"><?php echo $entry_no_bids; ?></div>');
                }
          }
      });
});

$("#cross").click(function(){
    
       //$(".subbids").slideUp();
       //$(".subheadcont").css('opacity',1);
       $(".subbids").css('display','none');
       $(".subheadcont").slideDown();
  });

$("#bidbutton").click(function(){

    var am=$("#bidamount").val();
    var val=<?php echo $this->currency->getValue($_SESSION['currency']); ?>;
    var amount=am/val;
    var user=<?php echo $user;?>;
    var end_date='<?php echo $end;?>';
    var start_date='<?php echo $start;?>';

    var auction=<?php echo $auction_id;?>;
    var product_id=<?php echo $this->request->get['product_id'];?>;
    if(!amount){
             $("#msg").html("<?php echo $entry_bids_error; ?>");
    } else if(!$.isNumeric(amount)) {
     $("#msg").html("<?php echo $entry_ammount_error; ?>");
    } else if(user==0) {
     $("#msg").html("<?php echo $entry_login_error; ?>");
    } else {
    $.ajax({
          type: 'post',
          url: 'index.php?route=module/wkproduct_auction/wkauctionbids',
          data: 'product_id='+product_id+'&amount='+amount+'&user='+user+'&auction='+auction+'&start_date='+start_date+'&end_date='+end_date,
          dataType: 'json',
          success: function(json) {
                if(json['success']) {
                    if(json['success']=='done'){
                       location.reload(); 
		    } else if(json['success']=='not') {
                       $("#msg").html("<?php echo $entry_ammount_less_error; ?>");
                    } else if (json['success']=='not_done') {
                       $("#msg").html("<?php echo $entry_ammount_range_error; ?>");
                    }
                } else {
                    $(".bidme").html('<div class="bids" style="font-size:11px;"><?php echo $entry_no_bids; ?></div>');
                }
          }
      });
   
  	}
  
 
});



</script>

<?php } }?>





   
    
