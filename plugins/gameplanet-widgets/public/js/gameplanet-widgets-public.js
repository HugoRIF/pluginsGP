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
    $(document).ready(function() {
        const y = document.getElementsByClassName("gp_single_product_direccion");
        if (gpGetCookie('_gp_geo_address_long')) {
            let address_long = decodeURIComponent(gpGetCookie('_gp_geo_address_long'));
            for (var i = 0; i < y.length; i++) {
                y[i].innerText = decodeURIComponent(address_long);
            }
        }
        // if ($("#tienda_seleccionada").length != 0) {
        //     if (gpGetCookie('_gp_tienda_favorita_nombre')) {
        //         let shop_name = decodeURIComponent(gpGetCookie('_gp_tienda_favorita_nombre'));
        //         $("#tienda_seleccionada").html("Recoger en " + shop_name);
        //     }
        // }
        if ($("#tienda_seleccionada").length != 0) {
            console.log('tienda_seleccionada');
            if (gpGetCookie('_gp_tienda_favorita_nombre') && gpGetCookie('_gp_tienda_favorita_id')) {
                console.log('cookie');
                let shop_id = decodeURIComponent(gpGetCookie('_gp_tienda_favorita_id'));
                let shop_name = decodeURIComponent(gpGetCookie('_gp_tienda_favorita_nombre'));
                const tiendas_disponibles = $("#recoger_tienda > ul li span");
                let tienda = false;
                let valores = [];
                tiendas_disponibles.each(function() {
                    tienda = $(this).hasClass("gp_btn_tienda_selec")
                    if (tienda) {
                        valores = $(this).attr('value').split(',')
                        if (valores[0] == shop_id) {
                            $("#tienda_seleccionada").html("Recoger en " + shop_name);
                            $("#gp_mensaje").html("");
                        }
                    }
                });
                // if ($("#recoger_tienda > ul li span").length > 0) {
                //     console.log('ul li');
                // }
            }
        }

    });
    //* globales default *//
    const _gp_geo_lng = '-99.12766';
    const _gp_geo_lat = '19.42847';
    const _gp_geo_address_short = 'CDMX, CP 06000';
    const _gp_geo_address_long = 'Talavera, República de El Salvador, Centro, Cuauhtémoc, 06000 Ciudad de México, CDMX';
    const _gp_tienda_default_id = 1;
    const _gp_tienda_default_nombre = 'GP Santa Fe I';

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
                                }
                            });


                        } else {
                            console.log("sin envio a domicilio");
                            $("#gp_mensaje_domicilio").html('<p>Por el momento no hay disponibilidad en esta zona, te sugerimos cambiar la dirección de envío. Code: WJS-002</p>');
                            $('.domicilio').prop('checked', false);
                        }
                    }

                });
            } else {
                console.log("estatus 0");
                $("#gp_mensaje_domicilio").html('<p>Por el momento no hay disponibilidad en esta zona, te sugerimos cambiar la dirección de envío. Code: WJS-001</p>');
                $('.domicilio').prop('checked', false);
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

    function gp_direccion_guardada() {
        var atributo = $(".gp_btn_ship").attr('gp_dir');
        var elementos = atributo.split(';');

        gpCookie('_gp_geo_address_long', encodeURIComponent(elementos[0]), 365);
        gpCookie('_gp_geo_address_short', encodeURIComponent(elementos[1]), 365);
        gpCookie('_gp_geo_lat', elementos[2], 365);
        gpCookie('_gp_geo_lng', elementos[3], 365);
        gpCookie('_gp_geo_pc', elementos[4], 365);

        const x = document.getElementsByClassName("gp_direccion");
        // address_txt.textContent = address_short;
        for (var i = 0; i < x.length; i++) {
            x[i].innerText = "Enviar a " + decodeURIComponent(elementos[1]);
        }

        // status.textContent = '¡Ubicación actualizada!';
        $('.mfp-bg.mfp-ready').click();
        $('#autocomplete').val('');
    }

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

    function initAutocomplete2() {
        let autocomplete;
        const options = {
            componentRestrictions: { country: "mx" },
            fields: ["address_components", "geometry"]
        };
        var input = document.getElementById('cambiar_direccion');
        autocomplete = new google.maps.places.Autocomplete(input, options);
        autocomplete.addListener('place_changed', function() {

            $("#gp_single_product_button").prop("disabled", true);
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

            f_gp_disponibilidad_domicilio();

            const x = document.getElementsByClassName("gp_direccion");
            const y = document.getElementsByClassName("gp_single_product_direccion");
            for (var i = 0; i < x.length; i++) {
                x[i].innerText = "Enviar a " + decodeURIComponent(address_short);
            }
            for (var i = 0; i < y.length; i++) {
                y[i].innerText = decodeURIComponent(address_long);
            }
            $('.mfp-bg.mfp-ready').click();
            $("#domicilio").click();
            $("#gp_single_product_button").prop("disabled", true);
            $('#cambiar_direccion').val('');

        })
    }

    $("#modal_cambiar_direccion").ready(function() {
        $("#modal_cambiar_direccion").on('click', '.gp_btn_ship', function() {
            $("#gp_single_product_button").prop("disabled", true);
            gp_direccion_guardada();
            f_gp_disponibilidad_domicilio();
            $('.mfp-bg.mfp-ready').click();
            $("#domicilio").click();
        });
    });

    $(".mi_ubicacion_d").ready(function() {
        $(".mi_ubicacion_d").on('click', function() {
            $("#gp_single_product_button").prop("disabled", true);
            gpFindeMe();
            $("#domicilio").click();
            // f_gp_disponibilidad_domicilio();
            return false;
        });
    });

    $(".gp_disponibilidad").ready(function() {
        $(".gp_disponibilidad").click(function() {
            $('#status_disponibilidad_tienda').html('Cargando...');
            if (typeof(gp_disponibilidad_var) === 'undefined') {
                console.log("no")
                $('#tiendas_disponibles').html('Error. Code: GPJ-101');
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
                    element.subtipo.almacenes.forEach((element2) => {
                        if (element2.cantidad > 0) {
                            color = "gp_c_verde";
                        } else {
                            color = "gp_c_gris";
                        }
                        html += `
                        <li class="li_disponibilidad">
                            <div class="row row-collapse align-left">
                                <div class="col medium-9 small-12 large-9">
                                    <div class="col-inner" style="padding-right: 1em;">
                                        <h3 style="margin-bottom: 0px;">${element2.nombre}</h3>
                                        <p>${element2.direccion}</p>
                                    </div>
                                </div>
                                <div class="col medium-3 small-3 large-3">
                                    <div class="col-inner">
                                        <div style="margin-right: 1em; text-align: center;">
                                            <h3 class="gp_h3_disponibilidad ${color}">${element2.cantidad}</h3>
                                            <p class="gp_fs_p8em">pieza(s)<br/>disponibles</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row row-collapse align-left">
                                <p>
                                    <a href="tel:+${element2.telefono}">${element2.telefono}</a>
                                </p>
                            </div>
                        </li>

                        <hr class="hr_gp">
                    `;
                    });
                    html += '</ul>';
                    $('#status_disponibilidad_tienda').html(html);
                }
            });
        });
    });

    $("#cambiar_direccion").ready(function() {
        if ($("#cambiar_direccion").length) {
            initAutocomplete2();
            $("#cambiar_direccion").click(function() {
                $('#ubicacion_status').html('');
            });
        }
    });

})(jQuery);