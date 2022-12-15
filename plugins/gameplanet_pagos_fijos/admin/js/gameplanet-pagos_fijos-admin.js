(function($) {
    'use strict';

     $(document).ready(function (){
        if($("#woocommerce_gp_pagos_fijos_sandbox").length){
            is_sandbox();
    
            $("#woocommerce_gp_pagos_fijos_sandbox").on("change", function(e){
                is_sandbox();
            });
        }
    
        function is_sandbox(){
            const sandbox = $("#woocommerce_gp_pagos_fijos_sandbox").is(':checked');
            if(sandbox){
                $("input[name*='live']").parent().parent().parent().hide();
                $("input[name*='test']").parent().parent().parent().show();
            }else{
                $("input[name*='test']").parent().parent().parent().hide();
                $("input[name*='live']").parent().parent().parent().show();
            }
        }

        if($("#woocommerce_gp_pagos_fijos_msi_type").length){
            gp_pagos_fijos_change_msi_type();
            $("#woocommerce_gp_pagos_fijos_msi_type").on("change", function(e){
                gp_pagos_fijos_change_msi_type();
            });
        }
        
        function gp_pagos_fijos_change_msi_type() {
            const msi_type = $("#woocommerce_gp_pagos_fijos_msi_type").val();
            switch (msi_type) {
                case 'all':
                    $("input[name*='msi_product_meta']").parent().parent().parent().hide();
                    
                    break;
                case 'meta':
                    $("input[name*='msi_product_meta']").parent().parent().parent().show();
                
                    break;
                default:
                    break;
            }

        }

        if($("#woocommerce_gp_pagos_fijos_msi").length){
            gp_pagos_fijos_change_msi();
            $("#woocommerce_gp_pagos_fijos_msi").on("change", function(e){
                gp_pagos_fijos_change_msi();
            });
        }
        function gp_pagos_fijos_change_msi() {
            const msi = $("#woocommerce_gp_pagos_fijos_msi").val();
            if(msi.length){
                $("#woocommerce_gp_pagos_fijos_msi_type").parent().parent().parent().show();
                $("#woocommerce_gp_pagos_fijos_minimum_amount_interest_free").parent().parent().parent().show();
            }
            else{
                $("#woocommerce_gp_pagos_fijos_msi_type").parent().parent().parent().hide();
                $("#woocommerce_gp_pagos_fijos_minimum_amount_interest_free").parent().parent().parent().hide();
            }
        }
        
      });

})(jQuery);