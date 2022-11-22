(function($) {
    'use strict';

    /**
     * El código JavaScript para las páginas públicas deben
     * estar en este archivo
     *
     * Nota: Se asume que escribirás código jQuery aquí, por lo que la
     * referencia de la función $ se ha preparado para el uso dentro
     * de esta función
     *
     * Esto te permitirá definir "handlers", para cuando tu DOM esté listo:
     *
     * $(function() {
     *
     * });
     *
     * Cuando la ventana ha cargado:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...y/u otras posibilidades.
     *
     * Idealmente, no se considera una buena práctica añadir más de un
     * DOM-ready o window-load handler para una página en particular.
     * Aunque scripts del core de WordPress, Plugins y Temas practican
     * esto, nosotros debemos luchar para marcar un mejor ejemplo
     * en nuestro trabajo.
     */
    //* globales default *//
    const _gp_geo_lng = '-99.12766';
    const _gp_geo_lat = '19.42847';
    const _gp_geo_address_short = 'CDMX, CP 06000';
    const _gp_geo_address_long = 'Talavera, República de El Salvador, Centro, Cuauhtémoc, 06000 Ciudad de México, CDMX';
    const _gp_tienda_default_id = 1;
    const _gp_tienda_default_nombre = 'GP Santa Fe I';
    //* ---- *//
    $(document).ready(function() {
        $("#_gp_autocompletado").keydown(function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });
        let cookie = "";
        const direccion = document.getElementsByClassName("gp_direccion");
        const tienda = document.getElementsByClassName("gp_tienda_fav");

        if (gpGetCookie('_gp_geo_address_short')) {
            cookie = decodeURIComponent(gpGetCookie('_gp_geo_address_short'));
        } else {
            cookie = _gp_geo_address_short;
        }

        for (var i = 0; i < direccion.length; i++) {
            direccion[i].innerText = "Enviar a " + cookie;
        }
        cookie = "";

        if (gpGetCookie('_gp_tienda_favorita_nombre')) {
            cookie = decodeURIComponent(gpGetCookie('_gp_tienda_favorita_nombre'));
        } else {
            cookie = decodeURIComponent(_gp_tienda_default_nombre);
        }

        for (var i = 0; i < tienda.length; i++) {
            tienda[i].innerText = "Recoger en " + cookie;
        }

        let doc_address_long = "";

        if (gpGetCookie('_gp_geo_address_long')) {
            doc_address_long = decodeURIComponent(gpGetCookie('_gp_geo_address_long'));
        } else {
            doc_address_long = _gp_geo_address_long;
        }

        //* sección para single-product
        $('[name="qc-add-to-cart"').remove();
        $('[name="add-to-cart"').remove();

        const z = document.getElementsByClassName("gp_de_ubicacion");
        for (var i = 0; i < z.length; i++) {
            z[i].innerText = decodeURIComponent(doc_address_long);
        }

        //gp_login_button();

        if ($("#gp_div_disp").length > 0) {
            let lat = _gp_geo_lat;
            let lng = _gp_geo_lng;
            if (gpGetCookie('_gp_geo_lat') && gpGetCookie('_gp_geo_lng')) {
                lat = gpGetCookie('_gp_geo_lat');
                lng = gpGetCookie('_gp_geo_lng');
            }

            let addrs_long = _gp_geo_address_long;
            if (gpGetCookie('_gp_geo_address_long')) {
                addrs_long = decodeURIComponent(gpGetCookie('_gp_geo_address_long'));
            }
            let tienda_fav = _gp_tienda_default_id;
            if (gpGetCookie('_gp_tienda_favorita_id')) {
                tienda_fav = gpGetCookie('_gp_tienda_favorita_id');
            }

            console.log(lat, lng);
            const request = new XMLHttpRequest();
            request.open('POST', var_ajax_disponibilidad.url, true);
            request.onload = function() {
                const response = JSON.parse(this.response);
                if (typeof response === 'object') {
                    $("#gp_div_disp").html(response[0]);
                    $("#gp_div_msi").html(response[1]);
                    $("#modal_disp_tiendas").html(response[2]);
                    $("#modal_disp_prod").html(response[3]);
                }
            }
            request.onerror = function() {
                $("#gp_div_disp").html("<div class='gp-disponibilidad-container'><p>Algo salió mal, inténtelo más tarde. Code: WJS-200</p></div>");
                console.log("Algo salio mal en la peticion", this.error);
            }
            const data = new FormData();
            const producto = $("#id_ps").val();
            data.append('action', var_ajax_disponibilidad.action);
            data.append('id_prod', producto);
            data.append('lat', lat);
            data.append('lng', lng);
            data.append('addrs_long', addrs_long);
            data.append('tienda_fav', tienda_fav);
            request.send(data);

            $('body').on('click', '#gp_recibir_domicilio_w', function(e) {
                e.preventDefault();
                $("#gp_address").click();
            });
            $('body').on('click', '#gp_cambiar_sucursal', function(e) {
                e.preventDefault();
                $("#btn_tiendas_disp").click();
            });
            $('body').on('click', '#domicilio', function() {
                gpCookie('_gp_id_tipo_envio', 'domicilio', 1);
                $("#gp_single_product_button").removeClass();
                let condicion = $("#condicion").val();
                if (condicion.startsWith('P')) {
                    if ($("#gp_apartalo_con").length > 0) {
                        $("#gp_apartalo_con").html('Apártalo con <span class="woocommerce-Price-currencySymbol">$</span><bdi>' + $("#gp_domicilio_apartalo").val() + '</bdi>');
                    } else {
                        $("p.price.product-page-price.price-on-sale.gp_precio_ps").after('<span id="gp_apartalo_con">Apártalo con <span class="woocommerce-Price-currencySymbol">$</span><bdi>' + $("#gp_domicilio_apartalo").val() + '</bdi></span>');
                    }
                    $("#gp_single_product_button").addClass('qc-add-to-cart-button button alt');
                    $("#gp_single_product_button").attr('name', 'qc-add-to-cart');
                    $("#gp_single_product_button_txt").text('Apártalo en preventa ahora');
                } else {
                    var idRadio = $("input[name='garantia']:checked").val();
                    if (typeof(idRadio) != 'undefined') {
                        if (idRadio != 'gp_no_garantia') {
                            $("#gp_single_product_button").addClass('qc-add-to-cart-button button alt');
                            $("#gp_single_product_button").attr('name', 'qc-add-to-cart');
                        } else {
                            $("#gp_single_product_button").addClass('single_add_to_cart_button button alt');
                            $("#gp_single_product_button").attr('name', 'add-to-cart');
                        }

                    } else {
                        $("#gp_single_product_button").addClass('single_add_to_cart_button button alt');
                        $("#gp_single_product_button").attr('name', 'add-to-cart');
                    }
                    $("#gp_single_product_button_txt").text('Añadir a Carrito');
                }
                $("input[name=quantity]").val(1);
                $("input[name=quantity]").attr({
                    "max": $("#domicilio_cantidad").val(),
                    "min": 1
                });
                if ($("#gp_mensaje_domicilio p").is(':empty')) {
                    $("#gp_single_product_button").prop("disabled", false);
                    $("#domicilio").prop("disabled", false);
                    $('#domicilio').show();
                } else {
                    $("#gp_single_product_button").prop("disabled", true);
                    $("#domicilio").prop("disabled", true);
                    $('#domicilio').hide();
                    $('#gp_radio_recibir_domicilio').addClass('disabled');
                    $('#domicilio').prop('checked', false);
                }
            });
            $('body').on('click', '#tienda', function() {
                let condicion = $("#condicion").val();
                let texto = '';
                if (condicion.startsWith('P')) {
                    if ($("#gp_apartalo_con").length > 0) {
                        $("#gp_apartalo_con").html('Apártalo con <span class="woocommerce-Price-currencySymbol">$</span><bdi>' + $("#gp_sucursal_apartalo").val() + '</bdi>');
                    } else {
                        $("p.price.product-page-price.price-on-sale.gp_precio_ps").after('<span id="gp_apartalo_con">Apártalo con <span class="woocommerce-Price-currencySymbol">$</span><bdi>' + $("#gp_sucursal_apartalo").val() + '</bdi></span>');
                    }
                    $("#gp_single_product_button_txt").text('Apártalo en preventa ahora');
                } else {
                    $("#gp_single_product_button_txt").text('Apartar Ahora');
                }
                gpCookie('_gp_id_tipo_envio', 'tienda', 1);
                $("#gp_single_product_button").removeClass();
                $("#gp_single_product_button").addClass('qc-add-to-cart-button button alt');
                $("#gp_single_product_button").attr('name', 'qc-add-to-cart');
                $("input[name=quantity]").val(1);
                $("input[name=quantity]").attr({
                    "max": $("#sucursal_cantidad").val(),
                    "min": 1
                });
                $("#gp_single_product_button").prop("disabled", false);
                $("#domicilio").prop("disabled", false);
            });

            $('body').on('click', '#gp_single_product_button', function() {
                var datos1 = {
                    'sku': $("#sku").val(),
                    'categoria': $("#categoria").val(),
                    'condicion': $("#condicion").val(),
                    'plataforma': $("#plataforma").val()
                };

                var idRadio = $("input[name='garantia']:checked").val();
                if (typeof(idRadio) != 'undefined') {
                    var metaRadio = $("input[name='garantia']:checked").attr('gp-gc');
                    var metaRadioName = $("input[name='garantia']:checked").attr('gp-gname');
                    if (metaRadio != 'gp_no_garantia') {
                        if ($('#domicilio').is(':checked')) {
                            console.log('id radio', idRadio);
                            console.log('meta radio', metaRadio);
                            $.extend(datos1, {
                                'id_garantia': idRadio,
                                'costo_garantia': metaRadio,
                                'nombre_garantia': metaRadioName,
                            })
                        }
                    }
                }

                if ($('#tienda').is(':checked')) {
                    $.extend(datos1, {
                        'entrega_estimada': $("#sucursal_entrega_estimada").val(),
                        'tienda': $("#sucursal_id_tienda").val(),
                        'tipo': $("#sucursal_id_tipo_envio").val(),
                        'subtipo': $("#sucursal_id_subtipo_envio").val(),
                        'shipping': $("#sucursal_shipping").val(),
                        'nombre_sucursal': $("#sucursal_nombre_tienda").val()
                    })
                } else if ($('#domicilio').is(':checked')) {
                    $.extend(datos1, {
                        'entrega_estimada': $("#domicilio_entrega_estimada").val(),
                        'tienda': $("#domicilio_id_tienda").val(),
                        'tipo': $("#domicilio_id_tipo_envio").val(),
                        'subtipo': $("#domicilio_id_subtipo_envio").val(),
                        'shipping': $("#domicilio_shipping").val(),
                        'nombre_sucursal': $("#domicilio_nombre_tienda").val()
                    })
                }
                // gpCookie('_gp_data_test', encodeURIComponent(JSON.stringify(datos1)), 365);
                gpCookie('_gp_data', encodeURIComponent(JSON.stringify(datos1)), 365);

                if (user_logged.check) {
                    // logged in
                } else {
                    if ($('#tienda').is(':checked')) {
                        event.preventDefault();
                        $("#gp_single_product_button").removeClass();
                        $("#gp_single_product_button").addClass('button alt');
                        $(".nav-top-link.nav-top-not-logged-in").click();
                    }
                    if ($('#domicilio').is(':checked') && ($("#sku").val()).startsWith('P')) {
                        event.preventDefault();
                        $("#gp_single_product_button").removeClass();
                        $("#gp_single_product_button").addClass('button alt');
                        $(".nav-top-link.nav-top-not-logged-in").click();
                    }
                }
            });
            $('body').on('click', '#disp_prod', function(e) {
                e.preventDefault();
                $("#btn_disp_prod").click();
            });
        }

    });

    //* funciones *//
    function gpFindeMe() {
        var options = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };

        const status = document.querySelector('#ubicacion_status');

        function success(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            status.textContent = '';
            var address_txt = gpCodeLatLng(latitude, longitude);

            gpCookie('_gp_geo_lat', latitude, 365);
            gpCookie('_gp_geo_lng', longitude, 365);
            $('.mfp-bg.mfp-ready').click();

        }

        function error() {
            status.textContent = 'No es posible localizar su ubicación...';
        }

        if (!navigator.geolocation) {
            status.textContent = 'Geolocalizacion no es soportada en su navegador...';
        } else {
            status.textContent = 'Cargando…';
            navigator.geolocation.getCurrentPosition(success, error, options);
        }
    }

    function gpCodeLatLng(lat, lng) {

        var address_short = "",
            address_long = "",
            locality = "",
            city = "",
            country = "",
            postal_code = "",
            locality_long = "",
            city_long = "",
            country_long = "",
            postal_code_long = "",
            street_long = "",
            number_long = "";
        const address_txt = document.querySelector('#gp_address');
        //* obtener todos los "span" donde imprimir la calle
        const x = document.getElementsByClassName("gp_direccion");
        // This is making the Geocode request
        var geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(lat, lng);

        geocoder.geocode({ 'latLng': latlng }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    //find details info
                    for (var i = 0; i < results[0].address_components.length; i++) {
                        for (var b = 0; b < results[0].address_components[i].types.length; b++) {

                            if (results[0].address_components[i].types[b] == "locality") {
                                locality = results[0].address_components[i];
                            }
                            if (results[0].address_components[i].types[b] == "administrative_area_level_1") {
                                city = results[0].address_components[i];
                            }
                            if (results[0].address_components[i].types[b] == "country") {
                                country = results[0].address_components[i];
                            }
                            if (results[0].address_components[i].types[b] == "postal_code") {
                                postal_code = results[0].address_components[i];
                            }
                        }
                    }
                    for (var i = 0; i < results[1].address_components.length; i++) {
                        for (var b = 0; b < results[1].address_components[i].types.length; b++) {

                            if (results[1].address_components[i].types[b] == "locality") {
                                locality_long = results[1].address_components[i];
                            }
                            if (results[1].address_components[i].types[b] == "administrative_area_level_1") {
                                city_long = results[1].address_components[i];
                            }
                            if (results[1].address_components[i].types[b] == "country") {
                                country_long = results[1].address_components[i];
                            }
                            if (results[1].address_components[i].types[b] == "postal_code") {
                                postal_code_long = results[1].address_components[i];
                            }
                            if (results[1].address_components[i].types[b] == "route") {
                                street_long = results[1].address_components[i];
                            }
                            if (results[1].address_components[i].types[b] == "street_number") {
                                number_long = results[1].address_components[i];
                            }
                        }
                    }

                    // direccion Completa
                    // address_long = results[0].formatted_address;
                    address_long = street_long.long_name + ' ' + number_long.long_name + ', ' + city_long.long_name + ', CP ' + postal_code.long_name;
                    // direccion Abreviada
                    // address_short = locality.long_name + ", " + city.long_name + " " + country.short_name + ", CP " + postal_code.long_name;
                    // address_short = locality.long_name + ", CP " + postal_code.long_name;
                    address_short = city.short_name + ", CP " + postal_code.long_name;

                    gpCookie('_gp_geo_address_long', encodeURIComponent(address_long), 365);
                    gpCookie('_gp_geo_address_short', encodeURIComponent(address_short), 365);
                    gpCookie('_gp_geo_pc', postal_code.long_name, 365);
                    address_txt.textContent = decodeURIComponent(address_short);
                    for (var i = 0; i < x.length; i++) {
                        x[i].innerText = "Enviar a " + decodeURIComponent(address_short); // Change the content
                    }

                    const y = document.getElementsByClassName("gp_single_product_direccion");
                    const z = document.getElementsByClassName("gp_de_ubicacion");
                    for (var i = 0; i < z.length; i++) {
                        z[i].innerText = decodeURIComponent(address_long);
                    }
                    if (y.length > 0) {
                        $("#gp_single_product_button").prop("disabled", true);
                        f_gp_disponibilidad_domicilio();
                        $('.mfp-bg.mfp-ready').click();
                        $("#domicilio").click();
                        for (var i = 0; i < y.length; i++) {
                            y[i].innerText = decodeURIComponent(address_long);
                        }
                    }

                } else {
                    alert("No results found");
                }
            } else {
                alert("Geocoder failed due to: " + status);
            }
        });
    }

    function gpCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + ";SameSite=Lax; path=/";
    }

    function gpGetCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function gpEraseCookie(name) {
        document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }

    // function initAutocomplete2() {
    //     let autocomplete;
    //     const options = {
    //         componentRestrictions: { country: "mx" },
    //         fields: ["address_components", "geometry"]
    //     };
    //     var input = document.getElementById('cambiar_direccion');
    //     autocomplete = new google.maps.places.Autocomplete(input, options);
    //     autocomplete.addListener('place_changed', function() {

    //         $("#gp_single_product_button").prop("disabled", true);
    //         var place = autocomplete.getPlace();
    //         var adrs = place.address_components;

    //         let address1 = "",
    //             address_num = "",
    //             postcode = "",
    //             locality = "",
    //             administrative_area_level_1 = "",
    //             country = "",
    //             address_long = '',
    //             address_short = '';

    //         for (const component of adrs) {
    //             const componentType = component.types[0];
    //             switch (componentType) {

    //                 case "route":
    //                     {
    //                         address1 = component.short_name + address_num;
    //                         break;
    //                     }
    //                 case "street_number":
    //                     {
    //                         address_num = `${address1} #${component.long_name}`;
    //                         break;
    //                     }

    //                 case "postal_code":
    //                     {
    //                         postcode = `${component.long_name}${postcode}`;
    //                         break;
    //                     }

    //                 case "postal_code_suffix":
    //                     {
    //                         postcode = `${postcode}-${component.long_name}`;
    //                         break;
    //                     }

    //                 case "locality":
    //                     locality = component.long_name;
    //                     break;

    //                 case "administrative_area_level_1":
    //                     {
    //                         administrative_area_level_1 = component.short_name;
    //                         break;
    //                     }

    //                 case "country":
    //                     country = component.long_name;
    //                     break;
    //             }
    //         }

    //         var lat = place.geometry.location.lat(),
    //             lng = place.geometry.location.lng();

    //         address_long = address1 + ', ' + postcode + ', ' + locality + ', ' + administrative_area_level_1 + ', ' + country;
    //         // address_short = address1 + ', ' + postcode;
    //         // address_short = locality + ', ' + administrative_area_level_1 + ' ' + postcode;
    //         if (postcode) {
    //             address_short = administrative_area_level_1 + ', CP ' + postcode;
    //         } else {
    //             address_short = administrative_area_level_1 + ', ' + country;
    //         }

    //         gpCookie('_gp_geo_address_long', encodeURIComponent(address_long), 365);
    //         gpCookie('_gp_geo_address_short', encodeURIComponent(address_short), 365);
    //         gpCookie('_gp_geo_lat', lat, 365);
    //         gpCookie('_gp_geo_lng', lng, 365);

    //         gp_disponibilidad_producto_simple();

    //         const x = document.getElementsByClassName("gp_direccion");
    //         const y = document.getElementsByClassName("gp_single_product_direccion");
    //         for (var i = 0; i < x.length; i++) {
    //             x[i].innerText = "Enviar a " + decodeURIComponent(address_short);
    //         }
    //         for (var i = 0; i < y.length; i++) {
    //             y[i].innerText = decodeURIComponent(address_long);
    //         }
    //         $('.mfp-bg.mfp-ready').click();
    //         $("#domicilio").click();
    //         $("#gp_single_product_button").prop("disabled", true);
    //         $('#cambiar_direccion').val('');

    //     })
    // }

    function initAutocomplete(elemento) {
        let autocomplete;
        const options = {
            componentRestrictions: { country: "mx" },
            fields: ["address_components", "geometry"]
        };
        var input = document.getElementById(elemento);
        autocomplete = new google.maps.places.Autocomplete(input, options);
        autocomplete.addListener('place_changed', function() {

            var place = autocomplete.getPlace();
            var adrs = place.address_components;

            let address1 = "",
                address_num = "",
                postcode = "",
                locality = "",
                administrative_area_level_1 = "",
                country = "",
                address_long = '',
                address_short = '';

            for (const component of adrs) {
                const componentType = component.types[0];
                switch (componentType) {

                    case "route":
                        {
                            address1 = component.short_name + address_num;
                            break;
                        }
                    case "street_number":
                        {
                            address_num = `${address1} #${component.long_name}`;
                            break;
                        }

                    case "postal_code":
                        {
                            postcode = `${component.long_name}${postcode}`;
                            break;
                        }

                    case "postal_code_suffix":
                        {
                            postcode = `${postcode}-${component.long_name}`;
                            break;
                        }

                    case "locality":
                        locality = component.long_name;
                        break;

                    case "administrative_area_level_1":
                        {
                            administrative_area_level_1 = component.short_name;
                            break;
                        }

                    case "country":
                        country = component.long_name;
                        break;
                }
            }
            const address_txt = document.querySelector('#gp_address');
            const status = document.querySelector('#ubicacion_status');

            var lat = place.geometry.location.lat(),
                lng = place.geometry.location.lng();

            address_long = address1 + ', ' + postcode + ', ' + locality + ', ' + administrative_area_level_1 + ', ' + country;
            // address_short = address1 + ', ' + postcode;
            // address_short = locality + ', ' + administrative_area_level_1 + ' ' + postcode;
            if (postcode) {
                address_short = administrative_area_level_1 + ', CP ' + postcode;
            } else {
                address_short = administrative_area_level_1 + ', ' + country;
            }

            gpCookie('_gp_geo_address_long', encodeURIComponent(address_long), 365);
            gpCookie('_gp_geo_address_short', encodeURIComponent(address_short), 365);
            gpCookie('_gp_geo_lat', lat, 365);
            gpCookie('_gp_geo_lng', lng, 365);
            gpCookie('_gp_geo_pc', postcode, 365);

            const x = document.getElementsByClassName("gp_direccion");
            const y = document.getElementsByClassName("gp_single_product_direccion");
            // address_txt.textContent = address_short;
            for (var i = 0; i < x.length; i++) {
                x[i].innerText = "Enviar a " + decodeURIComponent(address_short);
            }
            if (y.length > 0) {
                $("#gp_single_product_button").prop("disabled", true);
                f_gp_disponibilidad_domicilio();
                $('.mfp-bg.mfp-ready').click();
                $("#domicilio").click();
                for (var i = 0; i < y.length; i++) {
                    y[i].innerText = decodeURIComponent(address_long);
                }
            }
            const z = document.getElementsByClassName("gp_de_ubicacion");
            for (var i = 0; i < z.length; i++) {
                z[i].innerText = decodeURIComponent(address_long);
            }

            // status.textContent = '¡Ubicación actualizada!';
            $('.mfp-bg.mfp-ready').click();
            $('#autocomplete').val('');

        })
    }

    function gp_disponibilidad_producto_simple() {
        let upc = $("#sku").val(),
            lat = _gp_geo_lat,
            lng = _gp_geo_lng,
            tienda_fav = _gp_tienda_default_id,
            tienda_fav_nombre = _gp_tienda_default_nombre,
            tienda_selec = _gp_tienda_default_id,
            cantidad = 1,
            cantidad_sucursal = 1,
            cantidad_domicilio = 1,
            nombre = '',
            direccion_larga = _gp_geo_address_long,
            mensaje = '';

        if (gpGetCookie('_gp_geo_lat') && gpGetCookie('_gp_geo_lng')) {
            lat = gpGetCookie('_gp_geo_lat');
            lng = gpGetCookie('_gp_geo_lng');
        }

        if (gpGetCookie('_gp_tienda_favorita_nombre')) {
            tienda_fav_nombre = decodeURIComponent(gpGetCookie('_gp_tienda_favorita_nombre'));
        }

        if (gpGetCookie('_gp_tienda_favorita_id')) {
            tienda_fav = gpGetCookie('_gp_tienda_favorita_id');
        }

        cantidad = parseInt($("input[name=quantity]").val());
        if (cantidad < 1 || cantidad > 12) {
            cantidad = 1;
        }

        const headers = new Headers({
            'Content-Type': 'application/json',
            'X_WP_N11': ajax_disponibilidad.gp_nonce,
        });
        let data = {
            "upc": upc,
            "tienda_fav": tienda_fav,
            "tienda_selec": tienda_fav,
            "solicitud": cantidad,
            "lat": lat,
            "lng": lng,
        }
        fetch(ajax_disponibilidad.url, {
                method: 'post',
                body: JSON.stringify(data),
                headers: headers,
            })
            .then(response => {
                return response.ok ? response.json() : 'Not Found...';
            }).then(json_response => {
                $("input[name=quantity]").val(1);
                cantidad = 0;
                cantidad_sucursal = 0;
                cantidad_domicilio = 0;

                let domicilio_entrega = '',
                    domicilio_shipping = '';
                let domicilio_id_tienda, domicilio_nombre_tienda, domicilio_id_tipo_envio, domicilio_id_subtipo_envio, domicilio_entrega_estimada, domicilio_cantidad;
                let sucursal_id_tienda, sucursal_nombre_tienda, sucursal_id_tipo_envio, sucursal_id_subtipo_envio, sucursal_entrega_estimada, sucursal_cantidad, sucursal_shipping;

                $("#gp_mensaje").html();
                if (gpGetCookie('_gp_geo_address_long')) {
                    direccion_larga = decodeURIComponent(gpGetCookie('_gp_geo_address_long'));
                }

                // !---
                let datos_domicilio = json_response['domicilio'];
                let datos_tienda = json_response['tienda'];
                let opcion_envio = '';
                if (datos_domicilio['preventa'] && datos_domicilio['preventa']['disponible']) {
                    opcion_envio = 'preventa';
                } else if (datos_domicilio['standard'] && datos_domicilio['standard']['disponible']) {
                    opcion_envio = 'standard';
                } else if (datos_domicilio['sameday'] && datos_domicilio['sameday']['disponible']) {
                    opcion_envio = 'sameday';
                } else if (datos_domicilio['nextday'] && datos_domicilio['nextday']['disponible']) {
                    opcion_envio = 'nextday';
                } else if (datos_domicilio['express'] && datos_domicilio['express']['disponible']) {
                    opcion_envio = 'express';
                }
                // if (json_response['express'] && json_response['express']['disponible']) {
                //     opcion_envio = 'express';
                // } else if (json_response['nextday'] && json_response['nextday']['disponible']) {
                //     opcion_envio = 'nextday';
                // } else if (json_response['sameday'] && json_response['sameday']['disponible']) {
                //     opcion_envio = 'sameday';
                // } else if (json_response['standard'] && json_response['standard']['disponible']) {
                //     opcion_envio = 'standard';
                // }
                // //! BORRAR
                // if (upc == '820650850233') {
                //     opcion_envio = 'express';
                // }
                // //!-----
                domicilio_id_tienda = datos_domicilio[opcion_envio]['id_tienda'];
                domicilio_nombre_tienda = datos_domicilio[opcion_envio]['nombre_tienda'];
                domicilio_id_tipo_envio = datos_domicilio[opcion_envio]['id_tipo_envio'];
                domicilio_id_subtipo_envio = datos_domicilio[opcion_envio]['id_subtipo_envio'];
                domicilio_entrega_estimada = datos_domicilio[opcion_envio]['entrega_estimada'];
                domicilio_cantidad = datos_domicilio[opcion_envio]['cantidad'];

                cantidad_domicilio = datos_domicilio[opcion_envio]['cantidad'];
                domicilio_entrega = datos_domicilio[opcion_envio]['entrega_estimada'];
                domicilio_shipping = datos_domicilio[opcion_envio]['shipping'];
                // !---

                $('#domicilio_id_tienda').val(domicilio_id_tienda);
                $('#domicilio_nombre_tienda').val(domicilio_nombre_tienda);
                $('#domicilio_id_tipo_envio').val(domicilio_id_tipo_envio);
                $('#domicilio_id_subtipo_envio').val(domicilio_id_subtipo_envio);
                $('#domicilio_entrega_estimada').val(domicilio_entrega_estimada);
                $('#domicilio_cantidad').val(domicilio_cantidad);
                $('#domicilio_shipping').val(domicilio_shipping);

                let opcion_tienda = '';
                if (datos_tienda['apartado'] && datos_tienda['apartado']['disponible']) {
                    opcion_tienda = 'apartado';
                } else if (datos_tienda['preventa'] && datos_tienda['preventa']['disponible']) {
                    opcion_tienda = 'preventa';
                }
                sucursal_id_tienda = datos_tienda[opcion_tienda]['id_tienda'];
                sucursal_nombre_tienda = datos_tienda[opcion_tienda]['nombre_tienda'];
                sucursal_id_tipo_envio = datos_tienda[opcion_tienda]['id_tipo_envio'];
                sucursal_id_subtipo_envio = datos_tienda[opcion_tienda]['id_subtipo_envio'];
                sucursal_entrega_estimada = datos_tienda[opcion_tienda]['entrega_estimada'];
                if (datos_tienda[opcion_tienda]['cantidad'] > 1) {
                    sucursal_cantidad = 1;
                    cantidad_sucursal = 1;
                } else {
                    sucursal_cantidad = datos_tienda[opcion_tienda]['cantidad'];
                    cantidad_sucursal = datos_tienda[opcion_tienda]['cantidad'];
                }
                sucursal_shipping = datos_tienda[opcion_tienda]['shipping'];

                nombre = datos_tienda[opcion_tienda]['nombre_tienda'];
                if (nombre.length >= 15) {
                    nombre = nombre.replace("Gameplanet", "GP");
                }

                if (tienda_fav != datos_tienda[opcion_tienda]['id_tienda']) {
                    mensaje = '<p>La sucursal que tienes seleccionada de forma predeterminada <strong>"' + tienda_fav_nombre + '"</strong> no tiene el producto disponible, te recomendamos recogerlo en <strong>"' + nombre + '"</strong>.</p><br/>';
                }

                $('#sucursal_id_tienda').val(sucursal_id_tienda);
                $('#sucursal_nombre_tienda').val(sucursal_nombre_tienda);
                $('#sucursal_id_tipo_envio').val(sucursal_id_tipo_envio);
                $('#sucursal_id_subtipo_envio').val(sucursal_id_subtipo_envio);
                $('#sucursal_entrega_estimada').val(sucursal_entrega_estimada);
                $('#sucursal_cantidad').val(sucursal_cantidad);
                $('#sucursal_shipping').val(sucursal_shipping);

                if (!domicilio_cantidad) {
                    $("#gp_radio_recibir_domicilio").addClass('gp_dis');
                    $("#gp_radio_recibir_domicilio").prop('checked', false);
                } else {
                    $("#gp_radio_recibir_domicilio").removeClass('gp_dis');
                    $("#domicilio").prop("disabled", false);
                    $(".gp_single_product_direccion").text(direccion_larga);
                    $("#gp_domicilio_tiempo_entrega").text("Entrega " + domicilio_entrega);
                    $("#gp_domicilio_shipping").text("Costo envío: $" + domicilio_shipping);
                }

                if (!sucursal_cantidad) {
                    $("#gp_radio_recibir_sucursal").addClass('gp_dis');
                    $("#gp_radio_recibir_sucursal").prop('checked', false);
                } else {
                    $("#gp_radio_recibir_sucursal").removeClass('gp_dis');
                    $("#tienda").prop("disabled", false);
                    $("#tienda_seleccionada").text("Recoger en " + nombre);
                    $("#gp_sucursal_tiempo_entrega").text("Entrega " + datos_tienda[opcion_tienda]['entrega_estimada']);
                    $("#gp_sucursal_shipping").text("Costo envío: $" + datos_tienda[opcion_tienda]['shipping']);
                    $("#gp_mensaje").html(mensaje);
                }

                if ($('#domicilio').is(':checked')) {
                    cantidad = cantidad_domicilio;
                } else {
                    cantidad = cantidad_sucursal;
                }

                $("input[name=quantity]").attr({
                    "max": cantidad,
                    "min": 1
                });

                $('#lat').val(lat);
                $('#lng').val(lng);
                // }
                $("#gp_single_product_button").prop("disabled", false);
                $("#domicilio").prop("disabled", false);

            }).catch(err => console.log(err));
    }

    function gp_direccion_guardada() {
        console.log("<< entre 2");
        $("#gp_single_product_button").prop("disabled", true);
        var atributo = $(".gp_btn_ship").attr('gp_dir');
        var elementos = atributo.split(';');

        var addrs_long = elementos[0];
        var addrs_short = elementos[1];
        var lat = elementos[2];
        var lng = elementos[3];
        var postcode = elementos[4];
        gpCookie('_gp_geo_address_long', encodeURIComponent(addrs_long), 365);
        gpCookie('_gp_geo_address_short', encodeURIComponent(addrs_short), 365);
        gpCookie('_gp_geo_lat', lat, 365);
        gpCookie('_gp_geo_lng', lng, 365);
        gpCookie('_gp_geo_pc', postcode, 365);

        const x = document.getElementsByClassName("gp_direccion");
        // address_txt.textContent = address_short;
        for (var i = 0; i < x.length; i++) {
            x[i].innerText = "Enviar a " + decodeURIComponent(addrs_short);
        }

        const y = document.getElementsByClassName("gp_single_product_direccion");
        if (y.length > 0) {
            $('.mfp-bg.mfp-ready').click();
            $("#gp_single_product_button").prop("disabled", true);
            f_gp_disponibilidad_domicilio();
            $("#domicilio").click();
            for (var i = 0; i < y.length; i++) {
                y[i].innerText = decodeURIComponent(addrs_long) + '.';
            }
        }
        const z = document.getElementsByClassName("gp_de_ubicacion");
        for (var i = 0; i < z.length; i++) {
            z[i].innerText = decodeURIComponent(addrs_long);
        }

        // status.textContent = '¡Ubicación actualizada!';
        $('.mfp-bg.mfp-ready').click();
        $('#autocomplete').val('');
    }

    function f_gp_disponibilidad_domicilio() {
        let upc = $("#sku").val(),
            lat = _gp_geo_lat,
            lng = _gp_geo_lng,
            tienda_fav = _gp_tienda_default_id,
            tienda_fav_nombre = _gp_tienda_default_nombre,
            tienda_selec = _gp_tienda_default_id,
            cantidad = 1,
            cantidad_sucursal = 1,
            cantidad_domicilio = 1,
            nombre = '',
            direccion_larga = _gp_geo_address_long,
            mensaje = '';

        if (gpGetCookie('_gp_geo_lat') && gpGetCookie('_gp_geo_lng')) {
            lat = gpGetCookie('_gp_geo_lat');
            lng = gpGetCookie('_gp_geo_lng');
        }

        if (gpGetCookie('_gp_geo_address_long')) {
            direccion_larga = decodeURIComponent(gpGetCookie('_gp_geo_address_long'));
        }

        if (gpGetCookie('_gp_tienda_favorita_nombre')) {
            tienda_fav_nombre = decodeURIComponent(gpGetCookie('_gp_tienda_favorita_nombre'));
        }

        if (gpGetCookie('_gp_tienda_favorita_id')) {
            tienda_fav = gpGetCookie('_gp_tienda_favorita_id');
        }

        cantidad = parseInt($("input[name=quantity]").val());
        if (cantidad < 1 || cantidad > 12) {
            cantidad = 1;
        }

        const request = new XMLHttpRequest();
        request.open('POST', gp_disp_dom.url, true);

        //callback - resultado de la peticion
        request.onload = function() {
            $("#gp_mensaje_domicilio").html('<p></p>');
            const response = JSON.parse(this.response);
            // const response = this.response;
            console.log("respuesta del back", response);
            console.log("type", typeof(response));

            if (response.estatus) {
                response.tipos_envio.forEach((element, index, data) => {
                    if (element.id == 'domicilio') {
                        if (element.estatus) {
                            element.subtipo.almacenes.forEach((element2, index2, data2) => {
                                if (index2 == 0) {
                                    let entrega_temp = element.subtipo.entrega_estimada;
                                    let val_entrega = entrega_temp.replace('Entrega ', "");

                                    $("#domicilio_id_tienda").val(element2.id_sucursal);
                                    $("#domicilio_nombre_tienda").val(element2.nombre);
                                    $("#domicilio_cantidad").val(element2.cantidad);
                                    $("#domicilio_id_tipo_envio").val('domicilio');
                                    $("#domicilio_id_subtipo_envio").val(element.subtipo.nombre);
                                    $("#domicilio_entrega_estimada").val(val_entrega);
                                    $("#domicilio_shipping").val(element.subtipo.shipping.valor);

                                    $("#gp_domicilio_tiempo_entrega").text(element.subtipo.entrega_estimada);
                                    $("#gp_domicilio_shipping").text(element.subtipo.shipping.mensaje);

                                    $("#lat").val(lat);
                                    $("#lng").val(lng);

                                    const y = document.getElementsByClassName("gp_single_product_direccion");
                                    const z = document.getElementsByClassName("gp_de_ubicacion");
                                    for (var i = 0; i < y.length; i++) {
                                        y[i].innerText = decodeURIComponent(direccion_larga);
                                    }
                                    for (var i = 0; i < z.length; i++) {
                                        z[i].innerText = decodeURIComponent(direccion_larga);
                                    }

                                    $("#gp_single_product_button").prop("disabled", false);
                                    $("#domicilio").prop("disabled", false);
                                    $('#domicilio').show();
                                    $('#gp_radio_recibir_domicilio').removeClass('disabled');
                                }
                            });
                        } else {
                            console.log("sin envio a domicilio!", element);
                            // $("#gp_mensaje_domicilio").html('<p>Por el momento no hay disponibilidad en esta zona, te sugerimos cambiar la dirección de envío. Code: WJS-002</p>');
                            if (element.estatus_mensaje_print) {
                                $("#gp_mensaje_domicilio").html(element.estatus_mensaje);
                            }
                            $('#domicilio').prop('checked', false);
                            $('#domicilio').hide();
                            $('#gp_radio_recibir_domicilio').addClass('disabled');
                            $("#gp_single_product_button").prop("disabled", true);
                        }
                    }
                });
            } else {
                console.log("estatus 0");
                $("#gp_mensaje_domicilio").html('<p>Por el momento no hay disponibilidad en esta zona, te sugerimos cambiar la dirección de envío. Code: WJS-001</p>');
                $('#domicilio').prop('checked', false);
                $('#domicilio').hide();
                $('#gp_radio_recibir_domicilio').addClass('disabled');
                $("#gp_single_product_button").prop("disabled", true);
            }
        }

        // error en respuesta
        request.onerror = function() {
            console.log("Algo salio mal en la peticion", this.error);
        }

        //se envia la peticion
        const data = new FormData();
        data.append('action', gp_disp_dom.action);
        data.append('upc', upc);
        data.append('lat', lat);
        data.append('lng', lng);
        data.append('tienda_fav', tienda_fav);
        data.append('id_cliente', gp_disp_dom.id_gp);
        request.send(data);
    }

    //* ---- *//

    //* autocomplete *//
    $("#autocomplete").ready(function() {
        initAutocomplete('autocomplete');
        $("#autocomplete").click(function() {
            $('#ubicacion_status').html('');
        });
    });
    // $("#cambiar_direccion").ready(function() {
    //     if ($("#cambiar_direccion").length) {
    //         initAutocomplete2();
    //         $("#cambiar_direccion").click(function() {
    //             $('#ubicacion_status').html('');
    //         });
    //     }
    // });

    $("#mi_ubicacion").ready(function() {
        $("#mi_ubicacion").on('click', function() {
            gpFindeMe();
            return false;
        });
    });

    //* ---- *//

    //* entrega *//
    $('#domicilio').ready(function() {
        $("#domicilio").prop("disabled", false);
        $('#domicilio').click(function() {
            gpCookie('_gp_id_tipo_envio', 'domicilio', 1);
            $("#gp_single_product_button").removeClass();
            let condicion = $("#condicion").val();
            if (condicion.startsWith('P')) {
                if ($("#gp_apartalo_con").length >= 1) {
                    $("#gp_apartalo_con").remove();
                }
                $("#gp_single_product_button").addClass('qc-add-to-cart-button button alt');
                $("#gp_single_product_button").attr('name', 'qc-add-to-cart');
                $("#gp_single_product_button_txt").text('Apártalo en preventa ahora');
            } else {
                var idRadio = $("input[name='garantia']:checked").val();
                if (typeof(idRadio) != 'undefined') {
                    if (idRadio != 'gp_no_garantia') {
                        $("#gp_single_product_button").addClass('qc-add-to-cart-button button alt');
                        $("#gp_single_product_button").attr('name', 'qc-add-to-cart');
                    } else {
                        $("#gp_single_product_button").addClass('single_add_to_cart_button button alt');
                        $("#gp_single_product_button").attr('name', 'add-to-cart');
                    }

                } else {
                    $("#gp_single_product_button").addClass('single_add_to_cart_button button alt');
                    $("#gp_single_product_button").attr('name', 'add-to-cart');
                }
                $("#gp_single_product_button_txt").text('Añadir a Carrito');
            }
            $("input[name=quantity]").val(1);
            $("input[name=quantity]").attr({
                "max": $("#domicilio_cantidad").val(),
                "min": 1
            });
            if ($("#gp_mensaje_domicilio p").is(':empty')) {
                $("#gp_single_product_button").prop("disabled", false);
                $("#domicilio").prop("disabled", false);
            } else {
                $("#gp_single_product_button").prop("disabled", true);
                $("#domicilio").prop("disabled", true);
            }
        });
    });
    $("input[name='garantia']").ready(function() {
        if ($("input[name='garantia']").length >= 1) {
            if ($("input[name='entrega']").length >= 1) {
                $("input[name='garantia']").click(function() {
                    let valor_input_garantia = $("input[name='garantia']:checked").val();
                    let valor_input_entrega = $("input[name='entrega']:checked").val();
                    if (typeof(valor_input_entrega) != 'undefined') {
                        if (valor_input_entrega == 'domicilio') {
                            if (valor_input_garantia != 'gp_no_garantia') {
                                $("#gp_single_product_button").addClass('qc-add-to-cart-button button alt');
                                $("#gp_single_product_button").attr('name', 'qc-add-to-cart');
                            } else {
                                $("#gp_single_product_button").addClass('single_add_to_cart_button button alt');
                                $("#gp_single_product_button").attr('name', 'add-to-cart');
                            }
                        }
                    }
                });
            }
        }
    });



    $('#tienda').ready(function() {
        $("#tienda").prop("disabled", false);
        let condicion = $("#condicion").val();
        let texto = '';
        $('#tienda').click(function() {
            if (condicion.startsWith('P')) {
                if ($("#gp_apartalo_con").length == 0) {
                    // texto = '<span id="gp_apartalo_con">Apártalo con <span class="woocommerce-Price-currencySymbol">$</span><bdi>' + $("#gp_sucursal_apartalo").text() + '</bdi></span>';
                    // $("p.price.product-page-price").after(texto);
                    texto = '<span id="gp_apartalo_con">Apártalo con <span class="woocommerce-Price-currencySymbol">$</span><bdi>' + $("#gp_sucursal_apartalo").val() + '</bdi></span>';
                    $("p.price.product-page-price.price-on-sale.gp_precio_ps").after(texto);
                }
                $("#gp_single_product_button_txt").text('Apártalo en preventa ahora');
            } else {
                $("#gp_single_product_button_txt").text('Apartar Ahora');
            }
            gpCookie('_gp_id_tipo_envio', 'tienda', 1);
            $("#gp_single_product_button").removeClass();
            $("#gp_single_product_button").addClass('qc-add-to-cart-button button alt');
            $("#gp_single_product_button").attr('name', 'qc-add-to-cart');
            $("input[name=quantity]").val(1);
            $("input[name=quantity]").attr({
                "max": $("#sucursal_cantidad").val(),
                "min": 1
            });
            $("#gp_single_product_button").prop("disabled", false);
        });
    });

    $("input[name=quantity]").ready(function() {
        $("input[name=quantity]").attr({
            "max": 1,
            "min": 1,
        });
    });
    //* ---- *//

    //* disponibilidad *//
    // $(".gp_disponibilidad").ready(function() {
    //     $(".gp_disponibilidad").click(function() {
    //         $('#status_disponibilidad_tienda').html('Cargando...');

    //         let html = '',
    //             nombre = '',
    //             telefono = '';
    //         let latitud = '',
    //             longitud = '',
    //             tienda = '',
    //             color = '',
    //             cantidad = 1;

    //         if (gpGetCookie('_gp_geo_lat') && gpGetCookie('_gp_geo_lng') && gpGetCookie('_gp_tienda_favorita_id')) {
    //             latitud = gpGetCookie('_gp_geo_lat');
    //             longitud = gpGetCookie('_gp_geo_lng');
    //             tienda = gpGetCookie('_gp_tienda_favorita_id');
    //         } else {
    //             latitud = _gp_geo_lat;
    //             longitud = _gp_geo_lng;
    //             tienda = _gp_tienda_default_id
    //         }

    //         let upc = $("#sku").val();

    //         if ($("input[name=quantity]").val()) {
    //             cantidad = parseInt($("input[name=quantity]").val());
    //             if (cantidad < 1) {
    //                 cantidad = 1;
    //             }
    //             if (cantidad > 10) {
    //                 cantidad = 10;
    //             }
    //         }

    //         const headers = new Headers({
    //             'Content-Type': 'application/json',
    //             'X_WP_N11': ajax_var.gp_nonce,
    //         });

    //         let data = {
    //             "productos": [{
    //                 "upc": upc,
    //                 "surtidor": "GAM",
    //                 "origen": "planet.shop",
    //                 "tipo": "fisico",
    //                 "lat": latitud,
    //                 "lng": longitud,
    //                 "id_tienda_favorita": 12,
    //                 "id_tienda_seleccionada": 142,
    //                 "domicilio": {
    //                     "express": {
    //                         "solicitud": cantidad,
    //                         "metodo": "cache",
    //                         "cantidad_min": 1
    //                     },
    //                     "sameday": {
    //                         "solicitud": cantidad,
    //                         "metodo": "cache",
    //                         "cantidad_min": 1
    //                     },
    //                     "standard": {
    //                         "solicitud": cantidad,
    //                         "metodo": "cache",
    //                         "cantidad_min": 1
    //                     },
    //                     "nextday": {
    //                         "solicitud": cantidad,
    //                         "metodo": "cache",
    //                         "cantidad_min": 1
    //                     }
    //                 },
    //                 "tienda": {
    //                     "apartado": {
    //                         "solicitud": cantidad,
    //                         "metodo": "cache",
    //                         "cantidad_min": 1
    //                     }
    //                 }
    //             }]
    //         }
    //         fetch(ajax_var.url, {
    //                 method: 'post',
    //                 body: JSON.stringify(data),
    //                 headers: headers,
    //             })
    //             .then(response => {
    //                 return response.ok ? response.json() : 'Algo salió mal, inténtelo más tarde... Code: PSJ-001';
    //             }).then(json_response => {

    //                 if (typeof json_response === 'object') {

    //                     html = '';
    //                     html += '<ul>';
    //                     json_response.forEach((element, index, data) => {
    //                         if (element.mensaje_tienda) {
    //                             html += '<li>' + element.mensaje_tienda + '</li>';

    //                         } else if (element.tipo == 'tienda') {
    //                             nombre = element.nombre;
    //                             if (nombre.length >= 15) {
    //                                 nombre = nombre.replace("Gameplanet", "GP");
    //                             }

    //                             telefono = element.telefono;
    //                             telefono = telefono.replace(/-/g, "");
    //                             telefono = telefono.replace(/\(/g, "");
    //                             telefono = telefono.replace(/\)/g, "");
    //                             telefono = telefono.replace(/ /g, "");
    //                             telefono = telefono.substring(0, 2) + " " + telefono.substring(2, 6) + " " + telefono.substring(6, 10);
    //                             if (element.cantidad > 0) {
    //                                 color = "gp_c_verde";
    //                             } else {
    //                                 color = "gp_c_gris";
    //                             }

    //                             html += `
    //                             <li class="li_disponibilidad">
    //                                 <div class="row row-collapse align-left">
    //                                     <div class="col medium-9 small-12 large-9">
    //                                         <div class="col-inner" style="padding-right: 1em;">
    //                                             <h3 style="margin-bottom: 0px;">${nombre}</h3>
    //                                             <p>${element.direccion}</p>
    //                                         </div>
    //                                     </div>
    //                                     <div class="col medium-3 small-3 large-3">
    //                                         <div class="col-inner">
    //                                             <div style="margin-right: 1em; text-align: center;">
    //                                                 <h3 class="gp_h3_disponibilidad ${color}">${element.cantidad}</h3>
    //                                                 <p class="gp_fs_p8em">pieza(s)<br/>disponibles</p>
    //                                             </div>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                                 <div class="row row-collapse align-left">
    //                                     <p>
    //                                         <a href="tel:+${telefono}">${telefono}</a>
    //                                     </p>
    //                                 </div>
    //                             </li>

    //                             <hr class="hr_gp">
    //                             `;
    //                         }
    //                     });
    //                     html += '</ul>';
    //                     $('#status_disponibilidad_tienda').html(html);

    //                 } else {
    //                     html = json_response;
    //                     $('#status_disponibilidad').html(html);
    //                     $('#status_disponibilidad_tienda').html(html);
    //                 }
    //             }).catch(err => console.log(err));

    //     });
    // });

    //* ---- *//

    //* tienda *//
    $('.gp_tienda_fav').ready(function() {
        $('.gp_tienda_fav').click(function() {
            $('#tiendas_disponibles').html('Cargando...');
            let html = '',
                nombre = '',
                telefono = '',
                value = '';
            var distancia = 0;

            let lat = _gp_geo_lat,
                lng = _gp_geo_lng;
            if (gpGetCookie('_gp_geo_lat') && gpGetCookie('_gp_geo_lng')) {
                lat = gpGetCookie('_gp_geo_lat');
                lng = gpGetCookie('_gp_geo_lng');
            }


            const request = new XMLHttpRequest();
            request.open('POST', var_ajax_sucursal.url, true);

            //callback - resultado de la peticion
            request.onload = function() {
                const response = JSON.parse(this.response);

                console.log("respuesta del back", response);

                //Si todo esta bien mostramos el resultado y se oculta el formulario
                if (typeof response === 'object') {
                    html = '';

                    html += '<ul>';
                    response.forEach((element, index, data) => {
                        distancia = parseFloat(element.distancia);
                        distancia = distancia.toFixed(1);

                        telefono = element.telefono;
                        telefono = telefono.replace(/-/g, "");
                        telefono = telefono.replace(/\(/g, "");
                        telefono = telefono.replace(/\)/g, "");
                        telefono = telefono.replace(/ /g, "");
                        telefono = telefono.substring(0, 2) + " " + telefono.substring(2, 6) + " " + telefono.substring(6, 10);

                        nombre = element.tienda;
                        if (nombre.length >= 15) {
                            nombre = nombre.replace("Gameplanet", "GP");
                        }

                        value = element.id_tienda + "," + nombre;

                        html += `
                        <li class="li_tiendas">
                            <span class="gp_btn_tienda_fav" value="${value}">
                                <div class="row row-collapse align-left">
                                    <div class="col medium-9 small-12 large-9">
                                        <div class="col-inner" style="padding-right: 1em;">
                                            <h3 style="margin-bottom: 0px;">${nombre}</h3>
                                            <p>${element.direccion}</p>
                                        </div>
                                    </div>
                                    <div class="col medium-3 small-12 large-3">
                                        <div class="col-inner">
                                            <div style="margin-right: 1em;">
                                                <h3 style="margin-bottom: 0px;">${distancia} <span class="gp_color_gris">Km</span></h3>
                                                <p class="gp_fs_p8em">de distancia</p>
                                            </div>
                                        </div>
                                    </div>
                                    <p>
                                        <a href="tel:+${telefono}">${telefono}</a>
                                    </p>
                                </div>
                            </span>
                        </li>


                        <hr class="hr_gp">
                        `;
                    });
                    html += '</ul>';

                } else {
                    html = response;
                }
                $('#tiendas_disponibles').html(html);

            }

            // error en respuesta
            request.onerror = function() {
                console.log("Algo salio mal en la peticion", this.error);
            }

            //se envia la peticion
            const data = new FormData();
            data.append('action', var_ajax_sucursal.action);
            data.append('lat', lat);
            data.append('lng', lng);
            request.send(data);

        });
    });

    $('#tiendas_disponibles').ready(function() {
        $("#tiendas_disponibles").on("click", ".gp_btn_tienda_fav", function() {
            var result = $(this).attr('value').split(',');
            let shop_id = result[0];
            let shop_name = result[1];

            gpCookie('_gp_tienda_favorita_id', shop_id, 365);
            gpCookie('_gp_tienda_favorita_nombre', shop_name, 365);
            $('.mfp-bg.mfp-ready').click();
            const address_txt = document.querySelector('#gp_tienda');
            const x = document.getElementsByClassName("gp_tienda_fav");
            for (var i = 0; i < x.length; i++) {
                x[i].innerText = "Recoger en " + decodeURIComponent(shop_name);
            }



            const tiendas_disponibles = $("#recoger_tienda > ul li span");
            let tienda = false;
            let valores = [];
            let mensaje = false;
            tiendas_disponibles.each(function() {
                tienda = $(this).hasClass("gp_btn_tienda_selec")
                if (tienda) {
                    valores = $(this).attr('value').split(',')
                    if (valores[0] == shop_id) {
                        mensaje = true;
                        $("#tienda_seleccionada").html("Recoger en " + decodeURIComponent(shop_name));
                        $("#gp_mensaje").html("");
                    }
                }
            });
            if (!mensaje) {
                let txt = $("#tienda_seleccionada").text();
                $("#gp_mensaje").html("<p>La sucursal '" + decodeURIComponent(shop_name) + "' no tiene el producto disponible, te recomendamos '" + txt + "'.</p>");
            }
        });
    });
    //* ---- *//

    // $('input:radio[name=entrega]').ready(function() {
    $('#tienda_2').ready(function() {
        $('#tienda_2').click(function() {
            $("#a_test_2").click();
        });
    });

    $("#gp_recibir_domicilio").ready(function() {
        $("#gp_recibir_domicilio").click(function() {});
    });
    $("#gp_recibir_domicilio").ready(function() {
        $("#gp_recibir_tienda").click(function() {});
    });

    // $(".mi_ubicacion_d").ready(function() {
    //     $(".mi_ubicacion_d").on('click', function() {
    //         $("#gp_single_product_button").prop("disabled", true);
    //         gpFindeMe();
    //         $("#domicilio").click();
    //         gp_disponibilidad_producto_simple();
    //         return false;
    //     });
    // });

    $('#modal_recoger_tienda').ready(function() {
        $("#modal_recoger_tienda").on("click", ".gp_btn_tienda_selec", function() {
            $("#gp_single_product_button").prop("disabled", true);
            var result = $(this).attr('value').split(',');
            const tienda = document.getElementsByClassName("gp_tienda_fav");

            let id_tienda = result[0];
            let nombre_tienda = result[1];
            let cantidad = result[2];

            gpCookie('_gp_tienda_favorita_id', id_tienda, 365);
            gpCookie('_gp_tienda_favorita_nombre', nombre_tienda, 365);
            $("#sucursal_nombre_tienda").val(nombre_tienda);
            $("#sucursal_id_tienda").val(id_tienda);
            $("#sucursal_cantidad").val(cantidad);
            $("#tienda_seleccionada").html("Recoger en " + nombre_tienda);
            $("#tienda").click();
            // gp_disponibilidad_producto_simple();

            for (var i = 0; i < tienda.length; i++) {
                tienda[i].innerText = "Recoger en " + result[1];
            }

            $("#gp_single_product_button").prop("disabled", false);
            $('.mfp-bg.mfp-ready').click();
            $("#gp_mensaje").html('<p></p>');
        });
    });
    $('body').ready(function() {
        $("#modal_disp_tiendas").on("click", ".gp_btn_tienda_selec", function() {
            $("#gp_single_product_button").prop("disabled", true);
            var result = $(this).attr('value').split(',');
            const tienda = document.getElementsByClassName("gp_tienda_fav");

            let id_tienda = result[0];
            let nombre_tienda = result[1];
            let cantidad = result[2];

            gpCookie('_gp_tienda_favorita_id', id_tienda, 365);
            gpCookie('_gp_tienda_favorita_nombre', nombre_tienda, 365);
            $("#sucursal_nombre_tienda").val(nombre_tienda);
            $("#sucursal_id_tienda").val(id_tienda);
            $("#sucursal_cantidad").val(cantidad);
            $("#tienda_seleccionada").html("Recoger en " + nombre_tienda);
            $("#tienda").click();
            // gp_disponibilidad_producto_simple();

            for (var i = 0; i < tienda.length; i++) {
                tienda[i].innerText = "Recoger en " + result[1];
            }

            $("#gp_single_product_button").prop("disabled", false);
            $('.mfp-bg.mfp-ready').click();
            $("#gp_mensaje").html('<p></p>');
        });

        $("#gp_single_product_button").on('click', function(event) {
            var datos1 = {
                'sku': $("#sku").val(),
                'categoria': $("#categoria").val(),
                'condicion': $("#condicion").val(),
                'plataforma': $("#plataforma").val()
            };

            var idRadio = $("input[name='garantia']:checked").val();
            if (typeof(idRadio) != 'undefined') {
                var metaRadio = $("input[name='garantia']:checked").attr('gp-gc');
                var metaRadioName = $("input[name='garantia']:checked").attr('gp-gname');
                if (metaRadio != 'gp_no_garantia') {
                    if ($('#domicilio').is(':checked')) {
                        console.log('id radio', idRadio);
                        console.log('meta radio', metaRadio);
                        $.extend(datos1, {
                            'id_garantia': idRadio,
                            'costo_garantia': metaRadio,
                            'nombre_garantia': metaRadioName,
                        })
                    }
                }
            }

            if ($('#tienda').is(':checked')) {
                $.extend(datos1, {
                    'entrega_estimada': $("#sucursal_entrega_estimada").val(),
                    'tienda': $("#sucursal_id_tienda").val(),
                    'tipo': $("#sucursal_id_tipo_envio").val(),
                    'subtipo': $("#sucursal_id_subtipo_envio").val(),
                    'shipping': $("#sucursal_shipping").val(),
                    'nombre_sucursal': $("#sucursal_nombre_tienda").val()
                })
            } else if ($('#domicilio').is(':checked')) {
                $.extend(datos1, {
                    'entrega_estimada': $("#domicilio_entrega_estimada").val(),
                    'tienda': $("#domicilio_id_tienda").val(),
                    'tipo': $("#domicilio_id_tipo_envio").val(),
                    'subtipo': $("#domicilio_id_subtipo_envio").val(),
                    'shipping': $("#domicilio_shipping").val(),
                    'nombre_sucursal': $("#domicilio_nombre_tienda").val()
                })
            }
            // gpCookie('_gp_data_test', encodeURIComponent(JSON.stringify(datos1)), 365);
            gpCookie('_gp_data', encodeURIComponent(JSON.stringify(datos1)), 365);

            if (user_logged.check) {
                // logged in
            } else {
                if ($('#tienda').is(':checked')) {
                    event.preventDefault();
                    $("#gp_single_product_button").removeClass();
                    $("#gp_single_product_button").addClass('button alt');
                    $(".nav-top-link.nav-top-not-logged-in").click();
                }
                if ($('#domicilio').is(':checked') && ($("#sku").val()).startsWith('P')) {
                    event.preventDefault();
                    $("#gp_single_product_button").removeClass();
                    $("#gp_single_product_button").addClass('button alt');
                    $(".nav-top-link.nav-top-not-logged-in").click();
                }
            }
        });
    });

    $('#gp_recibir_tienda').ready(function() {
        if ($('#gp_recibir_tienda').length > 0) {

            $('#recoger_tienda').html('Cargando...');

            if (typeof(gp_disponibilidad_var) === 'undefined') {
                console.log("no")
                $('#tiendas_disponibles').html('Error. Code: GPJ-100');
                return;
            }

            let temp = JSON.stringify(gp_disponibilidad_var);
            const response = JSON.parse(temp);
            let tipos_env = response['tipos_envio'];

            let html = '';
            tipos_env.forEach((element) => {
                if (element.id == 'tienda') {
                    html += '<ul>';
                    let nombre = '',
                        telefono = '',
                        color = '',
                        gp_btn_tienda_selec = '',
                        value = '',
                        li_tiendas = '';
                    element.subtipo.almacenes.forEach((element) => {
                        gp_btn_tienda_selec = '';
                        value = '';
                        li_tiendas = '';
                        nombre = element.nombre;

                        telefono = element.telefono;
                        telefono = telefono.replace(/-/g, "");
                        telefono = telefono.replace(/\(/g, "");
                        telefono = telefono.replace(/\)/g, "");
                        telefono = telefono.replace(/ /g, "");
                        telefono = telefono.substring(0, 2) + " " + telefono.substring(2, 6) + " " + telefono.substring(6, 10);

                        if (element.cantidad > 0) {
                            color = "gp_c_verde";
                            gp_btn_tienda_selec = 'gp_btn_tienda_selec'
                            value = element.id_sucursal + "," + nombre + ',' + element.cantidad;
                            li_tiendas = 'li_tiendas';
                        } else {
                            color = "gp_c_gris";
                            li_tiendas = 'li_sin_inventario';
                        }

                        html += `
                            <li class="${li_tiendas}">
                                <span class="${gp_btn_tienda_selec}" value="${value}">
                                    <div class="row row-collapse align-left">
                                        <div class="col medium-9 small-12 large-9">
                                            <div class="col-inner" style="padding-right: 1em;">
                                                <h3 style="margin-bottom: 0px;">${nombre}</h3>
                                                <p>${element.direccion}</p>
                                            </div>
                                        </div>
                                        <div class="col medium-3 small-3 large-3">
                                            <div class="col-inner">
                                                <div style="margin-right: 1em; text-align: center;">
                                                <h3 class="gp_h3_disponibilidad ${color}">${element.cantidad}</h3>
                                                <p class="gp_fs_p8em">pieza(s)<br/>disponibles</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row row-collapse align-left">
                                        <p>
                                            <a href="tel:+${telefono}">${telefono}</a>
                                        </p>
                                    </div>
                                </span>
                            </li>
    
                            <hr class="hr_gp">
                        `;

                    });
                    html += '</ul>';
                    $('#recoger_tienda').html(html);
                }
            });
        }
        $('#gp_recibir_tienda').click(function() {});
    });

    $(".button.checkout.wc-forward").ready(function() {
        $(".button.checkout.wc-forward").on('click', function(event) {
            if (!user_logged.check) {
                $(".nav-top-link.nav-top-not-logged-in").click();
                return false;
            }
        });
    });
    $("#gp_single_product_button").ready(function() {
        $("#gp_single_product_button").on('click', function(event) {
            // let datos = '',
            //     datos_globales = '';
            // datos_globales = 'sku' + '|' + $("#sku").val() + ',' + 'categoria' + '|' + $("#categoria").val() + ',' + 'condicion' + '|' + $("#condicion").val() + ',' + 'plataforma' + '|' + $("#plataforma").val();

            // if ($('#tienda').is(':checked')) {
            //     datos = 'entrega_estimada' + '|' + $("#sucursal_entrega_estimada").val() + ',' + 'tienda' + '|' + $("#sucursal_id_tienda").val() + ',' + 'tipo' + '|' + $("#sucursal_id_tipo_envio").val() + ',' + 'subtipo' + '|' + $("#sucursal_id_subtipo_envio").val() + ',' + 'shipping' + '|' + $("#sucursal_shipping").val() + ',' + 'nombre_sucursal' + '|' + $("#sucursal_nombre_tienda").val();
            // } else if ($('#domicilio').is(':checked')) {
            //     datos = 'entrega_estimada' + '|' + $("#domicilio_entrega_estimada").val() + ',' + 'tienda' + '|' + $("#domicilio_id_tienda").val() + ',' + 'tipo' + '|' + $("#domicilio_id_tipo_envio").val() + ',' + 'subtipo' + '|' + $("#domicilio_id_subtipo_envio").val() + ',' + 'shipping' + '|' + $("#domicilio_shipping").val();
            // }
            // let datos_completos = datos_globales + ',' + datos;

            // gpCookie('_gp_data', datos_completos, 365);

            var datos1 = {
                'sku': $("#sku").val(),
                'categoria': $("#categoria").val(),
                'condicion': $("#condicion").val(),
                'plataforma': $("#plataforma").val()
            };

            var idRadio = $("input[name='garantia']:checked").val();
            if (typeof(idRadio) != 'undefined') {
                var metaRadio = $("input[name='garantia']:checked").attr('gp-gc');
                var metaRadioName = $("input[name='garantia']:checked").attr('gp-gname');
                if (metaRadio != 'gp_no_garantia') {
                    if ($('#domicilio').is(':checked')) {
                        console.log('id radio', idRadio);
                        console.log('meta radio', metaRadio);
                        $.extend(datos1, {
                            'id_garantia': idRadio,
                            'costo_garantia': metaRadio,
                            'nombre_garantia': metaRadioName,
                        })
                    }
                }
            }


            if ($('#tienda').is(':checked')) {
                $.extend(datos1, {
                    'entrega_estimada': $("#sucursal_entrega_estimada").val(),
                    'tienda': $("#sucursal_id_tienda").val(),
                    'tipo': $("#sucursal_id_tipo_envio").val(),
                    'subtipo': $("#sucursal_id_subtipo_envio").val(),
                    'shipping': $("#sucursal_shipping").val(),
                    'nombre_sucursal': $("#sucursal_nombre_tienda").val()
                })
            } else if ($('#domicilio').is(':checked')) {
                $.extend(datos1, {
                    'entrega_estimada': $("#domicilio_entrega_estimada").val(),
                    'tienda': $("#domicilio_id_tienda").val(),
                    'tipo': $("#domicilio_id_tipo_envio").val(),
                    'subtipo': $("#domicilio_id_subtipo_envio").val(),
                    'shipping': $("#domicilio_shipping").val(),
                    'nombre_sucursal': $("#domicilio_nombre_tienda").val()
                })
            }
            // gpCookie('_gp_data_test', encodeURIComponent(JSON.stringify(datos1)), 365);
            gpCookie('_gp_data', encodeURIComponent(JSON.stringify(datos1)), 365);

            if (user_logged.check) {
                // logged in
            } else {
                if ($('#tienda').is(':checked')) {
                    event.preventDefault();
                    $("#gp_single_product_button").removeClass();
                    $("#gp_single_product_button").addClass('button alt');
                    $(".nav-top-link.nav-top-not-logged-in").click();
                }
                if ($('#domicilio').is(':checked') && ($("#sku").val()).startsWith('P')) {
                    event.preventDefault();
                    $("#gp_single_product_button").removeClass();
                    $("#gp_single_product_button").addClass('button alt');
                    $(".nav-top-link.nav-top-not-logged-in").click();
                }
            }
        });
    });
    $(".woocommerce").ready(function() {
        $(".woocommerce").on('click', '.checkout-button.button.alt.wc-forward', function() {
            if (!user_logged.check) {
                $(".nav-top-link.nav-top-not-logged-in").click();
                return false;
            }
        });
    });

    $("#cart-popup").ready(function() {
        $("#cart-popup").on('click', '.button.checkout.wc-forward', function(event) {
            if (!user_logged.check) {
                $(".nav-top-link.nav-top-not-logged-in").click();
                return false;
            }
        });
    });
    $(".nav-dropdown.nav-dropdown-default.dropdown-uppercase").ready(function() {
        $(".nav-dropdown.nav-dropdown-default.dropdown-uppercase").on('click', '.button.checkout.wc-forward', function(event) {
            if (!user_logged.check) {
                $(".nav-top-link.nav-top-not-logged-in").click();
                return false;
            }
        });
    });

    $(".checkout-page-title.page-title").ready(function() {
        $(".checkout-page-title.page-title").on('click', 'a', function() {
            if (!user_logged.check) {
                $(".nav-top-link.nav-top-not-logged-in").click();
                return false;
            }
        });
    });

    $("#modal_de").ready(function() {
        $("#modal_de").on('click', '.gp_btn_ship', function() {
            console.log("-- entre 3");
            $("#gp_single_product_button").prop("disabled", true);
            gp_direccion_guardada();
        });
    });

    // $("#modal_cambiar_direccion").ready(function() {
    //     $("#modal_cambiar_direccion").on('click', '.gp_btn_ship', function() {
    //         $("#gp_single_product_button").prop("disabled", true);
    //         gp_direccion_guardada();
    //         gp_disponibilidad_producto_simple();
    //         $('.mfp-bg.mfp-ready').click();
    //         $("#domicilio").click();
    //     });
    // });

    $('#inputContainer').scroll(function() {
        //Set new top to autocomplete dropdown
        newTop = $('#autocompleteInput').offset().top + $('#autocompleteInput').outerHeight();
        $('.pac-container').css('top', newTop + 'px');
    });

    $(".gp_telefono").ready(function() {
        $(".gp_telefono").click(function() {
            if (!user_logged.check) {
                $(".nav-top-link.nav-top-not-logged-in").click();
                return false;
            }
        });
    });

    function gp_select_input(elemento) {
        $(elemento).focus(function() {
            $(elemento).select();
        });
    }

    $("#_gp_autocompletado").ready(function() {
        if ($("#_gp_autocompletado").length) {
            gp_select_input("#_gp_autocompletado");
            $("#_gp_autocompletado").after("<span>Utiliza este campo y/o el siguiente mapa para proporcionarnos tu ubicación con mayor precisión.</span><br/><span id='gp_shipping_error'>&nbsp;</span><div id='map' class='gp_map'>test ubicación mapa</div>");
            initAutocompleteMap('_gp_autocompletado');
            // $("form").submit(function(e) {
            //     var temp = $("#gp_shipping_address").val();
            //     const datos = temp.split("|");
            //     $("#_gp_autocompletado").removeClass("gp_shipping_error_input");
            //     $("#gp_shipping_error").text("");
            //     if (datos[0] == '') {
            //         e.preventDefault();
            //         $("#gp_shipping_error").text("Ingrese nuevamente el nombre de la calle");
            //         scroll_element('#_gp_autocompletado');
            //         // alert("Ingrese nuevamente el nombre de la calle");
            //     } else if (datos[1] == '') {
            //         e.preventDefault();
            //         $("#gp_shipping_error").text("Ingrese el número exterior de su domicilio");
            //         scroll_element('#_gp_autocompletado');
            //         // alert("Ingrese el número exterior de su domicilio");
            //     } else if (datos[2] == '') {
            //         e.preventDefault();
            //         $("#gp_shipping_error").text("Ingrese nuevamente la colónia");
            //         scroll_element('#_gp_autocompletado');
            //         // alert("Ingrese nuevamente la colónia");
            //     } else if (datos[3] == '') {
            //         e.preventDefault();
            //         $("#gp_shipping_error").text("Ingrese nuevamente su dirección");
            //         scroll_element('#_gp_autocompletado');
            //         // alert("Ingrese nuevamente su dirección");
            //     }
            // });;
        }
    });

    function scroll_element(elemento) {
        $(elemento).addClass("gp_shipping_error_input");
        $('html, body').animate({
            scrollTop: $(elemento).offset().top - 300
        });
    }

    function initAutocompleteMap(elemento) {

        // crear mapa
        var lat_guardada = $("#gp_lat_shipping").val();
        var lng_guardada = $("#gp_lng_shipping").val();

        if (lat_guardada == '' || lng_guardada == '') {
            lat_guardada = 19.4326077;
            lng_guardada = -99.13320799999997;
        }

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 19,
            center: new google.maps.LatLng(lat_guardada, lng_guardada),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false,
            mapTypeControl: false,
            fullscreenControl: false,
            restriction: {
                latLngBounds: {
                    north: 33,
                    south: 14,
                    east: -85,
                    west: -120,
                }
            }

        });

        // creates a draggable marker to the given coords
        var vMarker = new google.maps.Marker({
            position: new google.maps.LatLng(lat_guardada, lng_guardada),
            draggable: true
        });

        // adds the marker on the map
        vMarker.setMap(map);

        // autcocomplete input
        let autocomplete;
        const options = {
            componentRestrictions: { country: "mx" },
            fields: ["address_components", "geometry"]
        };
        var input = document.getElementById(elemento);
        autocomplete = new google.maps.places.Autocomplete(input, options);
        autocomplete.addListener('place_changed', function() {

            var place = autocomplete.getPlace();
            var adrs = place.address_components;

            console.log('autocompletado');
            console.log(adrs);

            let address1 = "";
            let address_num = "";
            let postcode = "";
            let locality = "";
            let administrative_area_level_1 = "";
            let country = "";
            let address_long = "";
            let address_short = '';
            let sublocality_level_1 = '';

            for (const component of adrs) {
                const componentType = component.types[0];
                switch (componentType) {

                    case "route":
                        address1 = component.short_name;
                        break;
                    case "street_number":
                        address_num = component.long_name;
                        break;
                    case "postal_code":
                        postcode = `${component.long_name}${postcode}`;
                        break;
                    case "locality":
                        locality = component.long_name;
                        break;
                    case "administrative_area_level_1":
                        administrative_area_level_1 = component.short_name;
                        break;
                    case "country":
                        country = component.long_name;
                        break;
                    case "sublocality_level_1":
                        sublocality_level_1 = component.long_name;
                        break;
                }
            }
            const address_txt = document.querySelector('#gp_address');
            const status = document.querySelector('#ubicacion_status');

            var lat = place.geometry.location.lat(),
                lng = place.geometry.location.lng();

            address_long = address1 + ' ' + address_num + ', ' + sublocality_level_1 + ', ' + postcode + ', ' + locality + ', ' + administrative_area_level_1 + ', ' + country;
            // address_short = address1 + ', ' + postcode;
            // address_short = locality + ', ' + administrative_area_level_1 + ' ' + postcode;
            if (postcode) {
                address_short = administrative_area_level_1 + ', CP ' + postcode;
            } else {
                address_short = administrative_area_level_1 + ', ' + country;
            }

            const z = document.getElementsByClassName("gp_de_ubicacion");
            for (var i = 0; i < z.length; i++) {
                z[i].innerText = decodeURIComponent(address_long);
            }
            gpCookie('_gp_geo_address_long', encodeURIComponent(address_long), 365);
            gpCookie('_gp_geo_address_short', encodeURIComponent(address_short), 365);
            gpCookie('_gp_geo_lat', lat, 365);
            gpCookie('_gp_geo_lng', lng, 365);
            gpCookie('_gp_geo_pc', postcode, 365);

            $("#gp_shipping_address").val(address1 + '|' + address_num + '|' + sublocality_level_1 + '|' + administrative_area_level_1);

            $("#shipping_address_1").val(address1);
            $("#_gp_exterior_number").val(address_num);
            $("#shipping_city").val();
            $("#_gp_suburb").val(sublocality_level_1);

            $("#gp_lat_shipping").val(lat);
            $("#gp_lng_shipping").val(lng);
            $("#shipping_postcode").val(postcode);
            $("#shipping_state").val('');
            $("#select2-shipping_state-container").html("Elige una opción…");

            const x = document.getElementsByClassName("gp_direccion");
            // address_txt.textContent = address_short;
            for (var i = 0; i < x.length; i++) {
                x[i].innerText = "Enviar a " + decodeURIComponent(address_short);
            }

            // status.textContent = '¡Ubicación actualizada!';
            $('.mfp-bg.mfp-ready').click();
            $('#autocomplete').val('');

            vMarker.setPosition(new google.maps.LatLng(lat, lng));


            // centers the map on markers coords
            map.setCenter(new google.maps.LatLng(lat, lng));

            // adds the marker on the map
            vMarker.setMap(map);

        });
        // adds a listener to the marker
        // gets the coords when drag event ends
        // then updates the input with the new coords
        google.maps.event.addListener(vMarker, 'dragend', function(evt) {
            let lat_marker = evt.latLng.lat().toFixed(6);
            let lng_marker = evt.latLng.lng().toFixed(6);
            var geocoder = new google.maps.Geocoder();

            const latlng = {
                lat: parseFloat(lat_marker),
                lng: parseFloat(lng_marker),
            };

            geocoder.geocode({ location: latlng }).
            then((response) => {
                    if (response.results[0]) {
                        map.setZoom(19);

                        var adrss = response.results[0].address_components;

                        let podigo_postal = '';
                        let ciudad = '';
                        let colonia = '';
                        let calle = '';
                        let numero = '';

                        console.log('mapa');
                        console.log(adrss);
                        for (const componente of adrss) {
                            const tipoComponente = componente.types[0];
                            switch (tipoComponente) {
                                case "route":
                                    calle = componente.long_name;
                                    break;
                                case "street_number":
                                    numero = componente.long_name;
                                    break;
                                case "political":
                                    colonia = componente.long_name;
                                    break;
                                case "postal_code":
                                    podigo_postal = componente.long_name;
                                    break;
                                case "administrative_area_level_1":
                                    ciudad = componente.short_name;
                                    break;
                            }
                        }
                        gpCookie('_gp_geo_address_long', encodeURIComponent(response.results[0].formatted_address), 365);
                        gpCookie('_gp_geo_address_short', encodeURIComponent(ciudad + ', CP ' + podigo_postal), 365);
                        gpCookie('_gp_geo_lat', lat_marker, 365);
                        gpCookie('_gp_geo_lng', lng_marker, 365);
                        gpCookie('_gp_geo_pc', podigo_postal, 365);

                        $("#_gp_autocompletado").val(response.results[0].formatted_address);
                        $("#shipping_address_1").val(calle);
                        $("#_gp_exterior_number").val(numero);
                        $("#shipping_city").val();
                        $("#_gp_suburb").val(colonia);


                        $("#gp_shipping_address").val(calle + '|' + numero + '|' + colonia + '|' + ciudad);
                        $("#gp_lat_shipping").val(lat_marker);
                        $("#gp_lng_shipping").val(lng_marker);
                        $("#shipping_postcode").val(podigo_postal);
                        $("#shipping_city").val(ciudad);
                        $("#shipping_state").val('');
                        $("#select2-shipping_state-container").html("Elige una opción…");
                    } else {
                        console.log("No results found");
                    }
                })
                .catch((e) => console.log("Geocoder failed due to: " + e));

            map.panTo(evt.latLng);
        });
    }

    $("#password").ready(function() {
        $("<span id='eye_password' class='gp-show-password-input dashicons dashicons-hidden'></span>").insertBefore("#password");
        $("#eye_password").click(function() {
            if ($(this).hasClass('dashicons-visibility')) {
                $(this).removeClass('dashicons-visibility').addClass('dashicons-hidden');
                $("#password").prop('type', 'password');
            } else {
                $(this).removeClass('dashicons-hidden').addClass('dashicons-visibility');
                $("#password").prop('type', 'text');
            }

        });
    });

    $("#reg_password").ready(function() {
        $("<span id='eye_reg_password' class='gp-show-password-input dashicons dashicons-hidden'></span>").insertBefore("#reg_password");
        $("#eye_reg_password").click(function() {
            if ($(this).hasClass('dashicons-visibility')) {
                $(this).removeClass('dashicons-visibility').addClass('dashicons-hidden');
                $("#reg_password").prop('type', 'password');
            } else {
                $(this).removeClass('dashicons-hidden').addClass('dashicons-visibility');
                $("#reg_password").prop('type', 'text');
            }

        });
    });

    $("#gp_nombre_producto").ready(function() {
        if ($("#gp_nombre_producto").length == 1) {
            $("h1.product-title.product_title.entry-title").text($("#gp_nombre_producto").val());
        }
    });

    $("div.badge-container.is-larger.absolute.right.top.z-1").ready(function() {
        if ($("#condicion").length >= 1) {
            let condicion = $("#condicion").val();
            if (condicion.startsWith('P')) {
                if ($("div.badge-container.is-larger.absolute.right.top.z-1").length >= 1) {
                    $("div.badge-container.is-larger.absolute.right.top.z-1").remove();
                }
            }
        }

    });


    /** TABLA MY ORDERS */

    var isMobile = window.matchMedia("only screen and (max-width: 800px)");
    $(document).on('click', '.woocommerce-orders-table__row', function() {
        if (isMobile.matches) {
            location.href = $(this).attr('order-url');
            //console.log("es mobile")
        }
    })

    /*** LOGIN DE USUARIO */
    function gp_login_button() {
        try {
            const userName = gpGetCookie('_gp_user_name');
            if (userName !== null && userName !== '' && userName !== 'NO USER') {
                $("#gp_mi_cuenta_no_sesion").hide();
                $("#gp_mi_cuenta_no_sesion_mob").hide();

                $("#mi_cuenta_user_name").html(decodeURIComponent(userName));
                $("#mi_cuenta_user_name_mob").html(decodeURIComponent(userName));

                $("#gp_mi_cuenta_sesion").show();
                $("#gp_mi_cuenta_sesion_mob").show();

            } else {

                if (user_logged.check) {
                    console.log('>>Usuario no llogeado antes del ccambio');

                    $("#link_login").replaceWith(`
                    <a href="${site_url.home}/my-account" class="nav-top-link nav-top-not-logged-in is-small">
                        <span>Ingreso/Registro</span>
                    </a>
                    `);
                }
            }
        } catch (error) {
            console.log("No se pudo identifiar al usuario", error);
        }
    }

})(jQuery);

if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}