jQuery( document ).ready(function( $ ) {

    var parseAlphaValue = function( value ) {

        // If in format RGBA - grab A channel value
        if ( value.match( /rgba\(\d+\,\d+\,\d+\,([^\)]+)\)/ ) ) {
            var alphaVal = parseFloat( value.match( /rgba\(\d+\,\d+\,\d+\,([^\)]+)\)/ )[1] ) * 100;
            return parseInt( alphaVal );
        } else {
            return 100;
        }
    };

    Color.prototype.toString = function( removeAlpha ) {
        if ( 'no-alpha' == removeAlpha ) {
            return this.toCSS( 'rgba', '1' ).replace( /\s+/g, '' );
        }
        if ( this._alpha < 1 ) {
            return this.toCSS( 'rgba', this._alpha ).replace( /\s+/g, '' );
        }
        var hex = parseInt( this._color, 10 ).toString( 16 );
        if ( this.error ) return '';
        if ( hex.length < 6 ) {
            for ( var i = 6 - hex.length - 1; i >= 0; i-- ) {
                hex = '0' + hex;
            }
        }
        return '#' + hex;
    };

    $( '.sdcolor-color-control' ).each(function() {
        var $control = $( this ),
            value = $control.val().replace( /\s+/g, '' );

        // Manage Palettes.
        var paletteInput = $control.attr( 'data-palette' );
        if ( 'false' == paletteInput || false == paletteInput ) {
            var palette = false;
        } else if ( 'true' == paletteInput || true == paletteInput ) {
            var palette = true;
        } else {
            var palette = $control.attr( 'data-palette' ).split( "," );
        }
        var $alphaSlider,
            defaultColor = $control.data( 'default-color' );

        $control.wpColorPicker({ // Change some things with the color picker.
            // clear: function(event, ui) {
            // 	// TODO reset Alpha Slider to 100
            // },
            change: function( event, ui ) {

                // Send ajax request to wp.customizer to enable Save & Publish button.
                var _newValue = $control.val(),
                    key = $control.attr( 'data-customize-setting-link' );

                wp.customize( key, function( obj ) {
                    obj.set( _newValue );
                } );

                // Change the background color of our transparency container whenever a color is updated.
                var $transparency = $control.parents( '.wp-picker-container:first' ).find( '.transparency' );

                // We only want to show the color at 100% alpha
                $transparency.css( 'backgroundColor', ui.color.toString( 'no-alpha' ) );

                // When returning to default need update alpha slider as well
                if ( defaultColor == _newValue ) {
                    var newAlphaVal = parseAlphaValue( _newValue );
                    $alphaSlider
                        .slider( 'value', newAlphaVal )
                        .find( '.ui-slider-handle' ).text( newAlphaVal );
                }
            },
            palettes: palette // Remove the color palettes.
        });
        $( '<div class="tdcolor-alpha-container"><div class="slider-alpha"></div><div class="transparency"></div></div>' ).appendTo( $control.parents( '.wp-picker-container' ) );
        $alphaSlider = $control.parents( '.wp-picker-container:first' ).find( '.slider-alpha' );

        var alphaVal = parseAlphaValue( value );
        $alphaSlider.slider({
            slide: function( event, ui ) {
                $( this ).find( '.ui-slider-handle' ).text( ui.value ); // Show value on slider handle
                // Send ajax request to wp.customizer to enable Save & Publish button
                var _newValue = $control.val();
                var key = $control.attr( 'data-customize-setting-link' );
                wp.customize( key, function( obj ) {
                    obj.set( _newValue );
                } );
            },
            create: function( event, ui ) {
                var v = $( this ).slider( 'value' );
                $( this ).find( '.ui-slider-handle' ).text( v );
            },
            value: alphaVal,
            range: 'max',
            step: 1,
            min: 1,
            max: 100
        }); // Slider
        $alphaSlider.slider().on( 'slidechange', function( event, ui ) {
            var newAlphaVal = parseFloat( ui.value ),
                iris = $control.data( 'a8cIris' ),
                colorPicker = $control.data( 'wpWpColorPicker' );
            iris._color._alpha = newAlphaVal / 100.0;
            $control.val( iris._color.toString() );
            colorPicker.toggler.css({
                backgroundColor: $control.val()
            });

            // Fix relationship between alpha slider and the 'side slider not updating.
            var getVal = $control.val();
            $( $control ).wpColorPicker( 'color', getVal );
        } );
    }); // Each
});
