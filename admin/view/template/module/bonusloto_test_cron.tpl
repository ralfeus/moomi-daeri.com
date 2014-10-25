<style>
.crontable{
border-color: gray;
border-bottom-style:solid;
border-bottom-width: 1px;
}
.cronJobEntry{
border-bottom-style:solid;
border-color: gray;
border-bottom-width: 1px;
}

</style>
<table class="testCron">
	<tr>
		<td colspan="2"><h5>System information:</h5></td>
	</tr>
	<tr>
		<td class="crontable">shell_exec() function status:</td>
		<td ><strong><p class="<?php echo $shell_exec_status ?>"><?php echo ($shell_exec_status=='Enabled') ? '<span style="color:#4CC417">Enabled</span>' : '<span style="color:#F70D1A">Disabled</span>'; ?></strong></td>
	</tr>
		<tr>
			<td class="crontable"><?php  echo $cron_folder;?></td>
			<td ><strong><p class="<?php echo $folder_permission ?>"><?php echo ($folder_permission=='Writable') ? '<span style="color:#4CC417">Writable</span>' : '<span style="color:#F70D1A">Unwritable</span>'; ?></strong></td>
		</tr>
	<tr>
		<td class="crontable">Cron job status:</td>
		<td ><strong><p class="<?php echo $cronjob_status ?>"><?php echo ($cronjob_status=='Enabled') ? '<span style="color:#4CC417">Enabled</span>' : '<span style="color:#F70D1A">Disabled</span>'; ?></strong></td>
	</tr>
	<?php if ($shell_exec_status='Enabled' && $folder_permission=='Writable' && $cronjob_status=='Enabled') { ?>
        <tr>
            <td colspan="2" ><h5>Current cron jobs:</h5></td>
        </tr>
        <?php if(isset($current_cron_jobs)) { 
                foreach($current_cron_jobs as $cron_job) { 
                    if(!empty($cron_job)) { ?>
                        <tr><td colspan="2"><p class="cronJobEntry"><?php  echo $cron_job; ?></td></tr>
        <?php }   }	} ?>
    <?php } ?>
</table>