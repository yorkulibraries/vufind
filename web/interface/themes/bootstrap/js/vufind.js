$.ajaxSetup ({
    // Disable caching of AJAX responses
    cache: false
});

$(document).ready(function() {    
    // disable repeat submissions when enter is pressed and held down
    preventRepeatedEnters();    
    
	// check availability
	checkAvailability();
	
	// resolve full text links
	resolveLinks();
	
	// fetch google books info
	fetchGoogleBooksInfo();
    
    // setup advanced search buttons
    setupAdvancedSearchButtons();
    
    // setup general link event handlers
    setupLinks();
    
    // setup general form submit handlers
    setupForms();
    
    // setup book bag actions
    setupBookBag();
    
    // handle bootstrap events
    handleBootstrapEvents();
    
    // setup upload cover form
    setupUploadCoverForm();
    
    // setup more/less buttons
    activateMoreLessButtons('.container');    
});

// handle logged out event
$(document).on('loggedout.vufind', function(e, params) {
    $('#myAccountPanel').html(params.loggedOutPanel);
    refreshRecordComments();
    $('.upload-cover-button').addClass('hidden');
});

// handle logged in event
$(document).on('loggedin.vufind', function(e, params) {
    $('#myAccountPanel').html(params.loggedInPanel);
    refreshRecordComments();
    if (params.canUploadCovers) {
        $('.upload-cover-button').removeClass('hidden');
    } else {
        $('.upload-cover-button').addClass('hidden');
    }
});

// handle comment added event
$(document).on('commentadded.vufind', function(e, params) {
    $('#recordCommentsList').siblings('.alert').remove();
    $('#recordCommentsList').html(params.list);
});

// handle comment deleted event
$(document).on('commentdeleted.vufind', function(e, params) {
    $('#recordCommentsList').siblings('.alert').remove();
    $('#recordCommentsList').html(params.list);
    $('#recordCommentsList').before(bootstrapAlert(params.message, 'success'));
});

// handle request placed event
$(document).on('requestplaced.vufind', function(e, params) {
    console.log('requestplaced');
});

// press and hold Enter key causes repeated submissions, prevent it.
function preventRepeatedEnters() {
    // don't submit the form when enter key is pressed (and possibly held down)
    $('input').keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
        }
    });
    // submit the form when the enter key is released instead
    $('input').keyup(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $(this).closest('form').submit();
        }
    });
}

function setupUploadCoverForm() {
    $('form.coverupload').fileupload({
        dataType: 'json',
        done: function (e, data) {
            if (data.result.files.length != 0) {
                var url = data.result.files[0].url + '&t=' + new Date().getTime();;
                var $bookcover = $(this).closest('.bookcover');
                var $img = $bookcover.find('img:first');
                if($img.size() == 0) {
                    $bookcover.prepend('<img class="img-responsive" src="' + url + '" alt="Cover Image"/>')
                } else {
                   $img.attr('src', url); 
                }
            }
        }
    });
    $('.upload-cover-button').on('click', function(e) {
        $(this).find('input:first').focus();
    });
}

function refreshRecordComments() {
    var id = $('.record-container').data('record-id');
    if (id) {
        $('#recordCommentsList').siblings('.alert').remove();
        $('#recordCommentsList').load(_global_path + '/AJAX/JSON?method=getRecordCommentsAsHTML&html=1&id=' + id);
    }
}

function handleBootstrapEvents() {
    $('.accordion').on('hidden.bs.collapse', '.panel-collapse', function () {
        $(this).siblings('.panel-heading').find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-right');
    });
    $('.accordion').on('shown.bs.collapse', '.panel-collapse', function () {
        var $heading = $(this).siblings('.panel-heading');
        $heading.find('.fa-chevron-right').removeClass('fa-chevron-right').addClass('fa-chevron-down');
        if (typeof ga == 'function') { 
            ga('send', 'event', 'accordion', 'shown', $heading.find('.panel-title').text(), 1);
        }
    });
    // make sure to destroy the modal when hidden so that it is loaded every time
    $('body').on('hidden.bs.modal', '.ajax-modal', function () {
      $(this).removeData('bs.modal').find('.modal-content').empty();
    });
    // load remote page when tab is shown
    $('.record-view-tabs a[data-toggle="tab"]').on('show.bs.tab', function (e) {
    	var target = $(e.target).data('target');
    	var href = $(e.target).attr('href');
    	var content = target + '-tab-content';
    	if($(content).length == 0) {
    		$(target).load(href + ' ' + content, function() {
                onAjaxTabLoaded(target);
            });
    	}
    });
}

function bootstrapAlert(data, type) {
    if (typeof data == 'string') {
        return bootstrapAlertHTML(data, type);   
    }
    if (typeof data.formErrors != 'undefined') {
        var alert = '';
        for (var field in data.formErrors) {
            alert += bootstrapAlertHTML(data.formErrors[field], type);
        }
        return alert;
    }
}

function bootstrapAlertHTML(message, type) {
    var alert = '<div class="alert alert-' + type + ' alert-dismissable">'
    + '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'
    + message
    + '</div>';
    return alert;
}

function setupForms() {
    // update the hidden "type" input variable on basic search form whenever a menu item is clicked
    $('.basic-search-type-menu-item').on('click', function (e) {
        e.preventDefault();
        var searchType = $(this).data('search-type');
        var searchTypeLabel = $(this).data('search-type-label');
        if (!searchType) {
            searchType = 'AllFields';
        }
        var $form = $(this).closest('form');
        var $lookfor = $form.find('input[name="lookfor"]');
        var placeholder = $lookfor.data('placeholder-' + searchType.toLowerCase());
        $lookfor.attr('placeholder', placeholder);
        $form.find('input[name="type"]').val(searchType);
        $(this).closest('li').addClass('active').siblings('li').removeClass('active');
        $form.find('.basic-search-type-menu-label').text(searchTypeLabel);
    });
    
    // track clicking of the renew buttons
    $('input.renew-button, input.cancel-hold-button').on('click', function () {
        if (typeof ga == 'function') { 
            ga('send', 'event', 'button', 'click', $(this).val(), 1);
        }
    });
    
    // submit the form if the multi-select-facet-checkbox is clicked
	$('form.facet input[type="checkbox"]').on('change', function() {
		$(this).closest('form').submit();
		// track this event in google analytics
		if (typeof ga == 'function') { 
            ga('send', 'event', 'facet', 'click', $(this).val(), 1);
        }
	});
	
	// submit the form with .ajax if data-json is set
    $('body').on('submit', 'form', function(e) {
        var $this = $(this);
        var $modal = $this.closest('.modal');
        var inModal = $modal.size() > 0; 
        if ($this.data('json')) {
            e.preventDefault();
            if ($this.hasClass('login-form')) {
                $this.find('input[name="username"]').attr('name', 'ajax_username');
                $this.find('input[name="password"]').attr('name', 'ajax_password');
            }
            $.ajax({
                type: 'post',
                url: $this.data('json'),
                dataType: 'json',
                data: $this.serialize(),
                success: function(resp, status) {
                    if (resp.status == 'ERROR') {
                        $this.children('.alert-container').html(bootstrapAlert(resp.data, 'danger'));
                        if ($this.hasClass('login-form') && $this.children('.alert-container').size() == 0) {
                            alert(resp.data);
                        }
                    } else if (resp.status == 'NEED_AUTH') {
                        if (inModal) {
                            $('.modal-content', $modal).load($this.attr('action'), {modal:1});
                        } else {
                            $this.children('.alert-container').html(bootstrapAlert(resp.data, 'danger'));
                        }
                    } else {
                        if (inModal) {
                            if (resp.data.followup) {
                                $('.modal-content', $modal).load(resp.data.followup, {modal:1});
                            } else {
                                if (resp.data.modalAlert) {
                                    $('.modal-content', $modal).html(resp.data.modalAlert);
                                } else {
                                    $modal.modal('hide');
                                }
                            }
                        }
                        if (resp.data.events) {
                            for (var i = 0; i < resp.data.events.length; i++) {
                                var event = resp.data.events[i];
                                $.event.trigger(event.type, event.data);
                            }
                        }
                    }
                }
            });
        }
    });
}

function setupLinks() {
    // handle links with data-json
    $('body').on('click', 'a[data-json]', function(e) {
        e.preventDefault();
        var $this = $(this);
        $.ajax({
            url: $this.data('json'),
            dataType: 'json',
            success: function(resp, status) {
                if (resp.data.events) {
                    for (var i = 0; i < resp.data.events.length; i++) {
                        var event = resp.data.events[i];
                        $.event.trigger(event.type, event.data);
                    }
                }
            }
        });
        if (typeof ga == 'function') { 
            ga('send', 'pageview', {'page': $(this).attr('href')});
        }
    });
    // handle links with data-toggle="modal"
    $('body').on('click', 'a[data-toggle="modal"]', function() {
        if ($(this).attr('href').indexOf('modal=1') == -1) {
            var param = ($(this).attr('href').indexOf('\?') == -1) ? '?modal=1' : '&modal=1';
            $(this).attr('href', $(this).attr('href') + param);
        }
        if (typeof ga == 'function') { 
            ga('send', 'pageview', {'page': $(this).attr('href')});
        }
    });
    // handle links with data-confirm
    $('body').on('click', 'a[data-confirm]', function(e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });
    // handle links with data-mylang
    $('body').on('click', 'a[data-mylang]', function(e) {
        e.preventDefault();
       
        var base = $(location).attr('href');
        var query = '';
        var index = $(location).attr('href').indexOf('?');
        if (index != -1) {
            base = $(location).attr('href').substring(0, index);
            query = $(location).attr('href').substring(index + 1);
        }
        query = query.replace(/&?mylang=[a-z]{2}/g, '');
        if (query.length > 0) {
            query += '&';
        }
        query += 'mylang=' + $(this).data('mylang');
        window.location = base + '?' + query;
    });
    // subject links - when hover'ed, highlight the entire subject line
    $('.subject-line a').hover( 
        function() {
            $(this).addClass('highlight').prevAll('a').addClass('highlight');
        },
        function() {
            $(this).removeClass('highlight').prevAll('a').removeClass('highlight');
        }
    );
    // track facet clicks in google analytics
    $('a.facet').on('click', function(e) {
        if (typeof ga == 'function') { 
            ga('send', 'event', 'facet', 'click', $(this).data('val'), 1);
        }
    });
    // show/hide sidebar on smaller screens
    $('[data-toggle=offcanvas]').click(function () {
        $('.row-offcanvas').toggleClass('active');
        ga('send', 'event', 'button', 'click', 'Show/hide sidebar', 1);
    });
}

function setupAdvancedSearchButtons() {
    $('.adv-search-form').on('click', '.adv-search-add-field', function(e) {
        var numberOfRows = $(this).closest('.adv-search-form').find('.row').size();
        $.ajax({
	        dataType: 'json',
	        url: _global_path + '/AJAX/JSON?method=newAdvancedSearchRow',
	        data: {group: numberOfRows-1},
	        success: function(response) {
	            if(response.status == 'OK') {
	                $('.adv-search-fields').append(response.data);
	            }
	        }
		});
		if (typeof ga == 'function') { 
            ga('send', 'event', 'button', 'click', 'Add Search Field', 1);
        }
    });
    $('.adv-search-form').on('click', '.adv-search-type-menu-item', function(e) {
        e.preventDefault();
        $(this).closest('li').addClass('active').siblings('li').removeClass('active');
        $(this).closest('.adv-search-type-container').find('.type-menu-label').text($(this).data('label'));
        $(this).closest('.adv-search-type-container').children('.type-menu-value').val($(this).data('value'));
    });
    $('.adv-search-form').on('click', '.adv-search-join-menu-item', function(e) {
        e.preventDefault();
        $(this).closest('li').addClass('active').siblings('li').removeClass('active');
        $(this).closest('.adv-search-join-container').find('.join-menu-label').text($(this).data('label'));
        $(this).closest('.adv-search-join-container').children('.join-menu-value').val($(this).data('value'));
    });
}

function activateMoreLessButtons(container) {
    $('[data-toggle=more-less]', container).each(function() {
        var $button = $(this);
        var threshold = $button.data('threshold');
        if (threshold == undefined || threshold == 0) {
            threshold = 5;
        }
        threshold--;
        var $itemsToHide = $button.closest($button.data('target')).find('.more-less:gt(' + threshold + ')');
        $itemsToHide.addClass('hidden');
        if ($itemsToHide.length > 0) {
            $button.removeClass('hidden');
        }
        $button.on('click', function(e) {
            e.preventDefault();
            $itemsToHide.toggleClass('hidden');
            $button.children('.fa').toggleClass('fa-plus fa-minus');
            var $label = $button.children('.more-less-label');
            var alt = $label.data('alt');
            $label.data('alt', $label.text());
            $label.text(alt);        
            if (typeof ga == 'function') { 
                ga('send', 'event', 'button', 'click', 'More/less: ' + $button.data('target-name'), 1);
            }
        });
    });
}

function checkAvailability() {
	var ids = [];
	$('.ajax-availability').each(function() {
		var id = $(this).closest('.result-container').data('record-id');
		if (/^[0-9]+$/.test(id)) {
			ids.push(id);
		}
	});
	if (ids.length > 0) {
		$.ajax({
		    cache: true,
	        dataType: 'json',
	        url: _global_path + '/AJAX/JSON?method=getItemStatuses',
	        data: {id:ids},
	        success: function(response) {
	            if(response.status == 'OK') {
	                $.each(response.data, function(i, result) {
	                	$('.result-container[data-record-id="' + result.id + '"] .ajax-availability').append(result.full_status);
	                });
                    activateMoreLessButtons('.ajax-availability');
	            }
	        }
		});
	}
}

function resolveLinks() {
    $('.online-access-container').each(function() {
        var $onlineAccessContainer = $(this);
        var $normalContainer = $onlineAccessContainer.find('.normal-links-container');
        var $openurlContainer = $onlineAccessContainer.find('.openurl-container');
        
        // if only normal links are present, then activate the normal links
        if ($openurlContainer.length == 0 && $normalContainer.length > 0) {
            $normalContainer.removeClass('hidden');
            $onlineAccessContainer.removeClass('hidden');
            return;
        }
        
        // attempt to resolve openurl links
        var issns = [];
        $('.openurl', $openurlContainer).each(function() {
    	    issns.push($(this).data('issn'));
    	});
    	if (issns.length > 0) {
    	    $.ajax({
        	    cache: true,
    	        dataType: 'json',
    	        url: _global_path + '/AJAX/JSON?method=getResolverLinks',
    	        data: {issn:issns},
    	        success: function(response) {
    	            if(response.status == 'OK' && response.data.length > 0) {	                
    	                $openurlContainer.append(response.data);
    	                $openurlContainer.removeClass('hidden');
    	                $onlineAccessContainer.removeClass('hidden');
                    } else {
                        if ($normalContainer.length) {
                            $normalContainer.removeClass('hidden');
                            $onlineAccessContainer.removeClass('hidden');
                        }
    	            }
    	            activateMoreLessButtons($openurlContainer);
    	        }
    		});
	    }
    });
}

function fetchGoogleBooksInfo() {
	var isbns = new Array();
    $('.google-books-preview').each(function(){
        var isbn = $(this).data('isbn');
        if (isbn) {
    	    isbns.push(isbn);
	    }
    });
	if (isbns.length > 0) {
	    var https =  (document.location.protocol == 'https:' || window.location.protocol == 'https:');
	    var url = https ? 'https://encrypted.google.com/books' : 'http://books.google.com/books';
		var script = url + '?jscmd=viewapi&bibkeys=' + isbns.join(',') + '&callback=googleBooksAPICallback';
		$.getScript(script);
	}
}

function googleBooksAPICallback(booksInfo) {
	for (bibkey in booksInfo) {
        var bookInfo = booksInfo[bibkey];
        if (bookInfo) {
            if (bookInfo.preview == "full" || bookInfo.preview == "partial") {
                $("div[data-role='footer']")
            	$('.google-books-preview[data-isbn="' + bibkey + '"]').removeClass('hidden').find('a').attr('href', bookInfo.preview_url);
            }
        }
    }
}

/** Book bag functions **/
function setupBookBag() {
    syncBookBagWithUI(getBookBagContent());
    $('.empty-book-bag').on('click', function (e) {
        e.preventDefault();
        emptyBookBag();
        if ($(location).attr('href').match('.*/BookBag/Home.*')) {
            location.reload();
        } else {
            syncBookBagWithUI(getBookBagContent());
        }
    });
    $('.mark-unmark-record').change(function() {
        if ($(this).is(':checked')) {
            addToBookBag($(this).val());
        } else {
            removeFromBookBag($(this).val());
        }
        updateBookBagButton(getBookBagContent());
    });
}

function getBookBagContent() {
    var cookie = $.cookie('vufind_cart');
    var items = (cookie && cookie.length > 0) ? cookie.split("\t") : new Array();
    return items;
}

function saveBookBagContent(content) {
    var cookie = (content.length > 0) ? content.join("\t") : '';
    $.cookie('vufind_cart', cookie, { path: '/' });
}

function inBookBag(id, items) {
    return ($.inArray(id.toString(), items) != -1);
}

function addToBookBag(id) {
    var items = getBookBagContent();
    if (!inBookBag(id, items)) {
        items.push(id);
        saveBookBagContent(items);
        if (typeof ga == 'function') { 
            ga('send', 'event', 'button', 'click', 'Add to Book Bag', 1);
        }
    }
}

function removeFromBookBag(id) {
    var content = getBookBagContent();
    content = $.grep(content, function(value) {
      return value != id;
    });
    saveBookBagContent(content);
}

function emptyBookBag() {
    saveBookBagContent(new Array());
}

function syncBookBagWithUI(items) {
    updateBookBagButton(items);
    $('.mark-unmark-record').each(function() {
        $(this).prop('checked', inBookBag($(this).val(), items));
    });
}

function updateBookBagButton(content) {
    $('.bookbag').each(function() {
        var $badge = $('.bookbag-count', this);
        $badge.html(content.length);
        if (content.length == 0) {
            $badge.removeClass('bg-success');
            $(this).siblings('.dropdown-menu').children('li').addClass('disabled');
        } else {
            $badge.addClass('bg-success');
            $(this).siblings('.dropdown-menu').children('li').removeClass('disabled');
        } 
    });
}

function onAjaxTabLoaded(target) {
    if (typeof activateCarousels === 'function') {
        activateCarousels();
    }
}

// Browse shelf carousel: catch before/after slide change events
$(document).on('beforeChange', '.browse-shelf', function(event, slick, currentSlide, nextSlide) {
    if (nextSlide > currentSlide) {
        slick.slidingDirection = 'right';
    } else {
        slick.slidingDirection = 'left';
    }
});

$(document).on('afterChange', '.browse-shelf', function(event, slick, currentSlide) {
    if (!(slick.slidingDirection == 'left' || slick.slidingDirection == 'right')) {
        return;
    }
    
    var slidesToShow = slick.getOption('slidesToShow');
    var lhs = currentSlide;
    var rhs = slick.slideCount - currentSlide - slidesToShow;

    var $lastItem = null;
    var direction = slick.slidingDirection;
    if ('left' == direction) {
        if (lhs <= slidesToShow) {
            $lastItem = $('.browse-shelf-item:first-child()', '.browse-shelf');
        }
    } else {
        if (rhs <= slidesToShow) {
            $lastItem = $('.browse-shelf-item:last-child()', '.browse-shelf');
        }
    }
    
    if ($lastItem != null) {
        var offset = $lastItem.data('shelf-order');
        var isLast = $lastItem.data('is-last');
        if (offset > 0 && !isLast) {
            $.ajax({
                url: _global_path + '/AJAX/JSON?method=shelfBrowseMore',
                dataType: 'json',
                data: {direction: direction, offset: offset},
                success: function(response) {
                    if(response.status == 'OK') {
                        var slidesToAdd = response.data;
                        $lastItem.data('is-last', (slidesToAdd.length == 0));
                        if (slidesToAdd.length > 0) {
                            var html = slidesToAdd.join('');
                            if ('left' == direction) {
                                slick.currentSlide += slidesToAdd.length;
                                slick.addSlide(html, true);
                            } else {
                                slick.addSlide(html);
                            }
                        }
                    }
                }
            });
        }
    }
    delete slick.slidingDirection;
});


function setupZeroClipboard() {
    ZeroClipboard.config( { swfPath: _global_path + "/interface/themes/bootstrap/js/ZeroClipboard.swf" } );
    $('.btn-clipboard').hover(
      function() {
        $( this ).addClass( "btn-clipboard-hover" );
      }, function() {
        $( this ).removeClass( "btn-clipboard-hover" );
      }
    );
    var client = new ZeroClipboard($('.btn-clipboard'));
}