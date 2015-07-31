/**
* This file based on code originally developed by Chris Haythornthwaite, Boatfax Limited. Used with permission.
* Copyright 2011 Boatfax Limited. Adapted by Russell Walker, Netshine Software Limited for nBill. Adaptations
* Copyright 2012, Netshine Software Limited.
*/
var highlight_colour = '#dddddd';

function cstop(e)
{
    if (e.preventDefault)
    {
        e.preventDefault();
    }
    else
    {
        e.returnValue = false;
    }
}

function _km(e,cl,eid,i)//Key input manager
{
    /**
    *e = key event
    *cl = True From Input, False From List
    *eid = Input id
    *i = List index
    *ll=max list length
    */
    var km = e.keyCode;
    var f = document.getElementById(eid);
    var fl = document.getElementById(eid+'_product_list');
    var fd = document.getElementById(eid+'_div');
    var ftd = document.getElementById(eid+'_ta');
    var fli = document.getElementById(eid+'_li_'+i);
    var fai = document.getElementById(eid+'_a_'+i);
    var tae = null;
    var el=false;
    var eidh=false;

    if(cl) //If in the input field
    {
        if (f.value.length >0 && (km ==8 || km > 44) && (eid.substring(eid.length - 12) == 'product_code' || eid.substring(eid.length - 19) == 'product_description'))
        {
            vget_skus(f.value, eid);
            fd.style.display='';
            var curr_width = parseInt(f.style.width);
            fd.style.width = (curr_width + 6) +"px";
            ftd.style.width = (curr_width + 6) +"px";
            if(fl && fl.getElementsByTagName('li').length < 1)
            {
                fd.style.display='none';
            }
        }
        switch (km)
        {
            case (8):
                if (f.value.length ==0)
                {
                    fd.style.display='none';
                }
            break;
            case (13):
                fd.style.display='none';
                ftd.style.display='none';
                break;
            case (40)://If list exists then go to list
                cstop(e);
                if(fl && fd.style.display!='none')
                {
                    fai.focus();
                    document.getElementById(eid+'_li_'+i).className='list_focus';
                    /*tae = fai.title;//type-ahead
                    tat(tae,eid);*/
                }
                break;
            default:
            break;
        }
    }
    else//In List
    {
        var li = fl.getElementsByTagName('li').length;
        var lm = li-1; //List max
        if (document.getElementById(eid+'_li_x')){lm = li-2;}
        switch (km)
        {
        case (40):
            cstop(e);
            if(li > 1 && (i < lm))
            {
                document.getElementById(eid+'_li_'+i).className = eid+'_list li';
                i++;
                //lfhide(eid,0,0);
                if(el==false)//Not last element
                {
                    document.getElementById(eid+'_a_'+i).focus();
                    document.getElementById(eid+'_li_'+i).className ='list_focus';
                    /*tae = document.getElementById(eid+'_a_'+i).title;//type-ahead
                    tat(tae,eid);*/
                }
            }
            break;
        case (38):
            cstop(e);
            if (li > 0)
            {
                if(i==0)
                {
                    f.focus();
                    document.getElementById(eid+'_li_'+i).className = eid+'_list li';
                    tat('',eid);
                    f.style.color = '#000000';
                    ftd.style.display = 'none';
                }
                else
                {
                    document.getElementById(eid+'_li_'+i).className = eid+'_list li';
                    i--;
                    document.getElementById(eid+'_a_'+i).focus();
                    document.getElementById(eid+'_li_'+i).className ='list_focus';
                    /*tae = document.getElementById(eid+'_a_'+i).title;//type-ahead
                    tat(tae,eid);*/
                }
            }
            break;
        case (13):
            cstop(e);
            document.getElementById(eid+'_a_'+i).onclick();
            /*f.value = document.getElementById(eid+'_a_'+i).title;
            f.focus();
            fd.style.display='none';
            ftd.style.display = 'none';
            f.style.color = '#000000';*/
            break;
        default:
            f.focus();
            ftd.style.display = 'none';
            f.style.color = '#000000';
            break;
        }
    }
}

function tat(t,eid)
{
    var fta = document.getElementById(eid+'_ta');
    var fv = document.getElementById(eid).value;
    fv = fv.replace(/^\s+|\s+$/g,'').replace(/\s+/g,' ');
    var sp = t.search(/\s/);
    var spf = fv.search(/\s/);
    var fve = null;
    t = t.slice(fv.length);
    fv= fv.charAt(0).toUpperCase() + fv.slice(1);
    if(sp>0 && fv.length>sp)
    {
        if (spf>0)
        {
            fve = fv.substring(spf);
            fv=fv.substring(0,spf)+' '+fve.charAt(1).toUpperCase() + fve.slice(2);
        }
        else
        {
            fve = fv.substring(sp);
            fv=fv.substring(0,sp)+' '+fve.charAt(0).toUpperCase() + fve.slice(1);
            t=t.slice(1);
        }
    }
    fta.innerHTML = '<span class="ta_fend">'+fv+'</span><span class="ta_bend">'+t+'</span>';
    fta.style.display='';
    document.getElementById(eid).style.color='#FFFFFF';
}

function v_k(e)
{
    var e=e||window.event;
    var km = e.keyCode;
    switch (km)
    {
    case (13)://Return
    case (37)://Left
    case (39)://Right
    case (40)://Down
    return false;
    default:
    return true;
    }
}

function vget_skus(sku, eid)
{
    submit_ajax_request('document_get_products', 'cur_action=' + document.getElementById('action').value + '&product=' + sku + '&target=' + eid + '&vendor_id=' + document.getElementById('vendor_id').value + '&billing_country=' + document.getElementById('billing_country').value + '&currency=' + document.getElementById('currency').value, function(response){show_suggest(response, eid);}, false);
}

function show_suggest(response, eid)
{
    var qDisplay = document.getElementById(eid + '_div');

    var content = '<div class="auto_suggest_close"><a href="#" onclick="document.getElementById(\'' + eid + '_div\').style.display=\'none\';return false;">x</a></div>';
    content += '<ul id="' + eid + '_product_list" tabindex="0">';

    var result_array = response.split('@!@'); //@!@ = row delimter
    for(i = 0; i < result_array.length - 1; i++)
    {
        var this_result = result_array[i].split('#!#'); //#!# = field delimiter
        if (this_result.length == 11 || this_result.length == 13)
        {
            var display_name = this_result[0];
            var title = this_result[1].replace(/'/g, '\\\'').replace(/"/g, '&quot;');
            var sku = this_result[2].replace(/'/g, '\\\'').replace(/"/g, '&quot;');
            var product = this_result[3].replace(/'/g, '\\\'').replace(/"/g, '&quot;');
            var desc = this_result[4].replace(/'/g, '\\\'').replace(/"/g, '&quot;');
            var ledger = this_result[5].replace(/'/g, '\\\'').replace(/"/g, '&quot;');
            var taxable = parseInt(this_result[6]);
            var tax_rate = this_result[7];
            var setup_fee_warning = this_result[8];
            var net_price = this_result[9];
            var pay_freq = this_result[10];
            var product_shipping_units = this_result.length == 13 ? this_result[11] : 1;
            var electronic_delivery = this_result.length == 13 ? this_result[12] : 0;
            var onclick_handler = 'ajax_apply_product(\'' + eid + '\', \'' + sku + '\', \'' + product + '\', \'' + desc + '\', \'' + ledger + '\', ' + taxable + ', \'' + tax_rate + '\', \'' + setup_fee_warning + '\', \'' + net_price + '\', \'' + pay_freq + '\', \'' + product_shipping_units + '\', ' + electronic_delivery + ');document.getElementById(\'' + eid + '_div\').style.display=\'none\';return false';
            var onkeydown_handler = '_km(event,false,\'' + eid + '\',' + i + ');return false;';
            content += '<li class="product_code_list li" id="' + eid + '_li_' + i + '" onkeydown="' + onkeydown_handler + '" onmouseover="this.style.backgroundColor=\'' + highlight_colour + '\';this.style.color=\'#000000\';" onmouseout="this.style.backgroundColor=\'\';this.style.color=\'\'" onclick="' + onclick_handler + '"><a id="' + eid + '_a_' + i + '" tabindex="-1" title="' + title + '" onkeydown="' + onkeydown_handler + '" onclick="' + onclick_handler + '">' + display_name + '</li>';
        }
    }

    qDisplay.innerHTML = content;
    if (response.length == 0)
    {
        document.getElementById(eid + '_div').style.display='none';
    }
}

function ajax_apply_product(eid, sku, product, desc, ledger, taxable, custom_tax_rate, setup_fee_warning, net_price, pay_freq, product_shipping_units, electronic_delivery, use_custom_tax_rate)
{
    var item_key = eid.replace('nbill_', '');
    item_key = item_key.replace('_product_code', '');
    item_key = item_key.replace('_product_description', '');

    eid = eid.replace('product_code', 'xxxxxxxxxx');
    eid = eid.replace('product_description', 'xxxxxxxxxx');

    document.getElementById(eid.replace('xxxxxxxxxx', 'product_code')).value = sku;
    document.getElementById(eid.replace('xxxxxxxxxx', 'product_description')).value = product;
    var elem_ledger = document.getElementById('nbill_' + document.getElementById('vendor_id').value + '_ledger_' + item_key);
    if (!elem_ledger) {
        elem_ledger = document.getElementById('nominal_ledger_code');
    }
    elem_ledger.value = ledger;

    document.getElementById(eid.replace('xxxxxxxxxx', 'net_price_per_unit')).value = net_price;
    var actual_net_price = format_currency(net_price * document.getElementById(eid.replace('xxxxxxxxxx', 'no_of_units')).value);
    document.getElementById(eid.replace('xxxxxxxxxx', 'net_price_for_item')).value = actual_net_price;
    if (taxable)
    {
        if (custom_tax_rate > 0 || use_custom_tax_rate)
        {
            document.getElementById(eid.replace('xxxxxxxxxx', 'tax_rate_for_item')).value = format_currency(custom_tax_rate);
        }
        var actual_tax_rate = document.getElementById(eid.replace('xxxxxxxxxx', 'tax_rate_for_item')).value;
        document.getElementById(eid.replace('xxxxxxxxxx', 'tax_for_item')).value = format_currency((actual_net_price / 100) * actual_tax_rate);
    }
    else
    {
        document.getElementById(eid.replace('xxxxxxxxxx', 'tax_rate_for_item')).value = '0.00';
        document.getElementById(eid.replace('xxxxxxxxxx', 'tax_for_item')).value = '0.00';
    }

    if (document.getElementById(eid.replace('xxxxxxxxxx', 'product_shipping_units'))) {
        document.getElementById(eid.replace('xxxxxxxxxx', 'product_shipping_units')).value = product_shipping_units;
    }
    document.getElementById(eid.replace('xxxxxxxxxx', 'shipping_for_item')).value = '0.00';
    document.getElementById(eid.replace('xxxxxxxxxx', 'tax_rate_for_shipping')).value = '0.00';
    document.getElementById(eid.replace('xxxxxxxxxx', 'tax_for_shipping')).value = '0.00';

    if (document.getElementById(eid.replace('xxxxxxxxxx', 'electronic_delivery1'))) {
        document.getElementById(eid.replace('xxxxxxxxxx', 'electronic_delivery0')).checked = !electronic_delivery;
        document.getElementById(eid.replace('xxxxxxxxxx', 'electronic_delivery1')).checked = electronic_delivery;
    }

    if (document.getElementById(eid.replace('xxxxxxxxxx', 'pay_freq'))) {
        document.getElementById(eid.replace('xxxxxxxxxx', 'pay_freq')).value = pay_freq;
    } else if (document.getElementById(eid.replace('xxxxxxxxxx', 'quote_pay_freq'))) {
        document.getElementById(eid.replace('xxxxxxxxxx', 'quote_pay_freq')).value = pay_freq;
    }

    update_totals();

    if ((typeof ajax_apply_product.last_sku == 'undefined' || ajax_apply_product.last_sku != sku)
        || (typeof ajax_apply_product.last_key == 'undefined' || ajax_apply_product.last_key != eid))
    {
        //Don't want to duplicate this processing as it may be called more than once
        refresh_editor(eid.replace('xxxxxxxxxx', 'detailed_description'), desc);
        ajax_apply_product.last_sku = sku;
        ajax_apply_product.last_key = eid;
        if (setup_fee_warning == '1')
        {
            show_setup_fee_warning();
        }
    }
}