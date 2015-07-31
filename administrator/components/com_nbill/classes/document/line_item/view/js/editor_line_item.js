var line_items;
var new_line_item = null;
var quantity_precision = quantity_precision ? quantity_precision : 0;
var tax_rate_precision = tax_rate_precision ? tax_rate_precision : 2;
var decimal_precision = decimal_precision ? decimal_precision : 2;
var currency_precision = currency_precision ? currency_precision : 2;
var currency_total_precision = currency_total_precision ? currency_total_precision : 2;

function LineItemsCollection(json_object, sectionClassName) {
    this.currency = null;
    this.sections = null;

    if (json_object) {
        for (var key in json_object) {
            if (key == 'sections') {
                var sections = [];
                var index;
                for (index = 0; index < json_object[key].length; ++index) {
                    if (typeof(sectionClassName) != 'undefined') {
                        sections[index] = new sectionClassName(json_object[key][index]);
                    } else {
                        sections[index] = new LineItemsSection(json_object[key][index]);
                    }
                    sections[index].index = index;
                }
                this[key] = sections;
            } else {
                this[key] = (json_object[key]);
            }
        }
    }
}
function LineItemsSection(json_object, line_item_type) {
    this.index = null;
    this.line_items = null;
    this.section_name = '';
    this.discount_title = '';
    this.discount_percent = null;
    this.discount_net = null;
    this.discount_tax = null;
    this.discount_gross = null;

    if (json_object) {
        for (var key in json_object) {
            if (key == 'line_items') {
                var line_items = [];
                var index;
                for (index = 0; index < json_object[key].length; ++index) {
                    if (typeof line_item_type == 'undefined') {
                        line_items[index] = new LineItem(json_object[key][index]);
                    } else {
                        line_items[index] = new line_item_type(json_object[key][index]);
                    }
                    line_items[index].section_index = this.index;
                }
                this[key] = line_items;
            } else {
                this[key] = (json_object[key]);
            }
        }
    }
}
function LineItem(json_object) {
    this.id = null;
    this.section_index = null;
    this.index = null;
    this.type = null;
    this.vendor_id = null;
    this.document_id = null;
    this.ordering = null;
    this.entity_id = null;
    this.nominal_ledger_code = null;
    this.nominal_ledger_description = null;
    this.product_description = null;
    this.detailed_description = null;
    this.net_price_per_unit = null;
    this.no_of_units = null;
    this.discount_percentage = null;
    this.discount_amount = null;
    this.discount_description = null;
    this.net_price_for_item = null;
    this.tax_rate_for_item = null;
    this.tax_for_item = null;
    this.product_shipping_units = null;
    this.shipping_id = null;
    this.shipping_for_item = null;
    this.tax_rate_for_shipping = null;
    this.tax_for_shipping = null;
    this.gross_price_for_item = null;
    this.product_code = null;
    this.page_break = null;
    this.electronic_delivery = null;

    if (json_object) {
        for (var key in json_object) {
            this[key] = (json_object[key]);
        }
    }
}
LineItem.prototype.reCalculateItemTotals = function(recalc_discount)
    {
        this.mapFromElements();
        this.reCalculateItemNet(true, recalc_discount);
    }

LineItem.prototype.reCalculateItemNet = function(mapping_done, recalc_discount)
    {
        if (!mapping_done) {
            this.mapFromElements();
        }
        var net_total = this.net_price_per_unit.value * this.no_of_units.value;
        if (recalc_discount) {
            this.discount_amount.value = format_decimal((net_total / 100) * this.discount_percentage.value, currency_total_precision);
        }
        this.net_price_for_item.value = format_decimal(net_total - this.discount_amount.value, currency_precision);
        this.reCalculateItemTax(true);
    }

LineItem.prototype.reCalculateItemTax = function(mapping_done)
    {
        if (!mapping_done) {
            this.mapFromElements();
        }
        this.tax_for_item.value = format_decimal((this.net_price_for_item.value / 100) * this.tax_rate_for_item.value, currency_total_precision);
        this.reCalculateShipping(true);
    }

LineItem.prototype.reCalculateShipping = function(mapping_done)
    {
        if (!mapping_done) {
            this.mapFromElements();
        }
        var shipping_method = this.findShippingMethod(this.shipping_id);

        /*this.shipping_for_item.value = format_decimal(0, currency_total_precision);
        this.tax_rate_for_shipping.value = this.tax_rate_for_item.value;*/
        if (shipping_method) {
            this.shipping_for_item.value = format_decimal(shipping_method.net_price.value, currency_total_precision);
            if (!shipping_method.is_fixed_per_invoice || shipping_method.is_fixed_per_invoice == '0') {
                this.shipping_for_item.value = format_decimal(this.shipping_for_item.value * this.product_shipping_units.value * this.no_of_units.value, currency_total_precision);
            }
            if (!shipping_method.is_taxable || shipping_method.is_taxable == '0') {
                this.tax_rate_for_shipping.value = format_decimal(0, tax_rate_precision);
            } else {
                if (shipping_method.tax_rate_if_different.value > 0) {
                    this.tax_rate_for_shipping.value = format_decimal(shipping_method.tax_rate_if_different.value, tax_rate_precision);
                }
            }
        }

        this.reCalculateShippingTax(true);
    }

LineItem.prototype.reCalculateShippingTax = function(mapping_done)
    {
        if (!mapping_done) {
            this.mapFromElements();
        }
        this.tax_for_shipping.value = format_decimal((this.shipping_for_item.value / 100) * this.tax_rate_for_shipping.value, currency_total_precision);

        this.reCalculateGross(true);
    }

LineItem.prototype.reCalculateGross = function(mapping_done)
    {
        if (!mapping_done) {
            this.mapFromElements();
        }
        this.gross_price_for_item.value = format_decimal((this.net_price_for_item.value * 1) + (this.tax_for_item.value * 1) + (this.shipping_for_item.value * 1) + (this.tax_for_shipping.value * 1), currency_total_precision);
        this.mapToElements();
    }

LineItem.prototype.findShippingMethod = function(shipping_id)
    {
        var shipping_method = null;
        if (shipping_id && shipping_methods) {
            for(var i=0;i<shipping_methods.length;i++)
            {
                if(shipping_methods[i].id==shipping_id) {
                    shipping_method = shipping_methods[i];
                    break;
                }
            }
        }
        return shipping_method;
    }

LineItem.prototype.mapFromElements = function()
    {
        this.product_code = this.mapFromElement('product_code');
        this.product_description = this.mapFromElement('product_description');
        this.detailed_description = this.mapFromElement('detailed_description');
        this.nominal_ledger_code = this.mapFromElement('nominal_ledger_code');
        this.discount_description = this.mapFromElement('discount_description');

        this.net_price_per_unit.value = format_decimal(this.mapFromElement('net_price_per_unit'), currency_precision);
        this.no_of_units.value = format_decimal(this.mapFromElement('no_of_units'), quantity_precision);
        this.discount_percentage.value = format_decimal(this.mapFromElement('discount_percentage'), decimal_precision);
        this.discount_amount.value = format_decimal(this.mapFromElement('discount_amount'), currency_total_precision);
        this.tax_rate_for_item.value = format_decimal(this.mapFromElement('tax_rate_for_item'), tax_rate_precision);
        this.shipping_id = this.mapFromElement('shipping_id');
        this.product_shipping_units.value = format_decimal(this.mapFromElement('product_shipping_units'), quantity_precision);
        if (!this.product_shipping_units.value) {
            this.product_shipping_units.value = 1;
        }
        this.tax_rate_for_shipping.value = format_decimal(this.mapFromElement('tax_rate_for_shipping'), tax_rate_precision);
        this.net_price_for_item.value = format_decimal(this.mapFromElement('net_price_for_item'), currency_total_precision);
        this.tax_for_item.value = format_decimal(this.mapFromElement('tax_for_item'), currency_total_precision);
        this.shipping_for_item.value = format_decimal(this.mapFromElement('shipping_for_item'), currency_total_precision);
        this.tax_for_shipping.value = format_decimal(this.mapFromElement('tax_for_shipping'), currency_total_precision);
        this.electronic_delivery = this.mapFromElement('electronic_delivery1');
    }

LineItem.prototype.mapFromElement = function(elem_id)
    {
        var elem = document.getElementById(elem_id);
        if (!elem) {
            elem = document.getElementById('editable_' + this.section_index + '_' + this.index + '_' + elem_id);
        }

        if (elem) {
            if (elem.getAttribute('type') == 'checkbox' || elem.getAttribute('type') == 'radio') {
                return elem.checked;
            } else if (elem.tagName == 'select') {
                return elem.options[elem.selectedIndex].value;
            } else {
                return elem.value;
            }
        } else {
            if (this[elem_id] && this[elem_id].hasOwnProperty('value')) {
                return this[elem_id].value;
            } else {
                if (!this[elem_id]) {
                    if ((elem_id.indexOf('0', elem_id.length - '0'.length) !== -1 ||
                                elem_id.indexOf('1', elem_id.length - '1'.length) !== -1)
                                && this.hasOwnProperty(elem_id.substr(0, elem_id.length - 1))) {
                        return this[elem_id.substr(0, elem_id.length - 1)];
                    }
                }
                return this[elem_id];
            }
        }
    }

LineItem.prototype.mapToElements = function()
    {
        this.mapToElement('product_code', this.product_code);
        this.mapToElement('product_description', this.product_description);
        this.mapToElement('detailed_description', this.detailed_description);
        this.mapToElement('nominal_ledger_code', this.nominal_ledger_code);
        this.mapToElement('discount_description', this.discount_description);

        this.mapToElement('net_price_per_unit', this.net_price_per_unit.value);
        this.mapToElement('no_of_units', this.no_of_units.value);
        this.mapToElement('discount_percentage', this.discount_percentage.value);
        this.mapToElement('discount_amount', this.discount_amount.value);
        this.mapToElement('shipping_id', this.shipping_id);
        this.mapToElement('product_shipping_units', this.product_shipping_units.value);
        this.mapToElement('net_price_for_item', this.net_price_for_item.value);
        this.mapToElement('tax_rate_for_item', this.tax_rate_for_item.value);
        this.mapToElement('tax_for_item', this.tax_for_item.value);
        this.mapToElement('net_price_for_shipping', this.shipping_for_item.value);
        this.mapToElement('tax_rate_for_shipping', this.tax_rate_for_shipping.value);
        this.mapToElement('shipping_for_item', this.shipping_for_item.value);
        this.mapToElement('tax_for_shipping', this.tax_for_shipping.value);
        this.mapToElement('electronic_delivery0', !this.electronic_delivery);
        this.mapToElement('electronic_delivery1', this.electronic_delivery);

        this.mapToElement('total_gross', this.gross_price_for_item.value, true);
        //document.getElementById('total_gross').innerHTML = this.gross_price_for_item.value;
    }

LineItem.prototype.mapToElement = function(elem_id, value, use_inner_html)
    {
        var elem = document.getElementById(elem_id);
        if (!elem) {
            elem = document.getElementById('editable_' + this.section_index + '_' + this.index + '_' + elem_id);
        }

        if (document.getElementById(elem_id)) {
            if (elem.getAttribute('type') == 'checkbox' || elem.getAttribute('type') == 'radio') {
                elem.checked = value;
            } else if (elem.tagName == 'select') {
                for(var i=0;i<elem.options.length;i++)
                {
                    if (elem.options[i].value == value) {
                        elem.selectedIndex = i;
                        break;
                    }
                }
            } else {
                if (use_inner_html) {
                    elem.innerHTML = value;
                } else {
                    elem.value = value;
                }
            }
        }
    }

function submitLineItemAjaxTask(task_name, parameters, callback)
{
    document.getElementById('line_items').value=JSON.stringify(line_items);

    if (typeof(callback) == 'undefined') {
        callback = refreshLineItems;
    }
    var currency = '';
    var document_type = document.getElementById('document_type').value;
    var elem_currency = document.getElementById('currency');
    if (elem_currency && elem_currency.options) {
        currency = elem_currency.options[elem_currency.selectedIndex].value
    }

    if (callback == refreshLineItems) {
        showLineItemUpdateMessage();
    }

    var line_items_params = '&line_items=' + encodeURIComponent(JSON.stringify(line_items));
    submit_ajax_request(task_name, 'selected_tab=' + document.getElementById('nbill_selected_tab_line_item_settings').value + '&document_type=' + document_type + '&currency_code=' + currency + '&' + parameters + line_items_params, callback, false);
}

function showLineItemUpdateMessage(message)
{
    elm = document.getElementById('line_item_editor');
    _width = elm.offsetWidth;
    _height = elm.offsetHeight;
    _top = elm.offsetTop;
    _left = elm.offsetLeft;
    overlay = document.getElementById('line_item_editor_overlay');
    overlay.style.width = _width + "px";
    overlay.style.height = _height + "px";
    overlay.style.lineHeight = _height + "px";
    overlay.style.display='block';
}

function refreshLineItems(response)
{
    response_parts = response.split('#!#');
    response_html = response_parts[0];
    response_json = response_parts[1];
    response_type = response_parts[2];
    response_tab = response_parts[3];
    product_add = response_parts[4];
    product_update = response_parts[5];

    var sectionClassName = LineItemsSection;
    switch (response_type)
    {
        case 'QU':
            sectionClassName = QuoteLineItemsSection;
            break;
    }

    document.getElementById('line_item_editor_overlay').style.display='none';

    if (response_html.length == 0) {
        document.getElementById('line_item_editor').innerHTML = '<strong style="color:#ff0000">Sorry! An error occurred. Please refresh the page and try again.</strong>';
    } else {
        document.getElementById('line_item_editor').innerHTML = response_html;
        line_items = new LineItemsCollection(JSON.parse(response_json), sectionClassName);
        if (response_tab.length == 0) {
            response_tab = 'nbill-tab-title-line_item_settings-line_item_basic';
        }
        select_tab_line_item_settings(response_tab);
    }

    document.getElementById('line_items').value=JSON.stringify(line_items);
    if (product_add && product_add != '0') {
        var added_elem = document.getElementById('product_added');
        if (added_elem.value.length > 0) {
            added_elem.value+=',';
        }
        added_elem.value+=product_add;
    }
    if (product_update && product_update != '0') {
        var updated_elem = document.getElementById('product_updated');
        if (updated_elem.value.length > 0) {
            updated_elem.value+=',';
        }
        updated_elem.value+=product_update;
    }

    if (response_type == 'QU') {
        setQuoteStatus();
    }
}

function refreshPopup(response, delay)
{
    if (document.getElementById('tinybox_popup_content')) {
        if (response.length == 0) {
            document.getElementById('tinybox_popup_content').innerHTML = '<strong style="color:#ff0000">Sorry! An error occurred. Please refresh the page and try again.</strong>';
        } else {
            document.getElementById('tinybox_popup_content').innerHTML = response;
        }
    } else {
        switch (delay) {
            case 250:
                delay = 300;
                break;
            case 300:
                delay = 500;
                break;
            case 500:
                delay = 750;
                break;
            case 750:
                delay = 900;
                break;
            case 900: //Should rarely if ever get here - maybe if a browser plugin slows things down too much
                alert('Lightbox failed to open in a timely manner. Please try again. If the problem persists, remove any browser plugins you do not need, try a different (faster) browser, or reset your browser to its factory default state.');
                return; //Give up - lightbox did not open for some reason
            default:
                delay = 250;
                break;
        }
        window.setTimeout(function(){refreshPopup(response, true)}, delay); //Allow time for lightbox to open
    }
}

function deletePageBreak(section_index, item_index)
{
    line_items.sections[section_index].line_items[item_index].page_break = false;
    var page_break_row = document.getElementById('page_break_' + section_index + '_' + item_index + '_basic');
    page_break_row.parentNode.removeChild(page_break_row);
    page_break_row = document.getElementById('page_break_' + section_index + '_' + item_index + '_advanced');
    page_break_row.parentNode.removeChild(page_break_row);

    var img_break = document.getElementById('img_page_break_' + section_index + '_' + item_index + '_basic');
    img_break.src = img_break.src.replace('_disabled', '');
    img_break = document.getElementById('img_page_break_' + section_index + '_' + item_index + '_advanced');
    img_break.src = img_break.src.replace('_disabled', '');

    document.getElementById('line_items').value=JSON.stringify(line_items);
}

function populateSection(section)
{
    var form = document.getElementById('nbill_section_editor');
    section.discount_percent = form.section_discount_percent;
    section.discount_title = form.section_discount_title;
    section.section_name = form.section_name;
    return section;
}

function insertSection(section)
{
    var section_index = document.getElementById('section_index').value;
    var item_index = document.getElementById('item_index').value;

    //This item and the ones before it now belong to the new section, and the new section has to be inserted after the current section_index
    for (sibling_index = 0; sibling_index <= item_index; sibling_index++)
    {
        sibling = line_items.sections[section_index].line_items[sibling_index];
        line_items.sections[section_index].line_items.splice(sibling_index, 1);
        section.line_items.push(sibling);
    }
    line_items.sections.splice(section_index + 1, 0, section);

    submitLineItemAjaxTask('', '');
}

function loadProduct(elem_link, product_id)
{
    elem_link.style.cursor='wait';
    var tax_exemption_code = '';
    if (document.getElementById('tax_exemption_code')) {
        tax_exemption_code = document.getElementById('tax_exemption_code').value;
    }
    submit_ajax_request('get_product','product_id=' + product_id + '&client_id=' + document.getElementById('entity_id').value + '&tax_exemption_code=' + tax_exemption_code + '&vendor_id=' + document.getElementById('vendor_id').value + '&country_code=' + document.getElementById('billing_country').value + '&currency_code=' + document.getElementById('currency').value, function(content){elem_link.style.cursor='';populateProduct(content);});
}

function populateProduct(result)
{
    var product = JSON.parse(result);
    if (product) {
        ajax_apply_product('product_code', product.product_code, product.name, product.description, product.nominal_ledger_code, product.is_taxable, product.custom_tax_rate, product.setup_fee > 0 ? 1 : 0, product.net_price, product.payment_frequency, product.shipping_units, product.electronic_delivery, product.use_custom_tax_rate);
        getCurrentLineItem().reCalculateItemTotals();
        var sku_list = document.getElementById('div_sku_list');
        if (sku_list) {
            sku_list.style.display='none';
        }
    }
}

function getCurrentLineItem()
{
    var section_index = document.getElementById('section_index');
    var item_index = document.getElementById('item_index');
    var line_item = getLineItem(section_index.value, item_index.value);
    if (line_item) {
        return line_item;
    }
    return new_line_item;
}

function getLineItem(section_index, item_index)
{
    if (line_items.sections[section_index]) {
        var line_item = line_items.sections[section_index].line_items[item_index];
        if (line_item) {
            return line_item;
        }
    }
}

function inlineUpdate(field_name, section_index, item_index, new_value)
{
    var line_item=getLineItem(section_index, item_index);
    if (line_item) {
        if (field_name == 'product_description') {
            if(line_item.hasOwnProperty(field_name)) {
                line_item[field_name]=new_value;
                document.getElementById('line_items').value=JSON.stringify(line_items);
            }
        } else if(line_item.hasOwnProperty(field_name) && line_item[field_name].hasOwnProperty('value')) {
            //line_item[field_name].value=new_value;
            switch (field_name) {
                case 'net_price_per_unit':
                case 'no_of_units':
                case 'discount_amount':
                    line_item.reCalculateItemTotals();
                    break;
                case 'net_price_for_item':
                    var qty = document.getElementById('editable_' + section_index + '_' + item_index + '_no_of_units').value;
                    if (qty != 0 && qty != '0') {
                        document.getElementById('editable_' + section_index + '_' + item_index + '_net_price_per_unit').value = new_value / qty;
                    }
                    line_item.reCalculateItemTax();
                    break;
                case 'tax_for_item':
                    if (new_value != 0 && line_item.tax_rate_for_item.value == 0) {
                        if (line_item.net_price_for_item.value != 0) {
                            line_item.tax_rate_for_item.value = format_decimal((new_value / line_item.net_price_for_item.value) * 100, 6);
                        }
                    } else if (new_value == 0 && line_item.tax_rate_for_item.value != 0) {
                        line_item.tax_rate_for_item.value = 0;
                    }
                    line_item.reCalculateGross();
                    break;
                case 'shipping_for_item':
                case 'tax_rate_for_shipping':
                    line_item.reCalculateShippingTax();
                    break;
                case 'tax_for_shipping':
                    line_item.reCalculateGross();
                    break;
            }

            document.getElementById('line_items').value=JSON.stringify(line_items);

submitLineItemAjaxTask('refresh');
        }
    }
}

function preSubmitLineItem()
{
    //Save everything to the javascript object
    var current_item = getCurrentLineItem();
    if (current_item) {
        //If we are adding a new line item, append it to the list (have to do this before mapping, as mapping requires it to be there already)
        if ((current_item.section_index == null || current_item.section_index == line_items.sections.length - 1) && current_item.index>=line_items.sections[line_items.sections.length-1].line_items.length){
            line_items.sections[line_items.sections.length-1].line_items.push(current_item);
        }
        current_item.mapFromElements(document.getElementById('section_index').value);
    }
    document.getElementById('line_items').value=JSON.stringify(line_items);
}

function row_click(section_index, item_index)
{
    row_desc = document.getElementById('editable_' + section_index + '_' + item_index + '_product_description');
    if (row_desc && row_desc.offsetParent === null) {
        btn_row_edit = document.getElementById('nbill_line_item_edit_' + section_index + '_' + item_index);
        if (btn_row_edit) {
            if (btn_row_edit.onclick) {btn_row_edit.onclick();}
        }
    }
}