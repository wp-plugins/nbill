function extract_and_execute_js(output_to_parse, output_is_elem_id, delayed)
{
    if (output_is_elem_id && !document.getElementById(output_to_parse)) {
        //Element does not exist yet!
        if (!delayed) {
            window.setTimeout(function(){extract_and_execute_js(output_to_parse, true, true);},1500);
        }
    }
    if (output_is_elem_id && document.getElementById(output_to_parse)) {
        output_to_parse = document.getElementById(output_to_parse).innerHTML;
    }
    if (typeof output_to_parse === 'undefined') {
        var output_to_parse = document.getElementsByTagName('html')[0].innerHTML;
    }
    if(output_to_parse != '') {
        var script = "";
        output_to_parse = output_to_parse.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(){if (output_to_parse !== null) script += arguments[1] + '\n';return '';});
        if(script) {
            if (window.execScript) {
                window.execScript(script);
            } else {
                window.setTimeout(script, 0);
            }
        }
    }
}