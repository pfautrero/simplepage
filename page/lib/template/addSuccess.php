<link type="text/css" href="<?php echo JQUERYUICSS; ?>" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLECSS; ?>">
<script type="text/javascript" src="<?php echo JQUERY; ?>"></script>
<script type="text/javascript" src="<?php echo JQUERYUI; ?>"></script>

<?php if ($message): ?>
<div class="alert"><?php echo $message; ?></div>
<?php endif; ?>
<h2>Ajouter une nouvelle page</h2>

<div class="myinfo">
    <img src="format/page/lib/template/images/addpage.jpg" width="70" height="70" class="img_left imgshadow" alt="bac sable" />
    <form action="view.php?id=<?php echo $id; ?>" method="post">
    <input id='sesskey' name='sesskey' type='hidden' value='<?php echo $sesskey; ?>' />
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <p>
        <span>Nom de la page : </span>
        <span><input style="padding:3px;" size="40" name="page_name" type="textbox"/></span>
    </p>
    <p>
        <span>Page parente : </span>
        <span>
        <select style="padding:3px;border:2px solid black;" name="pageparente">
            <option value="0" selected></option>
            <?php foreach ($pagesparentes as $pageparente): ?>
                    <?php if ($pageparente['level'] == 0): ?>
                    <option style="margin-left:3px;margin-right:3px;" value="<?php echo $pageparente['id']; ?>"><?php echo $pageparente['name']; ?></option>
                    <?php else: ?>
                    <option style="background:url('/pix/y/ln.gif') no-repeat;margin-left:3px;margin-right:3px;padding-left:20px;" value="<?php echo $pageparente['id']; ?>"><?php echo $pageparente['name']; ?></option>
                    <?php endif; ?>
            <?php endforeach; ?>
        </select>
        </span>
    </p>
    <div style="width:146px;margin:0 auto;padding:10px;">
        <input class="addpage" type="submit" name="addpage" value=""/>
    </div>
    </form>
</div>

<script>

$(function() {

    if ($("#region-post").length != 0) {
        $("#region-post").prepend("<?php echo $adminBlock; ?>");        
    }
    else {
        $("#region-main").prepend("<div style='margin:10px;float:right;width:300px;'><?php echo $adminBlock; ?></div>");
    }
});

</script>