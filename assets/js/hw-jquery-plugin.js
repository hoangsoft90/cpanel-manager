/**
 * check wether ajax working that fire by button toggle
 * @param options plugin options
 */
jQuery.fn.hw_is_ajax_working = function(options) {
    if(this.data('hw_ajax_working')) return true;
    this.data( "hw_ajax_working", 1 );  //set event status

    var data = {};

    //prepare data that i will used to save some info on element
    if(!this.data('hw-data')) {
        this.data('hw-data', data);
    }
    else data = this.data('hw-data');

    //get current content on element
    if(data && !data.text && typeof this.text == 'function') data.text = this.text();
    if(data && !data.html && typeof this.html == 'function') data.html = this.html();

    // This is the easiest way to have default options.
    var settings = jQuery.extend({
        // These are the defaults.
        //color: "#556b2f",
        //backgroundColor: "white",
        cssBackground:0
    }, options );

    //change object text
    if(settings.loadingText) this.text(settings.loadingText);
    this.addClass(settings.cssBackground? 'hw-ajax-working-bg':'hw-ajax-working');   //add class

    //update data on element
    this.data('hw-data', data);
    return false;
};
/**
 * reset ajax to work
 */
jQuery.fn.hw_reset_ajax_state = function(){
    this.data("hw_ajax_working", 0);    //allow make to call ajax
    var data = this.data('hw-data');
    //resume element content
    if(data && data.html && this.html) this.html(data.html);
    this.removeClass('hw-ajax-working').removeClass('hw-ajax-working-bg');    //remove working ajax class
};
/**
 * add loading image to container
 * @param options
 */
jQuery.fn.hw_set_loadingImage = function(options) {
    //prepare loading img
    if(! this.data('hw_loading_img') ) {
        var img = jQuery('<img/>', {src : hwcpanel.loading_image});
        this.data('hw_loading_img', img);
    }
    // This is the easiest way to have default options.
    var settings = jQuery.extend({
        // These are the defaults.
        target: "",
        place: "after"
    }, options );
    //var img;
    jQuery(this.data('hw_loading_img')).show();  //get loading element, and make it visible

    //valid
    if(!settings.target) settings.target = this;    //target object to render loading image

    //append loading image to container
    if(settings.place == 'replace') jQuery(settings.target).empty().append(img);
    else if(settings.place == 'after') jQuery(settings.target).append(img);
    else if(settings.place == 'before') jQuery(settings.target).prepend(img);
};
/**
 * remove loading image from container get from current element
 * @param target (optional) ->seem no longer use
 */
jQuery.fn.hw_remove_loadingImage = function(target) {
    if(! this.data('hw_loading_img') ) return ;
    if(!target) target = this;  //target is current element

    var img = this.data('hw_loading_img');
    //jQuery(target).remove(img);
    jQuery(img).hide(); //best way
};
/**
 * live search on a table
 * @param options
 * @example $(..).hw_search_table({inputText:'#input', column:1})
 */
jQuery.fn.hw_search_table = function(options) {
    // This is the easiest way to have default options.
    var settings = jQuery.extend({
        column:0, from_begin:0
    }, options );
    var table = this,
        removeHighlighting = function(highlightedElements){
            highlightedElements.each(function(){
                var element = $(this);
                element.replaceWith(element.html());
            })
        },
        addHighlighting = function(element, textToHighlight){
            var text = element.text();
            var highlightedText = '<em class="highlight-keyword">' + textToHighlight + '</em>';
            var newText = text.replace(textToHighlight, highlightedText);

            element.html(newText);
        };

    $(settings.inputText).on("keyup", function() {
        var value = $(this).val();
        removeHighlighting($(table).find("tr em"));

        $(table).find("tr").each(function(index) {
            if (index !== 0) {

                $row = $(this);

                var $tdElement = $row.find("td:eq("+settings.column+")");
                var id = $tdElement.text();

                if ((settings.from_begin && id.indexOf(value) !== 0) || (!settings.from_begin && id.indexOf(value) ===-1)) {
                    $row.hide();
                }
                else {
                    addHighlighting($tdElement, value);
                    $row.show();
                }
            }
        });
    });
};