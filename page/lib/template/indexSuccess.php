<link type="text/css" href="<?php echo JQUERYUICSS; ?>" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLECSS; ?>">
<script type="text/javascript" src="<?php echo JQUERY; ?>"></script>
<script type="text/javascript" src="<?php echo JQUERYUI; ?>"></script>
<div class='format_content' style='width:100%;'>
<input id='sesskey' type='hidden' value='<?php echo $sesskey; ?>' />

    <?php if ($message): ?>
    <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

<div style="display:none;"><a href="#contenu">Aller au contenu</a></div>
<?php echo $tabs; ?>
<div id="contenu"></div>



    <div style="clear:both;"></div>

    <!-- <a class="page_pdf_url"  href="/course/format/<?php echo $maindir; ?>/ajax.php?id=<?php echo $id; ?>&page=<?php echo $pageid; ?>&sesskey=<?php echo $sesskey; ?>&action=pdf">
    <img class="page_pdf" src="<?php echo PDF; ?>" alt='impression pdf' title='impression pdf de la page' />
    </a> -->
    <div id="pdf_alert" class="alert" style="display:none;">Veuillez patienter...</div>

    <?php if ($rightColumn): ?>
    <div class='center' style='float:left;width:78%;'>
    <?php echo $centerColumn; ?>
    </div>		
    <div class='right' style='float:right;width:20%;'>
    <?php echo $rightColumn; ?>
    </div>

    <?php else: ?>
    <div class='center' style='width:98%;'>
    <?php echo $centerColumn; ?>
    </div>			
    <?php endif; ?>

    <div style='clear:both;'></div>

    <?php if ($editing) : ?>
    <div class="addnewmodule addmodule_page">Ajouter un module</div>
    <div style='clear:both;'></div>
    <?php endif; ?>

    <?php if ($prev_page): ?>
    <div class="bottom_button_left">
    <?php echo "<a href='view.php?id=$id&page=$prev_page'>page précédente</a>"; ?>
    </div>
    <?php endif; ?>

    <?php if ($next_page): ?>
    <div class="bottom_button_right">
    <?php echo "<a href='view.php?id=$id&page=$next_page'>page suivante</a>"; ?>
    </div>	
    <?php endif; ?>	
	
</div>	
<div id="popup_delete" style="display:none;">
<p>Vous êtes sur le point de supprimer une activité, une ressource ou un bloc. La suppression ne concerne que la page visée. S'il existe d'autres instances de ce même item, elles seront conservées. Dans le cas contraire, l'item sera définitivement supprimé.</p>
</div>
<div id="popup_duplication" style="display:none;">
<p>Choisissez la page dans laquelle vous voulez afficher ce module :</p>
<form action="" method="post">
    <select id="select_page_duplication">
        <?php echo $pagestree; ?>	
    </select>
</form>
</div>
<div id="popup" style="display:none;">
    <?php echo $addmodule; ?>
    <h5>Position du module :</h5>
    <div class="radio" style="width:300px;margin:0px auto;">
        <input class="radiobutton" name="resource" id="leftposition" type="radio"  /><label style="vertical-align:35%;padding-right:20px;" title="Gauche"  for="4">Gauche</label>
        <input class="radiobutton" name="resource" id="centerposition" type="radio" checked="checked" /><label title="Centre" style="vertical-align:35%;padding-right:20px;" for="5">Centre</label>
        <input class="radiobutton" name="resource" id="rightposition" type="radio" /><label title="Droite" style="vertical-align:35%;" for="6">Droite</label>
    </div>
</div>
<script>

$(document).ready(function() {
	
	// ================= Add modules in the right and left columns
	if ($("#region-pre").length != 0) {
		$("#region-pre").prepend("<?php echo $leftColumn; ?>");
	}
	else {
            $("#region-main").prepend("<div style='float:left;width:300px;'><?php echo $leftColumn; ?></div>");
	}

        if ($("#region-post").length != 0) {
            $("#region-post").prepend("<?php echo $adminBlock; ?>");        
        }
        else {
            $("#region-main").prepend("<div style='margin:10px;float:right;width:300px;'><?php echo $adminBlock; ?></div>");
        }

	$('.squaredOne').mouseup(function(ev) {
            //ev.preventDefault();
            _current = $(this).children(':checkbox').attr('id');
            $.ajax({
                url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxassignment',
                type: 'POST',
                data: 'current='+_current+'&sesskey=<?php echo $sesskey; ?>',
                success: function(data) {
                        //
                }
            });
	});            
            
        <?php if ($editing) : ?>
	
	
	$('.radiobutton').click(function(ev) {	
		_id = $(this).attr('id');
		_sesskey = $('#sesskey').attr('value');
		$.ajax({
			url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxmoduleposition',
			type: 'POST',
			data: 'position='+_id+'&sesskey='+_sesskey,
			success: function(data) {
				//
			}
		});		
	});
	
	// ================== Handle ajax requests
	$('.showhideactivities').click(function(ev) {
            ev.preventDefault();
            _current = $(this).parent().parent().children('.input_id').attr('name');
            _sesskey = $('#sesskey').attr('value');
            if ($(this).hasClass('hideactivity')) {
                    $(this).attr('src','<?php echo EYE_OPENED ?>');
            }
            else {
                    $(this).attr('src','<?php echo EYE_CLOSED ?>');
            }
            $(this).toggleClass('hideactivity');
            $.ajax({
                    url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxshowactivity',
                    type: 'POST',
                    data: 'current='+_current+'&sesskey='+_sesskey,
                    success: function(data) {
                            //
                    }
            });
	});
	
	// ====== Avoid replay during 10 sec
	_generatePdf = false;
	$('.page_pdf_url').click(function() {
		if (!_generatePdf) {
			_generatePdf = true;
			$("#pdf_alert").fadeIn('slow');
			setTimeout(hidealert, 10000);
			return true;
		}
		return false;
		
	});	
	function hidealert() {
		_generatePdf = false;
		$('.alert').fadeOut('slow');
	}
	
	$('.deleteitem').click(function(ev) {
		ev.preventDefault();
		_parent = $(this).parent().parent();
		_current = $(this).parent().parent().children('.input_id').attr('name');
		_sesskey = $('#sesskey').attr('value');
		$('#popup_delete').dialog({
			closeOnEscape: true,
			width:'550px',
			buttons: { 
				'confirmer la suppression': function() {
					$(this).dialog('close');
					$.ajax({
						url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxdeleteitem',
						type: 'POST',
						data: 'current='+_current+'&sesskey='+_sesskey,
						success: function(data) {
							_parent.fadeOut('slow');
						}
					});									
				}
			},
			modal:true 
		});
	});	
	
	$('.duplicate').click(function(ev) {
            ev.preventDefault();
            _current = $(this).parent().parent().children('.input_id').attr('name');
            _sesskey = $('#sesskey').attr('value');
            $('#popup_duplication').dialog({
                closeOnEscape: true,
                width:'550px',
                buttons: { 
                    'valider': function() {
                        $(this).dialog('close');
                        _pageid = $('#select_page_duplication :selected').attr('value');

                        $.ajax({
                            url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxduplicate',
                            type: 'POST',
                            data: 'current='+_current+'&pageid='+_pageid+'&sesskey='+_sesskey,
                            success: function(data) {
                                    //
                            }
                        });									
                    }
                },
                modal:true 
            });
	});

	$('.addnewmodule').click(function(ev) {
            ev.preventDefault();
            $.ajax({
                url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxaddnewmodule',
                type: 'POST',
                data: 'current=<?php echo $pageid; ?>&sesskey=<?php echo $sesskey; ?>',
                success: function(data) {
                        //
                }
            });
            $('#popup').dialog({
                closeOnEscape: true,
                width:'400px',
                modal:true
            });

            $(".section-modchooser-link a").click(function(ev){
                    $("#popup").dialog("destroy");
            });
	});	
		

	<?php endif ?>	

});

</script>
