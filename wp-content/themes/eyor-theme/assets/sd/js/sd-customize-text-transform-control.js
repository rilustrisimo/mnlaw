(function() {

    var api = wp.customize;

    if ( api.SdTextTransformControl ) {
        return;
    }

    api.SdTextTransformControl = api.Control.extend({
        initialize: function( id, options ) {
            api.Control.prototype.initialize.call( this, id, options );
            this._initSelectors();
        },

        _initSelectors: function() {
            this.inputs = this.container.find( 'select' );
            var self = this;
            this.inputs.on( 'change', function() {
                var subKey = jQuery( this ).data( 'subkey' ),
                    newValue = jQuery( this ).val();
                self._setSubKey( subKey, newValue );
            } );
        },

        _setSubKey:function( subKey, value, silent ) {
            var curSet = this.setting() || {};
            if ( value && curSet[subKey] && value == curSet[subKey] ) {
                return;
            } else if ( ! value && ! curSet[subKey] ) {
                return;
            }
            var newValue = jQuery.extend( {}, curSet );
            newValue[subKey] = value;
            this.setting.set( newValue );

            // To mark save changes button as active
            if ( ! silent ) {
                //this.setting.preview();
                //api.trigger( 'change' );
            }
        }
    });

    api.controlConstructor['swishdesign_text_transform'] = api.SdTextTransformControl;

})(); // End of the scope call function
