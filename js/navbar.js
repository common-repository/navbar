// setup everything when document is ready
jQuery(document).ready(function($) {
	
	//iFrame Fix
	if (window.top.location != window.self.location)
	{
        return false;
    }
    
    $("body").prepend('<div id="navbar"><div id="navbar-info"><h4>' + NavBarSettings['blog_name'] + '</h4><br />' + NavBarSettings['info'] + '<h4>Menu</h4></div><hr class="navbar-separator" /><div id="navbar-sortable"></div><hr class="navbar-separator" /><div id="navbar-add-link-dialog" title="Add Link"><p>To add new shortcuts, simply drag and drop any link from the page to your NavBar. Additionally you can enter any URL below.</p><form><fieldset><label for="navbar-name">Name</label><input type="text" name="navbar-name" id="navbar-name" class="text ui-widget-content ui-corner-all" /><label for="navbar-url">URL</label><input type="text" name="navbar-url" id="navbar-url" class="text ui-widget-content ui-corner-all" /></fieldset></form></div><a id="navbar-add-link" class="ui-state-default ui-corner-all">Add Shortcut</a><a id="navbar-add-separator" class="ui-state-default ui-corner-all">Add Separator</a><div id="navbar-edit-links-dialog" title="Edit Links"><form><fieldset></fieldset></form></div><a id="navbar-edit-links" class="ui-state-default ui-corner-all">Edit Shortcuts</a><div id="navbar-trash" class="ui-widget-content ui-state-default ui-corner-all"><h4 class="ui-widget-header ui-corner-all"><span class="ui-icon ui-icon-trash">Trash</span> Trash</h4></div></div>');

    $("#navbar").addClass(
        "navbar-" + NavBarSettings['position']
    );
    
    if (NavBarSettings['hide'])
    {
        $("#navbar").css("display", "none");
    }
    else
    {
        $("body").css("margin-" + NavBarSettings['position'], NavBarSettings['width'] + "px");
    }    

    $("#navbar-trash, #navbar-add-link, #navbar-add-separator, #navbar-edit-links").css(
        "width",
        (parseInt(NavBarSettings['width']) - 20) + "px"
    );
    
    //Google Chrome Fix
    $("#navbar-trash, #navbar-add-link, #navbar-add-separator, #navbar-edit-links").css(
        NavBarSettings['position'],
        "0px"
    );
    
    $("#navbar").css(
        "backgroundColor",
        NavBarSettings['bgcolor']
    );
    
    $("#navbar").css("width", NavBarSettings['width'] + "px");
    
	if (NavBarSettings['links'])
    {
    	$.each(
            NavBarSettings['links']['name'], 
            function(key, value)
            {
                add_link(value, NavBarSettings['links']['url'][key]);
            }
        );
	}
	
	$("a").not("#navbar a").draggable({
        
        connectToSortable:  '#navbar-sortable',
        helper:             'clone',
        revert:             'invalid',
        start:              function(event, ui)
                            {
                                $("#navbar-sortable").addClass('ui-state-highlight');
                            },
        stop:               function(event, ui)
                            {
                                $("#navbar-sortable").removeClass('ui-state-highlight');
                            }
    });
    
    $(".navbar-separator").not("#navbar-sortable hr").draggable({
        cursor:             'pointer',
        connectToSortable:  '#navbar-sortable',
        helper:             'clone',
        revert:             'invalid',
        start:              function(event, ui)
                            {
                                $("#navbar-sortable").addClass('ui-state-highlight');
                            },
        stop:               function(event, ui)
                            {
                                $("#navbar-sortable").removeClass('ui-state-highlight');
                            }
    });    
    
    $("#navbar-sortable").sortable({
        revert:         true,
        items:          'a, hr',
        placeholder:    'ui-state-highlight',
        update:         function(event, ui) 
                        {
                            save_list();
                        },
        receive:        function(event, ui)
                        {
                            $(this).find("a").each(
                                function()
                                {
                                    $(this).removeAttr(
                                        "class"
                                    ).removeAttr(
                                        "tabIndex"
                                    ).addClass(
                                        "ui-state-default ui-corner-all"
                                    ).html(
                                        strip_tags($(this).html())
                                    );
                                }
                            );
                        },
        over:           function(event, ui)
                        {
                            $("#navbar-sortable").removeClass('ui-state-highlight');
                        }
    });
    
    $("#navbar").resizable({
        handles:    (NavBarSettings['position'] == "left" ? "e" : "w"),
        autoHide:   true,
        resize:     function(event, ui)
                    {
                        $("body").css(
                            "margin-" + NavBarSettings['position'], 
                            $(this).width()
                        );
                        
                        $("#navbar-trash, #navbar-add-link, #navbar-add-separator, #navbar-edit-links").css(
                            "width",
                            ($(this).width() - 20) + "px"
                        );
                        
                        //Google Chrome Fix
                        $("#navbar").css(
                            NavBarSettings['position'] == "left" ? "right":"left",
                            ""
                        );
                    },
        stop:       function(event, ui)
                    {
                        $.post(
                            NavBarSettings.url + "/navbar-ajax.php",
                            {
                                'type':         'width',
                                'width':        $(this).width(),
                                '_ajax_nonce':  NavBarSettings.nonce
                            }
                        );
                        
                        NavBarSettings['width'] = $(this).width();
                        
                        //Google Chrome Fix
                        $("#navbar").css(
                            NavBarSettings['position'] == "left" ? "right":"left",
                            ""
                        );
                    }
    });

	$("#navbar-add-link-dialog").dialog({
	bgiframe:  true,
	autoOpen:  false,
	modal:     true,
	resizable: false,
	buttons:   {
                    'Add Link': function() 
                                {
                                    add_link($("#navbar-name").val(), $("#navbar-url").val());
                                    save_list();
                                    
                                    $(this).dialog('close');
                                },
                    'Cancel':   function() 
                                {
                        			$(this).dialog('close');
                        		}
		      }
	});	
		
	$('#navbar-add-link').click(
        function()
        {
            $('#navbar-add-link-dialog').dialog('open');
        }
    ).hover(
        function()
        { 
			$(this).addClass("ui-state-hover"); 
		},
		function()
        { 
			$(this).removeClass("ui-state-hover"); 
		}
	).mousedown(
        function()
        {
			$(this).addClass("ui-state-active"); 
		}
    ).mouseup(
        function()
        {
			$(this).removeClass("ui-state-active");
		}
    );

	$("#navbar-edit-links-dialog").dialog({
	bgiframe:  true,
	autoOpen:  false,
	modal:     true,
	resizable: true,
	height:    400,
	buttons:   {
                    'Edit Links':   function() 
                                    {
                                        $("#navbar-sortable").find("a").each(
                                            function(id)
                                            {   
                                                $(this).attr(
                                                    "href",
                                                    $("#navbar-edit-links-dialog form fieldset input[name='navbar-url[" + id + "]']").val()
                                                ).html(
                                                    strip_tags($("#navbar-edit-links-dialog form fieldset input[name='navbar-name[" + id + "]']").val())
                                                );
                                            }
                                        );

                                        save_list();
                                        
                                        $(this).dialog('close');
                                    },
                    'Cancel':       function() 
                                    {
                            			$(this).dialog('close');
                            		}
		      }
	});	
		
	$('#navbar-edit-links').click(
        function()
        {
            $("#navbar-sortable a").each(
                function (id, href)
                {
                    $("#navbar-edit-links-dialog form fieldset").append('<label for="navbar-name[' + id + ']">Name</label><input type="text" name="navbar-name[' + id + ']" value="' + $(this).html() + '" class="text ui-widget-content ui-corner-all" /><label for="navbar-url[' + id + ']">URL</label><input type="text" name="navbar-url[' + id + ']" value="' + href + '" class="text ui-widget-content ui-corner-all" />');
                }
            );
            
            $("#navbar-edit-links-dialog").dialog('open');
        }
    ).hover(
        function()
        { 
			$(this).addClass("ui-state-hover"); 
		},
		function()
        { 
			$(this).removeClass("ui-state-hover"); 
		}
	).mousedown(
        function()
        {
			$(this).addClass("ui-state-active"); 
		}
    ).mouseup(
        function()
        {
			$(this).removeClass("ui-state-active");
		}
    );

	$('#navbar-add-separator').click(
        function()
        {
            $("#navbar-sortable").append('<hr class="navbar-separator" />');
            save_list();
        }
    ).hover(
        function()
        { 
			$(this).addClass("ui-state-hover"); 
		},
		function()
        { 
			$(this).removeClass("ui-state-hover"); 
		}
	).mousedown(
        function()
        {
			$(this).addClass("ui-state-active"); 
		}
    ).mouseup(
        function()
        {
			$(this).removeClass("ui-state-active");
		}
    );
	
    $("#navbar-trash").droppable({
        accept: "#navbar-sortable a, #navbar-sortable hr",
        greedy: true,
        activeClass: 'ui-state-highlight',
        drop:   function(event, ui) 
                {
                    $(ui.draggable).hide(
                        'slow', 
                        function()
                        { 
                            $(this).remove(); 
                        }
                    );
                }
    });
    
	$("#navbar-bgcolor").ColorPicker({
        onShow:         function (colpkr) 
                        {
                        	$(colpkr).fadeIn(500);
                        	return false;
                        },
        onHide:         function (colpkr) 
                        {
                        	$(colpkr).fadeOut(500);
                        	return false;
                        },
        onSubmit:       function(hsb, hex, rgb, el) 
                        {
                            $(el).val("#" + hex);
                            $(el).ColorPickerHide();
                        },
        onBeforeShow:   function () 
                        {
                            $(this).ColorPickerSetColor(this.value);
                        }
    }).bind(
        'keyup', 
        function()
        {
    	   $(this).ColorPickerSetColor(this.value);
        }
    );    
	
	$(document).bind(
        'keydown', 
        'Ctrl+' + NavBarSettings.hotkey, 
        function ()
        {
            if ($("#navbar").css("display") == "none")
            {   
                if (NavBarSettings['position'] == "left")
                {
                    $("body").animate(
                        {
                            marginLeft: $("#navbar").width()
                        },
                        "normal"
                    );
                }
                else
                {
                    $("body").animate(
                        {
                            marginRight: $("#navbar").width()
                        },
                        "normal"
                    );                    
                }
                
                $("#navbar").show("normal");
                
                $.post(
                    NavBarSettings.url + "/navbar-ajax.php",
                    {
                        'type':             'hide',
                        'hide':             false,
                        '_ajax_nonce':      NavBarSettings.nonce
                    }
                ); 
                
            }
            else
            {
                if (NavBarSettings['position'] == "left")
                {
                    $("body").animate(
                        {
                            marginLeft: 0
                        },
                        "normal"
                    );
                }
                else
                {
                    $("body").animate(
                        {
                            marginRight: 0
                        },
                        "normal"
                    );                    
                }
                
                $("#navbar").hide("normal");
                
                $.post(
                    NavBarSettings.url + "/navbar-ajax.php",
                    {
                        'type':             'hide',
                        'hide':             true,
                        '_ajax_nonce':      NavBarSettings.nonce
                    }
                );                
            }
        }
    );
	
	function save_list()
	{
        var links = {
            name: [],
            url: []
        };
        
        $("#navbar-sortable").children().each(
            function(i)
            {
                if ($(this).hasClass("navbar-separator"))
                {
                    links["name"][i] = "hr";
                    links["url"][i] = "#";
                }
                else
                {
                    links["name"][i] = $(this).html();
                    links["url"][i] = $(this).attr("href");   
                }     
            }
        );
        
        $.post(
            NavBarSettings.url + "/navbar-ajax.php",
            {
                'type':             'links',
                'links[name][]':    links['name'],
                'links[url][]':     links['url'],
                '_ajax_nonce':      NavBarSettings.nonce
            }
        );      
    }
    
    function add_link(name, url)
    {
        if (name == "hr" && url == "#")
        {
            $("#navbar-sortable").append(
            	$(document.createElement('hr')).addClass(
                    "navbar-separator"
                )
            );
        }
        else
        {
            name = strip_tags(name);
            
            $("#navbar-sortable").append(
            	$(document.createElement('a')).attr({
                    "href":     url,
                    "class":    "ui-state-default ui-corner-all"
                }).html(
                    name
                )
            );            
        }
    }
    
    function strip_tags(str, allowed_tags) 
    {
        // http://kevin.vanzonneveld.net
        // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   improved by: Luke Godfrey
        // +      input by: Pul
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   bugfixed by: Onno Marsman
        // +      input by: Alex
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +      input by: Marc Palau
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +      input by: Brett Zamir (http://brett-zamir.me)
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   bugfixed by: Eric Nagel
        // +      input by: Bobby Drake
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // *     example 1: strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
        // *     returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
        // *     example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
        // *     returns 2: '<p>Kevin van Zonneveld</p>'
        // *     example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
        // *     returns 3: '<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>'
        // *     example 4: strip_tags('1 < 5 5 > 1');
        // *     returns 4: '1 < 5 5 > 1'
     
        var key = '', allowed = false;
        var matches = [];
        var allowed_array = [];
        var allowed_tag = '';
        var i = 0;
        var k = '';
        var html = '';
     
        var replacer = function(search, replace, str) 
        {
            return str.split(search).join(replace);
        };
     
        // Build allowes tags associative array
        if (allowed_tags) 
        {
            allowed_array = allowed_tags.match(/([a-zA-Z]+)/gi);
        }
     
        str += '';
     
        // Match tags
        matches = str.match(/(<\/?[\S][^>]*>)/gi);
     
        // Go through all HTML tags
        for (key in matches) {
            if (isNaN(key)) {
                // IE7 Hack
                continue;
            }
     
            // Save HTML tag
            html = matches[key].toString();
     
            // Is tag not in allowed list? Remove from str!
            allowed = false;
     
            // Go through all allowed tags
            for (k in allowed_array) 
            {
                // Init
                allowed_tag = allowed_array[k];
                i = -1;
     
                if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+'>');}
                if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+' ');}
                if (i != 0) { i = html.toLowerCase().indexOf('</'+allowed_tag)   ;}
     
                // Determine
                if (i == 0) {
                    allowed = true;
                    break;
                }
            }
     
            if (!allowed) {
                str = replacer(html, "", str); // Custom replace. No regexing
            }
        }
     
        return str;
    }
	
});