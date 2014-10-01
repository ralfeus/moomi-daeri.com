<style type="text/css">
#content .breadcrumb{
display:none;
}
#content h1{
display:none;
}
#content .content{
display:none;
}
#content .buttons{
display:none;
}

</style>

<?php echo $header; ?>
<?php echo $column_left; ?><?php echo $column_right; ?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("ul#mpauctionul").quickPagination({pageSize:"12"});
});
</script>

<div class="box mp_auction" id="daily_deal_block_center">

  <div class="mpauction-heading"><?php echo $heading_title; ?></div>

  <div class="mpauction-content" id="dmmod">
  
  <div id="leftcontent">
 
	<ul id="mpauctionul">	
     <?php	 
	   if($allauctions){	   
	     $i = 0;	
	  foreach ($allauctions as $product) { 
	   ?>	  	  
	 	 <li class="mpauction_product">
		 			
			<a class="product_image" title="<?php echo $product['name']; ?>" href="index.php?route=product/product&product_id=<?php echo $product['product_id']; ?>"><img width="125" height="95" alt="<?php echo $product['name']; ?>" src="<?php echo HTTP_SERVER.'image/'.$product['image']; ?>"></a>
			
			<h3><a title="<?php echo $product['name']; ?>" href="index.php?route=product/product&product_id=<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></a></h3>
			
			<div id="mpauction_content">
				<div class="Countdown" id="count<?php echo $i; ?>"> to Go</div>
			</div>
			
			
	
			  <br /><span class="price"><?php echo $product['aumin']; ?></span><span class="price"> to <?php echo $product['aumax']; ?></span><br />
	
			  
			<div class="cart"><input type="button" value="Bid Now" onclick="addTobid('<?php echo HTTP_SERVER.'index.php?route=product/product&product_id='.$product['product_id']; ?>');" class="button" /></div>
			<?php 
				$sa=explode(" ",$product['auend']);
			 	$dat=explode("-",$sa[0]);
   				$tim=explode(":",$sa[1]);

   				?>
			<script type="text/javascript">
			function expireproduct() { 
				location.reload(); 
			}
			jQuery(function () {
				
   				var austDay = new Date(<?php echo $dat[0].','.$dat[1].'-1'.','.$dat[2].','.$tim[0].','.$tim[1].','.$tim[2]; ?>);
 					
				jQuery('#count<?php echo $i; ?>').Wkcountdown({until: austDay,compact: true, format: 'dHMS', description: '',compactLabels : ['y', 'm', 'w', ' days '],onExpiry: expireproduct});
			});

			
			

			</script>
		
		</li>	

      <?php
	   $i++; 
	  	}		 
	  }else{
	  ?>
	  <div id="noproduct"><?php echo $entry_empty; ?></div>
	  <?php 
	  }
	?>
	</ul>
	</div>
	</div>
	</div>

<script type="text/javascript">
			function addTobid(saas) { 
				this.document.location.href = saas;
			}
			
</script>


	<?php echo $content_bottom; ?>
<?php echo $footer; ?>