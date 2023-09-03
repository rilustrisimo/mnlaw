var Theme = {

    disableTabStateRestore:false,

    selectLiveSearchAutoStart:0,

    init:function( $ ) {
        this._initHamburgerMenu();
        this._initMobileMenu();
        this._initScrollTop( $ );
        this.initSelectpicker();
        this.initResponsiveTables();
        this._initTabsStateFromHash( $ );

        $( '[data-toggle="tooltip"]' ).tooltip();

        this.FormValidationHelper.init();

        $('.counter').counterUp({
            delay: 10,
            time: 1000
        });
    },

    _initHamburgerMenu: function() {
        var menuTrigger = jQuery('#hamburger-menu'),
            bottomLayer = jQuery('.bottom_layer'),
            mainNav = jQuery('.main-nav-header');

        menuTrigger.on('click', function(e){
            e.preventDefault();

            if ( menuTrigger.hasClass('hamburger-menu-close') ) {
                menuTrigger.removeClass('hamburger-menu-close').addClass('hamburger-menu-open');
                bottomLayer.css('visibility', 'visible');
                //mainNav.show('fast');
            } else {
                menuTrigger.removeClass('hamburger-menu-open').addClass('hamburger-menu-close');
                bottomLayer.css('visibility', 'hidden');
                //mainNav.hide('fast');
            }
        });
    },

    initSVGanimation: function( config ) {
        // For option list check https://github.com/maxwellito/vivus
        var cfg = jQuery.extend( {
                svgSelector:'',
            }, config || {}),
            svgCfg = {
                type: 'oneByOne',
                duration: 60,
                animTimingFunction: Vivus.EASE
            };

        if ( ! cfg.svgSelector ) {
            return null;
        }

        if ( cfg.vivusOptions ) {
            jQuery.extend( svgCfg, cfg.vivusOptions );
        }

        var noWinNoFee = new Vivus(cfg.svgSelector, svgCfg);
    },

    initGoogleMap: function( cfg ) {
        if ( 'undefined' == typeof( cfg ) ) {
            return;
        }

        var mapElement = document.getElementById( cfg.element_id );

        if ( ! mapElement ) {
            return;
        }

        var jMap = jQuery( mapElement );
        jMap.height( cfg.height );

        if ( cfg.full_width ) {
            var onResizeHandler = function() {
                jMap.width( jQuery( window ).outerWidth() )
                    .offset( { left:0 } );
                if ( map ) {
                    //google.maps.event.trigger(map, 'resize');
                    if ( mapLang ) {
                        map.setCenter( mapLang );
                    }
                }
            };
            onResizeHandler();
            jQuery( window ).on( 'resize', onResizeHandler );
        }

        var mapLang = new google.maps.LatLng( parseFloat( cfg.coordinates[0] ), parseFloat( cfg.coordinates[1] ) ),
            map = new google.maps.Map( mapElement, {
                scaleControl: true,
                center: mapLang,
                zoom: cfg.zoom,
                mapTypeId: cfg.MapTypeId || google.maps.MapTypeId.ROADMAP,
                scrollwheel: false
            }),
            marker = new google.maps.Marker({
                map: map,
                position: map.getCenter()
            });

        // Registers map instance in _inited_maps collection.
        if ( ! this._inited_maps ) this._inited_maps = {};
        this._inited_maps[cfg.element_id] = map;

        if ( cfg.address ) {
            var infowindow = new google.maps.InfoWindow();
            infowindow.setContent( cfg.address );
            google.maps.event.addListener( marker, 'click', function() {
                infowindow.open( map, marker );
            });
        }

        // Fix display map in bootstrap tabs and accordion.
        if ( cfg.is_reset_map_fix_for_bootstrap_tabs_accrodion ) {
            jQuery( document ).on( 'shown.bs.collapse shown.bs.tab', '.panel-collapse, a[data-toggle="tab"]', function() {
                google.maps.event.trigger( map, 'resize' );
                map.setCenter( mapLang );
            });
        }
    },

    initStickyHeader: function() {
        var doc = jQuery( document ),
            headerIsSticky = false,
            headerWrap = jQuery( '.header-wrap' ),
            headerInfo = headerWrap.find( '.header__info' ),
            headerBacklog = headerWrap.find( '.header-wrap__bundle' ),
            headerWrapClassSticky = 'header-wrap--sticky-header',
            stickyBreakpoint = null,
            switchHeightDelay = 0,
            calculateHeaderInfo = function(){
                stickyBreakpoint = headerInfo.outerHeight() + switchHeightDelay;

                headerBacklog.css({
                    'min-height': headerWrap.find( '.header__content-wrap' ).outerHeight() + 'px',
                    'margin-top': stickyBreakpoint + 'px'
                });
            };
        setTimeout( calculateHeaderInfo, 10 );
        jQuery( window ).on( 'resize', calculateHeaderInfo );

        doc.on( 'scroll', function() {
            var newState = doc.scrollTop() > stickyBreakpoint;
            if ( newState != headerIsSticky ) {
                headerIsSticky = newState;
                if ( newState ) {
                    headerWrap.addClass( headerWrapClassSticky );
                } else {
                    headerWrap.removeClass( headerWrapClassSticky );
                }
            }
        });
    },

    initResponsiveTables: function() {
        jQuery(".page-single__content table").each(function(){
            jQuery(this).addClass('table');
            jQuery(this).wrapAll('<div class="table-responsive" />');
        });
        jQuery("#taxes-pay-table").each(function(){
            jQuery(this).addClass('table');
            jQuery(this).wrapAll('<div class="table-responsive" />');
        });
    },

    initFocusButtonContents: function() {
        var self = this;
        jQuery(".focus-button__item-link-content").on( "click", function(e) {
            e.preventDefault();
            console.log('clicked');
            var wrapperId = jQuery( this ).attr('href');
            jQuery(".focus-button__item").removeClass('active');
            jQuery(this).closest( ".focus-button__item" ).addClass('active');
            //jQuery('.page-single').hide();
            //jQuery('#focus-buttons__content-wrapper').show();
            jQuery( '.focus-buttons__content-wrap').hide();
            jQuery( wrapperId ).show();
            if ( jQuery( window ).width() > 768 ) {
                self._scrollToElementHelper( wrapperId, 200);
            } else {
                self._scrollToElementHelper( wrapperId, 140);
            }
            
        });
    },

    initFocusButtonScrollToContent: function( activated ) {
        var self = this;
        if ( activated ) {
            jQuery( document ).ready(function() {
                console.log( "ready!" );
                var wrapperId = jQuery( '.focus-buttons__content');
                if ( jQuery( window ).width() > 768 ) {
                    self._scrollToElementHelper( wrapperId, 200);
                } else {
                    self._scrollToElementHelper( wrapperId, 140);
                }
            });
        }
    },

    _scrollToElementHelper: function( wrapperId, offsetFix) {
        jQuery( 'html, body' ).animate({
                scrollTop: jQuery( wrapperId ).offset().top - offsetFix
        }, 2000);
    },

    /**
     * Initialization modile menu.
     * @use jquery.slicknav.js, slicknav.css
     * @return void
     */
    _initMobileMenu:function() {
        var mainBtn,
            closeClass = 'slicknav_btn--close',
            itemClass = 'slicknav_item',
            itemOpenClass = 'slicknav_item--open';

        jQuery( '#navigation' ).slicknav({
            label:'',
            prependTo:'.header__content',
            openedSymbol: '',
            closedSymbol: '',
            allowParentLinks:true,
            beforeOpen: function( target ) {
                if ( target.length ) {
                    if ( target[0] == mainBtn ) {
                        target.addClass( closeClass );
                    }else if ( target.hasClass( itemClass ) ) {
                        target.addClass( itemOpenClass );
                    }
                }
            },
            beforeClose: function( target ) {
                if( target.length ){
                    if( target[0] == mainBtn ) {
                        target.removeClass( closeClass );
                    }else if( target.hasClass( itemClass ) ) {
                        target.removeClass( itemOpenClass );
                    }
                }
            }
        });

        mainBtn = jQuery( '.slicknav_btn' );
        mainBtn = mainBtn.length ? mainBtn[0] : null;
    },

    /**
     * Initialization custom select box.
     *
     * @use bootstrap.min.js, bootstrap-select.min.js, bootstrap-select.min.css
     * @param String|jQuery elements
     * @return void
     */
    initSelectpicker: function( elements ) {
        var self = this,
            collection = elements ? jQuery( elements ) : null;

        if ( null === collection ) {
            collection = jQuery( 'select.selectpicker' )
                .add( '.widget select' ); // Use .add() to add select elements.
        }

        if ( ! collection  || collection.length < 1 ) {
            return false;
        }

        var liveAutoStartLimit =  this.selectLiveSearchAutoStart;
        if ( liveAutoStartLimit > 0 ) {
            collection.each(function() { // .not('[data-live-search]')
                if ( this.children.length >= liveAutoStartLimit  && ! this.hasAttribute( 'data-live-search' ) ) {
                    jQuery( this ).attr( 'data-live-search', true );
                }
            });
        }

        collection.each(function() {
                // var el = jQuery( this );
                // if ( el.attr('multiple') ) {
                //     el.selectpicker({selectedTextFormat:'static'});
                // } else {
                //     el.selectpicker();
                // }
                self._fixSelectpickerEmptyClass( this );
                //self._fixSelectpickerMultiSelectedText( this );
            })
            .on( 'change', function() {
                self._fixSelectpickerEmptyClass( this );
            }
        );
    },

    _fixSelectpickerEmptyClass:function( node ) {
        var el = jQuery( node ),
            isSelectpicker = el.data( 'selectpicker' ) ? true : false;
        if ( ! isSelectpicker ) {
            return;
        }
        var emptyClass = 'selectpicker--empty';
        if ( el.val() ) {
            el.selectpicker( 'setStyle', emptyClass, 'remove' );
        } else {
            el.selectpicker( 'setStyle', emptyClass, 'add' );
        }
    },

    _fixSelectpickerMultiSelectedText:function( node ) {
        var el = jQuery( node );
        if ( ! isSelectpicker ) {
            return;
        }

        if ( el.hasAttr('multiple') ) {
            el.selectpicker( {selectedTextFormat:'static'} );
        }
    },

    _initTabsStateFromHash:function( $ ) {
        if ( this.disableTabStateRestore || ! document.location.hash ) {
            return;
        }
        var hash = document.location.hash;
        if ( hash.search( 'accordion' ) < 0 ) {
            var tabLink = $( '.nav-tabs a[href="' + hash + '"]' );
            if ( tabLink.length ) {
                tabLink.tab( 'show' );
            }
        } else {
            var accordionLink = $( '.accordion__item a[href="' + hash + '"]' );
            if ( accordionLink.length ) {
                accordionLink.trigger( 'click' );
            }
        }
    },

    _makeDatepickerConfig:function( customOptions ) {
        if ( window.ThemeSDDatepickerCfg ) {
            return jQuery.extend( {}, window.ThemeSDDatepickerCfg, customOptions || {} );
        }
        return customOptions;
    },

    initResizeHandler: function( config ) {
        var cfg = jQuery.extend( {
            deviceType:'desktop',
            }, config || {});
        _sliderResizeHandler = function(){
            var windowWidth = jQuery( window ).width();
            var deviceType = cfg.deviceType;

            if (windowWidth >= 992) {
                deviceType = 'desktop';
            } else {
                deviceType = 'mobile';
            }

            var isNewValue = cfg.deviceType != deviceType;

            if ( isNewValue ) {
                if ( isNewValue ) {
                    cfg.deviceType = deviceType;
                    switch (cfg.deviceType) {
                        case 'desktop':
                            jQuery('#eem-slider').hide();
                            jQuery('#dee-slider').show();
                            break;
                        case 'mobile':
                            jQuery('#dee-slider').hide();
                            jQuery('#eem-slider').show();
                            break;
                    }
                }
            }
        };
        jQuery( window ).on( 'resize', _sliderResizeHandler );//.trigger('resize');
    },

    /**
     * Create swiper sliders.
     *
     * @param numSlides config
     */
    makeSwiper: function( config ) {
        var cfg = jQuery.extend( {
            containerSelector:'',
            slidesNumber:4,
            navPrevSelector:'',
            navNextSelector:'',
            sliderElementSelector:'.swiper-slider',
            slideSelector: '.swiper-slide',
            widthToSlidesNumber:function( windowWidth, slidesPerView ) {
                var result = slidesPerView;
                if (windowWidth > 992) {

                } else if(windowWidth > 768) {
                    //result = Math.max(3, Math.ceil(slidesPerView / 2));
                    result = Math.ceil(slidesPerView / 2);
                } else if (windowWidth > 670) {
                    result = 2;
                } else {
                    result = 1;
                }

                return result;
            }
        }, config || {} );
        if ( ! cfg.containerSelector ) {
            return null;
        }

        var numSlides = cfg.slidesNumber,
            container = jQuery( cfg.containerSelector ),
            sliderElement = container.find( cfg.sliderElementSelector ),
            realSlidesNumber = sliderElement.find( cfg.slideSelector ).length,
            swiperCfg = {
                slidesPerView: numSlides,
                spaceBetween: 30,
                loop: numSlides < realSlidesNumber
                //,loopedSlides: 0
            };
        if ( cfg.swiperOptions ) {
            jQuery.extend( swiperCfg, cfg.swiperOptions );
        }

        var swiper = new Swiper( sliderElement, swiperCfg ),
            navButtons = null,
            naviPrev = null,
            naviNext = null;
        if( cfg.navPrevSelector ) {
            naviPrev = container.find( cfg.navPrevSelector );
            if ( naviPrev.length ) {
                naviPrev.on( 'click', function( e ) {
                    e.preventDefault();
                    swiper.slidePrev();
                });
                navButtons = jQuery( naviPrev );
            }
        }
        if ( cfg.navNextSelector ) {
            naviNext = container.find( cfg.navNextSelector );
            if ( naviNext.length ) {
                naviNext.on( 'click', function( e ) {
                    e.preventDefault();
                    swiper.slideNext();
                });
                navButtons = navButtons ? navButtons.add( naviNext ) : jQuery( naviNext );
            }
        }

        var isFirstCall = true,
        _resizeHandler = function(){
            var slidesPerView = numSlides;

            if ( cfg.widthToSlidesNumber && 'function' == typeof cfg.widthToSlidesNumber ) {
                slidesPerView = cfg.widthToSlidesNumber( jQuery( window ).width(), numSlides );
            }

            var isNewValue = swiper.params.slidesPerView != slidesPerView;

            if ( isFirstCall || isNewValue ) {
                if ( isNewValue ) {
                    swiper.params.slidesPerView = slidesPerView;
                    swiper.update();
                }

                if ( navButtons ) {
                    if ( slidesPerView < realSlidesNumber && realSlidesNumber > 1 ) {
                        navButtons.show();
                    } else {
                        navButtons.hide();
                    }
                }
                if ( isFirstCall ) {
                    isFirstCall = false;
                }
            }
        };
        jQuery( window ).on( 'resize', _resizeHandler );//.trigger('resize');
        _resizeHandler();
    },

    /**
     * Create slideshows.
     *
     * @param numSlides config
     */
    makeSlider: function( config ) {
        var cfg = jQuery.extend( {
            sliderSelector:'',
            slideTransitionType:'',
            nextSelector:'',
        }, config || {}),
        sliderCfg = {
            autoplay: true,
            dots: false,
            arrows: false,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            slidesToShow: 1,
            slidesToScroll: 1,
        };
        if ( ! cfg.sliderSelector && ! cfg.slideTransitionType ) {
            return null;
        }

        if ( cfg.sliderOptions ) {
            jQuery.extend( sliderCfg, cfg.sliderOptions );
        }

        var slider = jQuery( cfg.sliderSelector ).slick( sliderCfg );

        if ( 'click' === cfg.slideTransitionType ) {
            jQuery( cfg.nextSelector ).on( 'click', function(){
                slider.slick('slickNext');
            });
        }
    },

    /**
     * Modify slideshow delay.
     *
     * @param numSlides config
     */
    //modifySliderDelay: function( slider, currentSlide, imagePauses ) {
    //    var self = this;
    //    var cfg = jQuery.extend( {
    //        mode: 'horizontal',
    //        infiniteLoop: true,
    //        auto: false,
    //        autoStart: false,
    //        autoDirection: 'next',
    //        autoHover: true,
    //        autoControls: false,
    //        pager: true,
    //        pagerType: 'full',
    //        controls: true,
    //        captions: true,
    //        speed: 500,
    //        startSlide: startSlide,
    //        onSlideAfter: function($el,oldIndex, newIndex){
    //            self( slider, currentSlide, imagePauses[newIndex] );
    //        }
    //    }, config || {});
    //
    //    setTimeout( slider.goToNextSlide(), imagePauses[currentSlide] );
    //
    //    slider.reloadSlider( cfg );
    //},

    initParallax: function( selector ) {
        if ( ! selector ) {
            selector = '.parallax-image';
        }

        jQuery( selector ).each(function() {
            var element = jQuery( this ),
                speed = element.data( 'parallax-speed' );
            element.parallax( "50%", speed ? speed : 0.4 );
        });
    },

    _initScrollTop: function( $ ) {
        var document = $( 'body, html' ),
            link = $( '.footer__arrow-top' ),
            windowHeight = $( window ).outerHeight(),
            documentHeight = $( document ).outerHeight();

        if( windowHeight >= documentHeight ) {
            link.hide();
        }

        link.on( 'click', function( e ) {
            e.preventDefault();

            document.animate({
                scrollTop: 0
            }, 800 );
        });
    },

    init_faq_question_form: function( formSelector ) {
        var form = jQuery( formSelector ),
            formContent = jQuery( '.form-block__content' ),
            formElMsgSuccess = jQuery( '.form-block__validation-success' );

        if ( form.length < 1 ) {
            return;
        }

        var noticeWrapper = form.find( '.form-block__validation-error' ),
            resetFormErrors = function() {
                form.find( '.field-error-msg' ).remove();
                noticeWrapper.html( '' );
            };

        Theme.FormValidationHelper.initTooltip();

        form.on( 'submit', function( e ) {
            //e.preventDefault();
            var dataArray = form.serializeArray(),
                formData = {};

            jQuery.each( dataArray, function( i, item ) {
                formData[item.name] = item.value;
            });

            jQuery.ajax({
                url: form.attr( 'action' ),
                data: formData,
                method:'POST',
                error:function( responseXHR ) {
                    var res = responseXHR.responseJSON ? responseXHR.responseJSON : {};
                    resetFormErrors();
                    Theme.FormValidationHelper.formReset( formSelector );

                    if ( res.field_errors ) {
                        jQuery.each( res.field_errors, function( fieldKey, message ) {
                            var el = form.find( '[name*="[' + fieldKey + ']"]' );
                            el.tooltip( 'destroy' );
                            setTimeout(function() {
                                Theme.FormValidationHelper.initTooltip( el );
                                Theme.FormValidationHelper.itemMakeInvalid( el, message );
                            }, 200 );
                        });
                    }

                    if ( res.error ) {
                        noticeWrapper.html( '<i class="fa fa-exclamation-triangle"></i>' + res.error );
                    }
                },
                success:function( res ) {
                    resetFormErrors();
                    Theme.FormValidationHelper.formReset( formSelector );
                    if ( res.message ) {
                        formContent.fadeOut( 400, function() {
                            formElMsgSuccess.html( res.message );
                        });
                    }
                    if ( res.success ) {
                        form[0].reset();
                    }
                }
            });

            return false;
        });
    },

    /**
     * Initilize sharrre buttions.
     * @param  object config
     * @return void
     */
    initSharrres: function( config ) {
        if ( ! config || typeof config != 'object' || ! config.itemsSelector ) {
            //throw 'Parameters error.';
            return;
        }

        var curlUrl = config.urlCurl ? config.urlCurl : '',
            elements = jQuery( config.itemsSelector );

        if ( elements.length < 1 ) {
            return;
        }

        var initSharreBtn = function() {
            var el = jQuery( this ),
                url = el.parent().data( 'urlshare' ),
                imageUrl = el.parent().data( 'imageshare' ),
                curId = el.data( 'btntype' ),
                curConf = {
                    urlCurl: curlUrl,
                    enableHover: false,
                    enableTracking: true,
                    url: ( '' !== url ) ? url : document.location.href,
                    share: {},
                    buttons: {
                        pinterest: {
                            media: imageUrl
                        },
                        //vk: {
                        //    image: imageUrl
                        //}
                    },
                    click: function( api, options ) {
                        api.simulateClick();
                        api.openPopup( curId );
                    }
                };

            curConf.share[curId] = true;
            el.sharrre( curConf );
        };
        elements.each( initSharreBtn );

        // To prevent jumping to the top of page on click event.
        setTimeout(function() {
            jQuery( 'a.share,a.count', config.itemsSelector ).attr( 'href', 'javascript:void(0)' );
        }, 1500 );
    },

    /**
     * Initilize Search Form in popup.
     * @use jquery.magnific-popup.min.js magnific-popup.css
     * @return void
     */
    initSerchFormPopup: function( config ) {
        var classHide = 'search-form-popup--hide',
            cfg = jQuery.extend({
                placeholder_text: 'Type in your request...'
            }, config || {});

        jQuery( '.popup-search-form' ).magnificPopup({
            type: 'inline',
            preloader: false,
            focus: '#s',
            //closeMarkup: '<button title="%title%" type="button" class="mfp-close"><i class="fa fa-times"></i></button>',
            showCloseBtn: false,
            removalDelay: 500, // Delay removal by X to allow out-animation.
            fixedContentPos: false,
            callbacks: {
                beforeOpen: function() {
                    this.st.mainClass = this.st.el.attr( 'data-effect' );
                },
                open: function() {
                    this.content.removeClass( classHide );
                    jQuery( '.mfp-close' ).on( 'click', function() {
                        jQuery.magnificPopup.close();
                    });
                },
                close: function() {
                    this.content.addClass( classHide );
                }
            },
            midClick: true // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
        });

        if ( cfg.placeholder_text ) {
            jQuery( '.search-form-popup' )
                .find( '.search-field' )
                .attr( 'placeholder', cfg.placeholder_text );
        }
    },

};

/**
 * Gallery plugin.
 * Enables filtering and pagination functionalities.
 *
 * @param {jQuery|selector} container
 * @param {Oject}           config
 */
Theme.Gallery = function( container, config ) {
    if ( config ) {
        jQuery.extend( this, config );
    }

    this.cnt = jQuery( container );

    this._init();
};

Theme.Gallery.prototype = {

    paginationSl: '.pagination',
    imagesContainerSl:'.gallery__items',
    filterButtonsSl: '.gallery__navigation a',
    filterButtonActionClass: 'gallery__navigation__item-current',
    animationClass: 'animated',
    _jPager:null,

    /**
     * Settings for jPages plugin
     *
     * @see initPagination
     * @type {Object}
     */
    paginationConfig:{
        // container: '#galleryContatiner1 .gallery__items',
        perPage: 9,
        animation:'fadeIn',
        previous: '',
        next: '',
        minHeight: false
    },

    getPagerEl:function() {
        return this.paginationSl ? this.cnt.find( this.paginationSl ) : null;
    },

    getImagesContEl:function() {
        return this.cnt.find( this.imagesContainerSl );
    },

    /**
     * Initilize gallery.
     * @use jquery.swipebox.js, swipebox.css, jPages.js
     *
     * @return void
     */
    _init: function( contSelector ) {
        if ( this.cnt.length < 1 ) {
            // throw 'configuration error';
            return;
        }

        this.cnt.find( '.swipebox' ).swipebox({
            useSVG: true,
            hideBarsDelay: 0,
            loopAtEnd: true
        });

        this._initPagination();
        this._initFilter();
    },

    /**
     * Initilize gallery pagination.
     *
     * @use jPages.js
     * @return void
     */
    _initPagination:function() {
        var paginationEl = this.getPagerEl();

        if ( ! paginationEl || paginationEl.length < 1 ) {
            return;
        }

        if ( this._jPager ) {
            this._jPager.jPages( 'destroy' );
        }

        this._jPager = paginationEl.jPages(
            jQuery.extend({
                    container : this.getImagesContEl()
                },
                this.paginationConfig
            )
        );
    },

    /**
     * Initilize gallery filter.
     * @param container selector, wrap gallery
     * @param filterButtons selector
     * @return void
     */
    _initFilter:function( container, filterButtons ) {
        var filterButtonsEl = this.filterButtonsSl ? this.cnt.find( this.filterButtonsSl ) : null;
        if ( ! filterButtonsEl && ! filterButtonsEl.length ) {
            return;
        }

        var self = this,
            items = this.getImagesContEl().children();

        /**
         * Items animation use jPages animation, when pagination off.
         */
        var _itemsAnimation = function() {
            if( self._jPager ) {
                return;
            }

            var customAnimationClass = self.paginationConfig.animation;
            if( ! customAnimationClass ) {
                return;
            }

            var animationClasses = self.animationClass + ' ' + customAnimationClass;
            items.addClass( animationClasses );
            setTimeout( function() {
                items.removeClass( animationClasses );
            }, 600 );
        };

        _itemsAnimation();

        filterButtonsEl.on( 'click', function( e ) {
            e.preventDefault();
            var idFilter = jQuery( this ).data( 'filterid' ),
                btnActiveClass = self.filterButtonActionClass;

            filterButtonsEl.parent()
                .removeClass( btnActiveClass );

            jQuery( this ).parent()
                .addClass( btnActiveClass );

            if( ! idFilter ) {
                idFilter = 'all';
            }

            var filtered = idFilter == 'all' ? items : items.filter( '[data-filterid*="' + idFilter + '"]' ),
                needShow = filtered,// filtered.filter(':not(:visible)'),
                needHide = items.not( filtered );//.filter(':visible');

            if ( ! needShow.length && ! needHide.length ) {
                return; // Nothing to do.
            }

            _itemsAnimation();

            needHide.hide();
            needShow.show();

            if ( self._jPager ) {
                self._initPagination();
            }
        });
    }
};

/**
 * Form validation helper.
 * @use bootstrap.min.js, bootstrap-custom.css
 */
Theme.FormValidationHelper = {
    options: {
        itemsValidationClass: 'form-validation-item',
        emailValidationRegex: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/,
    },

    errors: {
        requiredField: 'Fill in the required field.',
        emailInvalid: 'Email invalid.',
    },

    init: function() {
        this.initTooltip(
            jQuery( '.' + this.options.itemsValidationClass )
        );
        this.initContactForm7CustomValidtion();
    },

    /**
     * Initialization tooltips.
     * @param selector|jQuery items
     * @return void
     */
    initTooltip: function( items ) {
        if ( 'string' == typeof items ) {
            items = jQuery( items );
        }else if ( 'undefined' == typeof items ) {
            items = jQuery( '.' + this.options.itemsValidationClass );
        }

        if ( items.length < 1 ) {
            return items;
        }

        items
            .tooltip({
                trigger: 'manual',
                animation: true,
                delay: 0
            })
            .on( 'focus', function() {
                jQuery( this ).tooltip( 'destroy' );
            });
        return items;
    },

    /**
     * Form items hide tooltip.
     * @param selector wrap
     * @return void
     */
    formReset: function( wrap ) {
        var wrap = jQuery( wrap );

        if( 0 == wrap.length ) {
            return null;
        }

        wrap.find( '.' + this.options.itemsValidationClass )
            .tooltip( 'destroy' )
            .attr( 'data-original-title', '' )
            .attr( 'title', '' );
    },

    /**
     * Item show tooltip with error.
     * @parm jQuery object item
     * @parm  string title
     * @return void
     */
    itemMakeInvalid: function( item, title ) {
        item
            .attr( 'data-original-title', title )
            .tooltip( 'show' );
    },

    /**
     * Validation items.
     * @param object items
     * @return integer errors count
     */
    itemsValidation: function( items ) {
        var self = this,
            errorsCount = 0;

        jQuery.each( items, function( i, item ) {
            var item = jQuery( item ),
                itemVal = item.val(),
                itemName = item.attr( 'name' ),
                itemType = item.attr( 'type' );

            if( ! itemVal.trim() ) {
                errorsCount++;
                self.itemMakeInvalid( item, ( 'undefined' != self.errors[ 'requiredField' ] ? self.errors[ 'requiredField' ] : '' ) );
            } else if( 'email' == itemType || 'email' == itemName || item.hasClass( 'yks-mc-input-email-address' ) ) { // Change to mailchimp for wp.
                if( ! self.options.emailValidationRegex.test( itemVal ) ) {
                    errorsCount++;
                    self.itemMakeInvalid( item, ( 'undefined' != self.errors[ 'emailInvalid' ] ? self.errors[ 'emailInvalid' ] : '' ) );
                }
            }
        });

        return errorsCount;
    },

    /**
     * Initialization custom validation for plugin contact form 7.
     * @return void
     */
    initContactForm7CustomValidtion: function() {
        var self = this,
            wrapForm = jQuery( '.wpcf7' ),
            itemsValidationClass = this.options.itemsValidationClass;

        wrapForm.each(function() {
            var wrapFromId = jQuery( this ).attr( 'id' ),
                wrapFormEl = jQuery( '#' + wrapFromId );

            if( wrapFormEl.length < 1 ) {
                return;
            }

            var items = wrapFormEl
                .find( '.wpcf7-validates-as-required' )
                .addClass( itemsValidationClass );

            self.initTooltip( items );

            wrapFormEl.find( 'form' ).on( 'ajaxComplete', function( e ) {
                self.initTooltip( items );
                jQuery( this ).find( '.wpcf7-not-valid' ).each(function( i, item ) {
                    var item = jQuery( item ),
                        itemErrorText = item.siblings( '.wpcf7-not-valid-tip' ).text();

                    switch( itemErrorText ){
                        case 'Please fill in the required field.':
                            itemErrorText = 'undefined' != self.errors[ 'requiredField' ] ? self.errors[ 'requiredField' ] : '';
                            break;
                        case 'Email address seems invalid.':
                            itemErrorText = 'undefined' != self.errors[ 'emailInvalid' ] ? self.errors[ 'emailInvalid' ] : '';
                            break;
                    }

                    self.itemMakeInvalid( item, itemErrorText );
                });
            });
        });
    },

    /**
     * Initialization custom validation for plugin Easy MailChimp Forms.
     *
     * @param selector wrapFormId
     * @return void
     */
    initMailChimpCustomValidtion: function( wrapFormId ) {
        var self = this,
            itemsValidationClass = this.options.itemsValidationClass,
            wrapForm = jQuery( '#' + wrapFormId );

        if ( wrapForm.length < 1 ) {
            return;
        }

        var items = wrapForm.find( '.yks-require, input[required="required"]' )
            .addClass( itemsValidationClass );

        this.initTooltip( items );

        wrapForm.find( 'form' )
            .find( '[type="submit"], [type="image"]' )
            .on( 'click', function( e ) {
                self.initTooltip( items );
                if ( self.itemsValidation( items ) > 0 ) {
                    e.preventDefault();
                }
            });
    },

    /**
     * Initialization custom validation for forms.
     *
     * @param  selector wrapFormId
     * @return void
     */
    initValidationForm: function( wrapFormId ) {
        var self = this,
            itemsValidationClass = this.options.itemsValidationClass,
            wrapForm = jQuery( '#' + wrapFormId );

        if ( 0 == wrapForm.length ) {
            return;
        }

        this.initTooltip(
            wrapForm.find( '.' + this.options.itemsValidationClass )
        );

        wrapForm.find( 'form' ).on( 'submit', function( e ) {

            // e.preventDefault();
            self.formReset( wrapForm );

            var items = wrapForm.find( '.' + itemsValidationClass ),
                formErrors = 0;

            formErrors = self.itemsValidation( items ) ;

            // validation success
            if( 0 == formErrors ) {
                //TODO complete
            }
        });
    }
};

Theme.formatter = {
    configs:{},

    setConfig:function( format, cfg ) {
        this.configs[format] = cfg;
    },

    formatMoney:function( amount ) {
        var cfg = jQuery.extend({
            //mask: '{amount}',
            decimal_separator: '.',
            thousand_separator: ',',
            decimals: 2
        }, this.configs.money[ 'money' ] ? this.configs[ 'money' ] : {});

        var formatted = this.formatNumber( amount, cfg.decimals, 3, cfg.thousand_separator, cfg.decimal_separator );

        if ( cfg.mask ) {
            var completed = cfg.mask.replace( '{amount}', formatted );
            if ( completed != cfg.mask ) {
                return completed;
            }
        }

        return formatted;
    },

    formatNumber: function( number, decimals, th, thSep, decSep ) {
        var re = '\\d(?=(\\d{' + ( th || 3 ) + '})+' + ( decimals > 0 ? '\\D' : '$' ) + ')',
            number = parseFloat(number);
            num = number.toFixed( Math.max( 0, ~~decimals ) );

        return ( decSep ? num.replace( '.', decSep ) : num ).replace( new RegExp( re, 'g' ), '$&' + ( thSep || ',' ) );
    },

    /**
     * Allows format strings with %s and %d placeholders.
     *
     * @return String
     */
    sprintf:function() {
        var args = arguments,
            string = args[0],
            i = 1;

        return string.replace( /%((%)|s|d)/g, function( m ) {
            // m is the matched format, e.g. %s, %d
            var val = null;
            if ( m[2] ) {
                val = m[2];
            } else {
                val = args[i];
                switch ( m ) {
                    case '%d':
                        val = parseFloat( val );
                        if ( isNaN( val ) ) val = 0;
                        break;
                }
                i++;
            }
            return val;
        });
    },

    time:function( timeIn24Hours, format ) {
        if ( ! format || 'hh:ii' == format ) {
            return timeIn24Hours;
        }

        var parts = timeIn24Hours.split( ':' ),
            result = format.replace( 'ii', parts[1] ),
            h = parseInt( parts[0], 10 ),
            is12HoursFormat = format.search( 'A' ) >= 0,
            is12HoursFormatLowercase = format.search( 'a' ) >= 0,
            newHourValue = h;


        if ( is12HoursFormat || is12HoursFormatLowercase ) {
            var suffix = h >= 12 ? ' PM' : ' AM';
            result = result.replace( is12HoursFormatLowercase ? 'a' : 'A', is12HoursFormatLowercase ? suffix.toLowerCase() : suffix );
            if ( newHourValue >= 12 ) {
                newHourValue -= 12;
            }
            if ( 0 == newHourValue ) {
                newHourValue = 12;
            }
        }

        if ( format.search( 'hh' ) >= 0 ) {
            result = result.replace( 'hh', ( newHourValue < 10 ? '0' : '' ) + newHourValue );
        } else {
            result = result.replace( 'h', newHourValue );
        }

        return result;
    }
};

jQuery(function( $ ) {
    Theme.init( $ );
});
