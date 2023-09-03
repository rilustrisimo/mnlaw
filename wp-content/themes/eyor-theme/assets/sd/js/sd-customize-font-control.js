(function() {

    var api = wp.customize;

    // Class already defined
    if ( api.SdFontControl ) {
        return;
    }

    api.SdFontControl = api.Control.extend({
        /**
         * @see _getConfig
         * @type {String}
         */
        fieldConfigVariablePrefix: '_SdCustomizeFontControl',

        initialize: function( id, options ) {
            api.Control.prototype.initialize.call( this, id, options );
            this._initSelectors();
        },

        _initSelectors: function() {
            var self = this;
            this.selectors = this.container.find( 'select' );
            this.selectors.on( 'change', function() {
                var subKey = jQuery( this ).data( 'subkey' ),
                    newValue = jQuery( this ).val();
                if ( 'family' == subKey ) {
                    self._reloadListsForFamily( newValue );
                }
                self._setSubKey( subKey, newValue );
            } );
        },

        _reloadListsForFamily:function( family ) {
            var lists = this.getStylesAndWeightsForFamily( family );
            for ( var listName in lists ) {
                this._setupListOptions( listName, lists[listName] );
            }
        },

        getStylesAndWeightsForFamily:function( family ) {
            var fontSet = this._getConfig( 'font_set' ),
                kurKey = fontSet[family] ? fontSet[family] : {},
                result = {
                    style: ['normal'],
                    weight: ['regular']
                };

            return jQuery.extend( result, kurKey );
        },

        _getConfig:function( option ) {
            var confName = this.fieldConfigVariablePrefix + this.id;
            return window[confName] && window[confName][option] ? window[confName][option] : null;
        },

        _getSelectByKey:function( key ) {
            return this.selectors.filter( '[data-subkey="'+key+'"]' );
        },

        _setSubKey:function( subKey, value, silent ) {
            var curSet = this.setting() || {};
            if ( value && curSet[subKey] && value == curSet[subKey] ) {
                return;
            } else if ( ! value && ! curSet[subKey] ) {
                return;
            }
            var newVal = {};
            jQuery.extend( newVal, curSet );
            newVal[subKey] = value;

            this.setting.set( newVal );

            // To mark save changes button as active
            if ( ! silent ) {

                //this.setting.preview();
                //api.trigger('change');

            }
        },

        _setupListOptions:function( listName, set ) {
            var selector = this._getSelectByKey( listName );
            if ( selector.length < 1 ) {
                return;
            }

            // If set is array - need convert it into object
            var map = {};
            if ( set.length ) {
                for ( var i = 0; i < set.length; i++ ) {
                    map[set[i]] = set[i];
                }
            } else {
                map = set;
            }

            var curVal = selector.val(),
                options = [],
                defVal = null,
                hasCurVal = false;

            for (var val in map ) {
                options.push( '<option value="' + val + '">' + map[val] + '</option>' );
                if ( ! defVal ) {
                    defVal = val;
                }
                if ( ! hasCurVal && val == curVal ) {
                    hasCurVal = true;
                }
            }

            selector.find( 'option' ).remove();
            jQuery( options.join('') ).appendTo( selector );
            if ( hasCurVal ) {
                selector.val( curVal );//.trigger('change');
            } else {
                selector.val( defVal ).trigger( 'change' );
            }

            return true;
        }
    });

    api.controlConstructor['swishdesign_font'] = api.SdFontControl;

})(); // End of the scope call function
