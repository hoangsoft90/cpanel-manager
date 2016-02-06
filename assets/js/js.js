(function( $ ) {
    if(!$.widget) {
        alert("Need a jquery ui");
        return;
    }
    $.widget( "custom.combobox", {
        _create: function() {
            this.wrapper = $( "<span>" )
                .addClass( "custom-combobox" )
                .insertAfter( this.element );

            this.element.hide();
            this._createAutocomplete();
            this._createShowAllButton();
        },

        _createAutocomplete: function() {
            var selected = this.element.children( ":selected" ),
                value = selected.val() ? selected.text() : "";

            this.input = $( "<input>" )
                .appendTo( this.wrapper )
                .val( value )
                .attr( "title", "" )
                .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: $.proxy( this, "_source" )
                })
                .tooltip({
                    tooltipClass: "ui-state-highlight"
                });

            this._on( this.input, {
                autocompleteselect: function( event, ui ) {
                    ui.item.option.selected = true;
                    this._trigger( "select", event, {
                        item: ui.item.option
                    });
                },

                autocompletechange: "_removeIfInvalid"
            });
        },

        _createShowAllButton: function() {
            var input = this.input,
                wasOpen = false;

            $( "<a>" )
                .attr( "tabIndex", -1 )
                .attr( "title", "Show All Items" )
                .tooltip()
                .appendTo( this.wrapper )
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                .removeClass( "ui-corner-all" )
                .addClass( "custom-combobox-toggle ui-corner-right" )
                .mousedown(function() {
                    wasOpen = input.autocomplete( "widget" ).is( ":visible" );
                })
                .click(function() {
                    input.focus();

                    // Close if already visible
                    if ( wasOpen ) {
                        return;
                    }

                    // Pass empty string as value to search for, displaying all results
                    input.autocomplete( "search", "" );
                });
        },

        _source: function( request, response ) {
            var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
            response( this.element.children( "option" ).map(function() {
                var text = $( this ).text();
                if ( this.value && ( !request.term || matcher.test(text) ) )
                    return {
                        label: text,
                        value: text,
                        option: this
                    };
            }) );
        },

        _removeIfInvalid: function( event, ui ) {

            // Selected an item, nothing to do
            if ( ui.item ) {
                return;
            }

            // Search for a match (case-insensitive)
            var value = this.input.val(),
                valueLowerCase = value.toLowerCase(),
                valid = false;
            this.element.children( "option" ).each(function() {
                if ( $( this ).text().toLowerCase() === valueLowerCase ) {
                    this.selected = valid = true;
                    return false;
                }
            });

            // Found a match, nothing to do
            if ( valid ) {
                return;
            }

            // Remove invalid value
            this.input
                .val( "" )
                .attr( "title", value + " didn't match any item" )
                .tooltip( "open" );
            this.element.val( "" );
            this._delay(function() {
                this.input.tooltip( "close" ).attr( "title", "" );
            }, 2500 );
            this.input.autocomplete( "instance" ).term = "";
        },

        _destroy: function() {
            this.wrapper.remove();
            this.element.show();
        }
    });
})( jQuery );
/**
 * ajax submit form
 * @param form form element
 */
function init_ajaxform(form) {

    //form action
    var action = $(form).attr('action');
    if(!action) action = location.href; //get current page url

    $(form).submit(function(event) {
        event.preventDefault();

        // get the form data
        // there are many ways to get this data using jQuery (you can use the class or id also)
        var formData = $(this).serialize(),
            submitbtn = $(this.submit),
            require = $(this).data('require').split(',');

        for(var i in require) {
            if( $(this).find("[name="+require[i]+"]:first").get(0).value.replace(/[\s]+/g,'')=="") {
                alert('require ['+require[i]+']');
                return false;
            }
        }
        //console.log(formData);
        $(submitbtn).attr('disabled','disabled').val('loading...');
        //event.preventDefault();return;
        if($(submitbtn).hw_is_ajax_working({loadingText:'loading..',cssBackground:true})) {
            alert('working..');
            return;
        }
        // process the form
        $.ajax({
            type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
            url         : action, // the url where we want to POST
            data        : formData, // our data object
            encode          : true
        })
            // using the done promise callback
            .done(function(data) {
                $(submitbtn).removeAttr('disabled').val('Submit');  //resume button text
                // log data to the console so we can see
                log(data);
                alert('Done !');

                $(submitbtn).hw_reset_ajax_state();
            });

        event.preventDefault();

    });
}
/**
 * URL string to array
 * @param url
 * @returns {{}}
 * @constructor
 */
function URLToArray(url) {
    var request = {};
    var pairs = url.substring(url.indexOf('?') + 1).split('&');
    for (var i = 0; i < pairs.length; i++) {
        if(!pairs[i])
            continue;
        var pair = pairs[i].split('=');
        request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
    }
    return request;
}
//--------------------------------------------------------------------------------
/***
 * myform_user form submision
 * @param frm
 */
function myform_submit(frm) {
    if(frm.acc.options.length==0) {
        alert("Please select account before ?");
        return false;
    }
    return true;
}

jQuery(function($){
    //clear log event
    $('.logging .clearbtn').click(function() {
        clearLog();
    });
    $('input[type=file]').change(function () {
        //console.log(URL.createObjectURL(this.files[0]));
        //var filePath = $(this).val();
        console.log(this.files[0]);
    });
    if($('.tooltip').tooltipster) {
        $('.tooltip').tooltipster({
            contentAsHTML: true,
            position: 'right'
        });
        $('.tooltip-top').tooltipster({
            contentAsHTML: true,
            position: 'top'
        });

    }
    $( ".combobox_acc" ).combobox();
    //init ajax form processing
    init_ajaxform('.ajax-form');
});
/**
 * set checked all checbox exists in form
 * @param opt
 * @param ckb_selector
 */
function hw_checkall(opt, ckb_selector) {
    var status = opt.checked;
    var frm= opt.form;
    if(! $(frm).is('form')) return;   //valid

    var ckbox = (ckb_selector)? $(ckb_selector) : $(frm).find('input[type=checkbox]');
    ckbox.prop('checked', status);
}
/**
 * checkbox values
 * @param frm
 * @returns {string}
 */
function hw_get_checkboxs_values(frm) {
    //get selected repositories
    var repo_list='';
    $(frm).find('input[type=checkbox]:checked').each(function(){
        repo_list=repo_list+','+$(this).val();
    });
    repo_list=repo_list.replace(/^[,\s]+/g, '');
    return repo_list;
}
/**
 * set log
 * @param txt
 */
function log(txt){
    $('.logging .content').append($('<div/>').append(txt));
    $(".logging .content").scrollTop($(".logging .content")[0].scrollHeight);
}
/**
 * empty logs
 */
function clearLog() {
    $('.logging .content').empty();
}
/**
 * generate strong password
 * @param obj
 * @param holder
 */
function hw_generate_strong_pass(obj, holder) {
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=generate_strong_pass&auth=0&available_sets=lud';
    $.ajax({
        url: url,
        success: function(res) {
            //log(res);
            if(typeof holder == 'function') {
                holder(res);
            }
            else $(holder).val(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        }
    });
    //other way
    if(typeof generate_password == 'function') {
        //return generate_password();
    }
}
/**
 * trim characters from string
 * @param string
 * @param charToRemove
 * @returns {*}
 */
function trimChar(string, charToRemove) {
    while(string.charAt(0)==charToRemove) {
        string = string.substring(1);
    }

    while(string.charAt(string.length-1)==charToRemove) {
        string = string.substring(0,string.length-1);
    }

    return string;
}
/**
 * URL string to array
 * @param url
 * @returns {{}}
 * @constructor
 */
function URLToArray(url) {
    var request = {};
    var pairs = url.substring(url.indexOf('?') + 1).split('&');
    for (var i = 0; i < pairs.length; i++) {
        if(!pairs[i])
            continue;
        var pair = pairs[i].split('=');
        request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
    }
    return request;
}
/**
 * open url in new tab
 * @param url
 * @constructor
 */
function OpenInNewTab(url) {
    var win = window.open(url, '_blank');
    //win.focus();
}