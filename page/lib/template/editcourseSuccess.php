<link rel="stylesheet" type="text/css" href="<?php echo STYLECSS; ?>">
<link type="text/css" href="<?php echo JQUERYUICSS; ?>" rel="stylesheet" />
<script type="text/javascript" src="<?php echo JQUERY; ?>"></script>
<script type="text/javascript" src="<?php echo JQUERYUI; ?>"></script>
<script type="text/javascript" src="<?php echo JQUERYNESTABLE; ?>"></script>

<?php if ($message): ?>
<div class="alert"><?php echo $message; ?></div>
<script>
	setTimeout(hidealert, 4000);
	function hidealert() {
		$('.alert').fadeOut('slow');
	}
</script>
<?php endif; ?>


<h2><?php echo get_string('titleEditCourse', 'format_page'); ?></h2>
<input id='sesskey' type='hidden' value='<?php echo $sesskey; ?>' />
<div class="myinfo">
    <img src="<?php echo TOOLBOX; ?>" width="70" height="70" class="img_left imgshadow" alt="outils" />
    <p style="margin-top:10px;"><?php echo get_string('helpEditCourse', 'format_page'); ?></p>
</div>
<div style="clear:both;"></div>

<div class="nestable-lists">
    <div class="dd" id="nestable">
        <ol class="dd-list">
            <?php echo $tree2; ?>	
        </ol>
    </div>
</div>

<div id="popup" style="display:none;"><?php echo $addmodule; ?></div>
<div id="popup_duplication" style="display:none;">
<p><?php echo get_string('commentChoosePage', 'format_page'); ?></p>
<form action="" method="post">
	<select id="select_page_duplication">
		<?php echo $pagestree; ?>	
	</select>
</form>
</div>
<div id="popup_delete" style="display:none;">
<p><?php echo get_string('warningDeleteModule', 'format_page'); ?></p>
</div>
<div id="popup_deletepage_forbidden" style="display:none;">
<p><?php echo get_string('warningDeletePage', 'format_page'); ?></p>
</div>


<h2><?php echo get_string('titleOrphansModules', 'format_page'); ?></h2>
<input id='sesskey' type='hidden' value='<?php echo $sesskey; ?>' />
<div class="myinfo">
<img src="<?php echo TOOLBOX; ?>" width="70" height="70" class="img_left imgshadow" alt="outils" />
<p style="margin-top:10px;"><?php echo get_string('helpOrphansModules', 'format_page'); ?></p>
</div>
<div id="orphansmodules" style="display:none;"></div>
<div style="text-align:center;margin:20px;">
	<form id="lookfororphans" action="" method="post">
	<input type="hidden" id="id" name="id" value="<?php echo $id; ?>" />
	<input type="hidden" name="sesskey" value="<?php echo $sesskey; ?>" />
	<span id="lookfor_orphans_span"><input id="lookfor_orphans" style="margin:5px;" type="submit" name="lookfor_orphans" value="<?php echo get_string('buttonOrphansModules', 'format_page'); ?>" /></span>
	</form>
</div>


<h2><?php echo get_string('titleSections', 'format_page'); ?></h2>
<input id='sesskey' type='hidden' value='<?php echo $sesskey; ?>' />
<div class="myinfo">
<img src="<?php echo TOOLBOX; ?>" width="70" height="70" class="img_left imgshadow" alt="outils" />
<p style="margin-top:10px;"><?php echo get_string('helpSections', 'format_page'); ?></p>
<p><strong><?php echo get_string('warning', 'format_page'); ?></strong><?php echo get_string('commentSections', 'format_page'); ?></p>
<p><strong><?php echo get_string('remark', 'format_page'); ?></strong><?php echo get_string('comment2Sections', 'format_page'); ?></p>
</div>
<div style="text-align:center;margin:20px;">
	<form id="deletesections" action="" method="post">
	<input type="hidden" id="id" name="id" value="<?php echo $id; ?>" />
	<input type="hidden" name="sesskey" value="<?php echo $sesskey; ?>" />
	<span id="delete_span"><input style="margin:5px;" id="suppress_sections" type="submit" name="suppress_sections" value="<?php echo get_string('buttonDeleteSections', 'format_page'); ?>" /></span>
	</form>
	<form id="associatesections" action="" method="post">
	<input type="hidden" id="id" name="id" value="<?php echo $id; ?>" />
	<input type="hidden" name="sesskey" value="<?php echo $sesskey; ?>" />
	<span id="associate_span"><input style="margin:5px;" id="associate_sections" type="submit" name="associate_sections" value="<?php echo get_string('buttonRelationSections', 'format_page'); ?>" /></span>
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

    _dragitem = new Array();

    // =================================
    //
    //  function called each time
    //  we want to move orphan MODULE in a page
    //
    // =================================
    move_orphan = function() {
        _parent = $(this).parent().parent();
        _moduleid = $(this).parent().parent().children('.id').html();
        $('#popup_duplication').dialog({
            closeOnEscape: true,
            width:'550px',
            buttons: { 
                'valider': function() {
                    $(this).dialog('close');
                    _pageid = $('#select_page_duplication :selected').attr('value');
                    $.ajax({
                        url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxmovemodule',
                        type: 'POST',
                        data: 'current='+_moduleid+'&pageid='+_pageid+'&sesskey=<?php echo $sesskey; ?>',
                        success: function(_data) {
                            if ($('#table_'+_pageid).children('tbody').length != 0) {
                                $('#item_'+_moduleid)
                                    .attr('id','item_'+_data)
                                    .find('.defineposition_radio')
                                    .each(function(_index){
                                        $(this).attr('id','radio'+_index+'_'+_data);
                                        $(this).attr('name','position_'+_data);
                                    })
                                    .parent()
                                    .parent()
                                    .appendTo('#table_'+_pageid+' tbody');
                                $('#item_'+_data)
                                    .find('.displaynone')
                                    .each(function(_index){
                                        $(this).removeClass('displaynone');
                                    });
                                 $('#item_'+_data)
                                    .find('.moveorphan')
                                    .each(function(_index){
                                        $(this).removeClass('moveorphan');
                                    });
                                 $('#item_'+_data).find('.duplicate').removeClass().addClass('duplicate');
                                 $('#item_'+_data).find('.showhideactivities').removeClass().addClass('showhideactivities');
                                 $('#item_'+_data).find('.deletemodule').removeClass().addClass('deleteitem');
                                 $('#item_'+_data).find('.defineposition_radio').removeClass().addClass('defineposition_radio');
                                 $('#item_'+_data).find('.duplicate').off().on('click', duplicate_item);
                                 $('#item_'+_data).find('.showhideactivities').off().on('click', showhide_activities);
                                 $('#item_'+_data).find('.defineposition_radio').off().on('click', defineposition_radio);
                                 $('#item_'+_data).find('.dd-handle2').off().on('mousedown', drag_item);
                                 $('#item_'+_data).find('.deleteitem').off().on('click', deleteitem);
                                 $('#item_'+_data).find('.id').html(_data);   
                                 if ($('#table_'+_pageid).children('tbody').find('.dd-empty').length != 0) {
                                     $('#table_'+_pageid).children('tbody').find('.dd-empty').parent().remove();
                                 }
                                 _module_title = $('#item_'+_data).find('.object_name').html(); 
                                 $('#item_'+_data).find('.object_name').html("<a href='modedit.php?update="+_moduleid+"&return=0'>"+_module_title+"</a>");
                            }
                            else {
                                _parent.fadeOut('slow').remove();
                            }
                        }
                    });									
                }
            },
            modal:true 
        });
    }

    // =================================
    //
    //  function called each time
    //  we mousedown on an item
    //
    // =================================
    
    drag_item = function() {
        _dragitem.push($(this).parent().children('.id').html());
    }

    // =================================
    //
    //  function called each time
    //  we want to hide or show a MODULE
    //
    // =================================
    showhide_activities = function() {
        _current = $(this).parent().parent().children('.id').html();
        _sesskey = $('#sesskey').attr('value');
        if ($(this).hasClass('hideactivity')) {
                $(this).attr('src','<?php echo EYE_OPENED; ?>');
        }
        else {
                $(this).attr('src','<?php echo EYE_CLOSED; ?>');
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
    }

    // =================================
    //
    //  function called each time
    //  we want to duplicate a MODULE
    //
    // =================================
    duplicate_item = function() {
        _current = $(this).parent().parent().children('.id').html();
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
                        success: function(_data) {
                            if ($('#table_'+_pageid).children('tbody').length != 0) {
                                $('#item_'+_current)
                                    .clone(true)
                                    .attr('id','item_'+_data)
                                    .find('.defineposition_radio')
                                    .each(function(_index){
                                        $(this).attr('id','radio'+_index+'_'+_data);
                                        $(this).attr('name','position_'+_data);
                                    })
                                    .parent()
                                    .parent()
                                    .appendTo('#table_'+_pageid+' tbody');
                                $('#item_'+_data)
                                    .find('.id')
                                    .html(_data);
                            }
                        }
                    });									
                }
            },
            modal:true 
        });                                        
    }
    
    // =================================
    //
    //  function called each time
    //  we want to modify a MODULE position (left column, center column or right column)
    //
    // =================================
    
    defineposition_radio = function() {
        _position = $(this).attr('value');
        _current = $(this).parent().parent().children('.id').html();
        _sesskey = $('#sesskey').attr('value');
        $.ajax({
            url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxactivityposition',
            type: 'POST',
            data: 'current='+_current+'&position='+_position+'&sesskey='+_sesskey,
            success: function(data) {
                    console.log(data);
            }
        });	
    }

    // =================================
    //
    //  function called each time
    //  we want to delete a MODULE
    //
    // =================================
    delete_module = function() {
        _parent = $(this).parent().parent();
        _sesskey = $('#sesskey').attr('value');        
        _current = $(this).parent().parent().children('.id').html();
        $('#popup_delete').dialog({
            closeOnEscape: true,
            width:'550px',
            buttons: { 
                'confirmer la suppression': function() {
                    $(this).dialog('close');
                    $.ajax({
                        url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxdeletemodule',
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
    
    
    }

    // =================================
    //
    //  function called each time
    //  we want to delete an ITEM
    //
    // =================================
    deleteitem = function() {
        
        _parent = $(this).parent().parent();
        _tbody = $(this).parent().parent().parent();
        _current = $(this).parent().parent().children('.id').html();
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
                        success: function(_data) {
                            if ((_data == "context_null")||(_data == "invalid_token")) return;
                            if (_data != "do_nothing") {
                                if ($('#orphansmodules').children('table').length != 0) {
                                    $('#item_'+_current).appendTo('#orphansmodules table');
                                    $('#item_'+_current).attr('id','item_'+_data);
                                    $('#item_'+_data).find(".showhideactivities").parent().addClass("displaynone");
                                    $('#item_'+_data).find(".defineposition_radio").parent().addClass("displaynone");
                                    $('#item_'+_data).find(".dd-handle2").addClass("displaynone");
                                    $('#item_'+_data).find(".duplicate").off().on('click',move_orphan);
                                    $('#item_'+_data).find(".deleteitem").removeClass('deleteitem').addClass('deletemodule');
                                    $('#item_'+_data).find(".deletemodule").off().on('click',delete_module);
                                    $('#item_'+_data)
                                        .find('.defineposition_radio')
                                        .each(function(_index){
                                            $(this).attr('id','radio'+_index+'_'+_data);
                                            $(this).attr('name','position_'+_data);
                                        });
                                    $('#item_'+_data).find('.id').html(_data); 
                                    _module_title = $('#item_'+_data).find('.object_name a').html();
                                    $('#item_'+_data).find('.object_name').html(_module_title);
                                }
                                else {
                                    _parent.fadeOut('slow').remove();
                                }                                
                               
                            }
                            else {
                                _parent.fadeOut('slow').remove();
                            }

                            if (_tbody.children('.dd-item').length == 0) {
                                _tbody.append("<tr><td class='dd-empty' colspan='7'></td></tr>")
                            } 
                        }
                    });									
                }
            },
            modal:true 
        });
    }

    // =================================
    //
    //  function called each time
    //  a MODULE or a PAGE is dropped down
    //
    // =================================
    var updateOutput = function(e)
    {
        if (e.target.id == this.id){
            if ((this.id == 'nestable') && (_dragitem[0] != undefined)) {
                //console.log("déplacement de la page "+_dragitem[0]);
                //console.log("previous = "+$("#page_"+_dragitem[0]).prev().children(".input_course").attr('value'));
                //console.log("parent = "+$("#page_"+_dragitem[0]).parent().parent().children(".input_course").attr('value'));
		_parent = $("#page_"+_dragitem[0]).parent().parent().children(".input_course").attr('name');
		_previous = $("#page_"+_dragitem[0]).prev().children(".input_course").attr('name');
		_current = _dragitem[0];
		$.ajax({
			url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxmove',
			type: "POST",
			data: "parent="+_parent+"&previous="+_previous+"&current="+_current+"&id=<?php echo $id; ?>"+"&sesskey=<?php echo $sesskey; ?>",
			success: function(data) {
				console.log(data);
			}
		}); 
            }
            else {
                if (_dragitem[0] != undefined) {
                    //console.log("déplacement d'un module : " + _dragitem[0]);
                    _module = "#item_" + _dragitem[0];
                    _current = _dragitem[0];
                    _previous = $(_module).prev().children(".id").html();
                    _parent = $(_module).parent().parent().parent().children(".input_course").attr('name');
                    //console.log("Page mère destination = " + _parent);
                    //console.log("Module précédent = " + _previous);
                    $.ajax({
                            url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxmovemodule',
                            type: "POST",
                            data: "previous="+_previous+"&current="+_current+"&pageid="+_parent+"&sesskey=<?php echo $sesskey; ?>",
                            success: function(data) {
                                    console.log(data);
                            }
                    });                     
                    
                    
                }
            }
            _dragitem.length = 0;
        }
    };
    // =================================
    //
    //  function called at mousedown
    //  on a PAGE
    //
    // =================================
    var dragitem = function(e)
    {
        _dragitem.push($(this).parent().children(".input_course").attr('name'));
    };
    $('.dd-handle').bind('mousedown', dragitem);

    // =================================
    //
    //  Init the sort tool
    //
    // =================================

    $('#nestable').nestable({
        maxDepth    : 3
    }).on('change', updateOutput);

    $(".modules_table").nestable({
        listNodeName    : 'tbody',
        itemNodeName    : 'tr',
        rootClass       : 'dd',
        listClass       : 'dd-list-table',
        handleClass     : 'dd-handle2',
        maxDepth        : 1,
        group           : 1
    }).on('change', updateOutput);
        

    // =================================
    //
    //  delete all sections from the database
    //
    // =================================
    $('#deletesections').submit(function() {
            $("#delete_span").html("<img style='margin:10px;' src='<?php echo AJAX_LOADER; ?>' alt='loader' />");
            $.ajax({
                    url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxdeletesections',
                    type: "POST",
                    data: "current=<?php echo $id; ?>&sesskey=<?php echo $sesskey; ?>",
                    success: function(data) {
                            if (data=='done') {
                                    document.location.href= "view.php?id=<?php echo $id; ?>&action=editcourse&sesskey=<?php echo $sesskey; ?>&suppress_sections=1";
                            }
                    }
            });
            return false;
    });
    // =================================
    //
    //  synchronize sections and pages
    //
    // =================================
    $('#associatesections').submit(function() {
        $("#associate_span").html("<img style='margin:10px;' src='<?php echo AJAX_LOADER; ?>' alt='loader' />");
        $.ajax({
            url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxassociatesections',
            type: "POST",
            data: "current=<?php echo $id; ?>&sesskey=<?php echo $sesskey; ?>",
            success: function(data) {
                if (data=='done') {
                    document.location.href= "view.php?id=<?php echo $id; ?>&action=editcourse&sesskey=<?php echo $sesskey; ?>&associate_sections=1";
                }
            }
        });
        return false;
    });

    // =================================
    //
    //  toggle the visibility of a PAGE
    //
    // =================================
    $(".showhide").click(function(ev) {
            ev.preventDefault();
            _current = $(this).parent().children(".input_course").attr('name');

            if ($(this).hasClass("hidepage")) {
                    $(this).attr("src", "<?php echo EYE_CLOSED; ?>");
            }
            else {
                    $(this).attr("src", "<?php echo EYE_OPENED; ?>");
            }
            $(this).toggleClass("hidepage");
            $.ajax({
                    url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxdisplay',
                    type: "POST",
                    data: "current="+_current+"&sesskey=<?php echo $sesskey; ?>",
                    success: function(data) {
                            console.log(data);
                    }
            });	
    });
    // =================================
    //
    //  toggle button to edit the name of the current page
    //
    // =================================
    $(".edit_title").click(function(ev) {
        if ($(this).attr('src') == '<?php echo EDIT; ?>') {
            $(this).attr('src','<?php echo EDIT_OK; ?>');
            $(this).parent().children('.input_course').css({'background-color': '#F0E0FF','cursor':'text'});
        }
        else {
            $(this).attr('src','<?php echo EDIT; ?>');
            $(this).parent().children('.input_course').css({'background-color': '#FFFFFF','cursor':'pointer'});
            $(this).parent().children('.input_course').effect("highlight", {}, 3000);
            _name = $(this).parent().children('.input_course').attr('value');
            _current = $(this).parent().children('.input_course').attr('name');
            _sesskey = $("#sesskey").attr('value');
            $.ajax({
                url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxrename',
                type: "POST",
                data: "current="+_current+"&name="+_name+"&sesskey="+_sesskey,
                success: function(data) {
                        console.log(data);
                        /**
                         * @TODO : update popup_duplication list
                         */ 
                }
            });	            
        }
    });
     // =================================
    //
    //  If edit mode is active, edit title
    //  else go to the current PAGE
    //
    // =================================
    $(".input_course").on('mousedown',function(ev) {
        if ($(this).parent().children('.edit_title').attr('src') == '<?php echo EDIT; ?>') {
            ev.preventDefault();
            _current = $(this).attr('name');
            document.location.href= "view.php?id=<?php echo $id; ?>&page="+_current;
        }
    
    } );  
    // =================================
    //
    //  record name of the PAGE if we press enter
    //
    // =================================
    $(".input_course").keypress(function(ev) {
        if(ev.keyCode == 13) {
            $(this).css({'background-color':'rgb(255,255,255)','cursor':'pointer'});
            $(this).effect("highlight", {}, 3000);
            $(this).parent().children('.edit_title').attr('src','<?php echo EDIT; ?>');
            _name = $(this).attr('value');
            _current = $(this).attr('name');
            _sesskey = $("#sesskey").attr('value');
            $.ajax({
                url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxrename',
                type: "POST",
                data: "current="+_current+"&name="+_name+"&sesskey="+_sesskey,
                success: function(data) {
                        console.log(data);
                        // TODO : update popup_duplication list
                }
            });			
        }
    });
    // =================================
    //
    //  show MODULES and BLOCKS of the specified page
    //
    // =================================    
    $(".dd-item").children(".showactivities").click(function(ev) {
            ev.preventDefault();
            _current = $(this).parent().children(".input_course").attr('name');
            _container = $(this).parent().children(".dd");
            if (_container.hasClass("hideactivities")) {
                
                if (_container.children(".dd-list-table").length) {
                    // Wrap table into a div to make slidedown effect working
                    // the div wrapper is removed at the end of this effect
                    _container.wrap("<div style='width:100%;' class='table-wrapper'/>");
                    _container.parent().hide();
                    _container.show();                         
                    _container.parent().slideDown("slow", 'swing', function(){
                        _container.unwrap();                                            
                    });
                }
                else {
                    $.ajax({
                        url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxshowactivities',
                        type: "POST",
                        data: "current="+_current+"&sesskey=<?php echo $sesskey; ?>",
                        success: function(data) {
                            _container.children(".dd-list-table").remove();
                            _container.append(data);
                            // Wrap table into a div to make slidedown effect working
                            // the div wrapper is removed at the end of this effect
                            _container.wrap("<div style='width:100%;' class='table-wrapper'/>");
                            _container.parent().hide();
                            _container.show();                         
                            _container.parent().slideDown("slow", 'swing', function(){
                            _container.unwrap();                        
                            });
                        }
                    });
                }
            }
            else {
                _container.fadeOut('slow');
            }
            _container.toggleClass("hideactivities");
    });

    // =================================
    //
    //  just remember the page where we want to add a new module
    //  and then give hand to moodle core
    //
    // =================================
    $(".addmodule").click(function(ev) {
        ev.preventDefault();
        _current = $(this).parent().children(".input_course").attr('name');
        $.ajax({
                url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxaddnewmodule',
                type: "POST",
                data: "current="+_current+"&sesskey=<?php echo $sesskey; ?>",
                success: function(data) {
                        //
                }
        });
        _name = $(this).parent().children(".input_course").attr('value');
        $("#popup_title").html(_name);
        $("#popup").dialog({
                title: _name,
                closeOnEscape: true,
                modal:true 
        });

        $(".section-modchooser-link a").click(function(ev){
                $("#popup").dialog("destroy");
        });

    });
    
    // =================================
    //
    //  Delete a PAGE if it is empty
    //
    // =================================    
    $(".deletepage").click(function(ev) {
        ev.preventDefault();
        _current = $(this).parent().children(".input_course").attr('name');
        _parent = $(this).parent();
        $.ajax({
                url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxdeletepage',
                type: "POST",
                data: "current="+_current+"&sesskey=<?php echo $sesskey; ?>",
                success: function(data) {
                    $("#popup_deletepage_forbidden").html(data);
                    if (data != 'done') {
                        $("#popup_deletepage_forbidden").dialog({
                                closeOnEscape: true,
                                modal:true 
                        });				
                    }
                    else {
                        _parent.fadeOut('slow');
                    }
                }
        });

    });
    // =================================
    //
    //  Toggle the visibility of the arrow previous page
    //
    // =================================
    $(".linkpreviouspage").click(function(ev) {

        ev.preventDefault();
        _current = $(this).parent().children(".input_course").attr('name');
        if ($(this).hasClass('showlink')) {
                $(this).attr('src','<?php echo PREVIOUS_DISABLED; ?>');
        }
        else {
                $(this).attr('src','<?php echo PREVIOUS_ENABLED; ?>');
        }
        $(this).toggleClass('showlink');
        $.ajax({
                url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxlinkpages',
                type: 'POST',
                data: 'current='+_current+'&link=previous&sesskey=<?php echo $sesskey; ?>',
                success: function(data) {
                        //
                }
        });
    });
    // =================================
    //
    //  Toggle the visibility of the arrow next page
    //
    // =================================
    $(".linknextpage").click(function(ev) {
            ev.preventDefault();
            _current = $(this).parent().children(".input_course").attr('name');
            if ($(this).hasClass('showlink')) {
                    $(this).attr('src','<?php echo NEXT_DISABLED; ?>');
            }
            else {
                    $(this).attr('src','<?php echo NEXT_ENABLED; ?>');
            }
            $(this).toggleClass('showlink');
            $.ajax({
                    url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxlinkpages',
                    type: 'POST',
                    data: 'current='+_current+'&link=next&sesskey=<?php echo $sesskey; ?>',
                    success: function(data) {
                            //
                    }
            });
    });
    // =================================
    //
    //  look for orphans MODULES in this course
    //
    // =================================	
    $('#lookfororphans').submit(function() {
            $("#lookfor_orphans_span").html("<img style='margin:10px;' src='<?php echo AJAX_LOADER; ?>' alt='loader' />");
            $.ajax({
                    url: '/course/format/<?php echo $maindir; ?>/ajax.php?action=ajaxlookfororphans',
                    type: "POST",
                    data: "current=<?php echo $id; ?>&sesskey=<?php echo $sesskey; ?>",
                    success: function(data) {
                            $("#orphansmodules").html(data);
                            $("#orphansmodules").show();
                            $("#lookfor_orphans_span").hide();
                    }
            });
            return false;
    });	
});
</script>

<div style="clear:both;"></div>
