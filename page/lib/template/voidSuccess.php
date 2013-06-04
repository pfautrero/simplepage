<link type="text/css" href="<?php echo JQUERYUICSS; ?>" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLECSS; ?>">
<script type="text/javascript" src="<?php echo JQUERY; ?>"></script>
<script type="text/javascript" src="<?php echo JQUERYUI; ?>"></script>
<div class='format_content' style='width:100%;'>
</div>
<script>
$(document).ready(function() {
	<?php if ($editing) : ?>
	// ============== Add admin block
        if ($("#region-post").length != 0) {
            $("#region-post").prepend("<?php echo $adminBlock; ?>");        
        }
        else {
            $("#region-main").prepend("<div style='margin:10px;float:right;width:300px;'><?php echo $adminBlock; ?></div>");
        }	
	<?php endif ?>	

});
</script>