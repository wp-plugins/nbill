function QuoteLineItemsSection(json_object)
{
    this.quote_atomic = false;
    LineItemsSection.call(this, json_object, QuoteLineItem);
}
QuoteLineItemsSection.prototype = new LineItemsSection();
QuoteLineItemsSection.prototype.constructor = QuoteLineItemsSection;
QuoteLineItemsSection.prototype.setQuoteItemAccepted = function(item_index, new_value) {
        if (this.quote_atomic) {
            //All must be set to the same value
            for (var index = 0; index < this.line_items.length; ++index) {
                this.line_items[index].quote_item_accepted = new_value;
            }
        } else {
            //Just set the one item
            this.line_items[item_index].quote_item_accepted = new_value;
        }
    }

function QuoteLineItem(json_object)
{
    this.quote_pay_freq = null;
    this.quote_auto_renew = null;
    this.quote_relating_to = null;
    this.quote_unique_invoice = null;
    this.quote_mandatory = null;
    this.quote_awaiting_payment = null;
    this.quote_item_accepted = null;
    this.quote_g_tx_id = null;

    LineItem.call(this, json_object);
}
QuoteLineItem.prototype = new LineItem();
QuoteLineItem.prototype.constructor = QuoteLineItem;
QuoteLineItem.prototype.parent = LineItem.prototype;

QuoteLineItem.prototype.mapFromElements = function(section_index)
    {
        this.quote_pay_freq = LineItem.prototype.mapFromElement.call(this, 'quote_pay_freq');
        this.quote_relating_to = LineItem.prototype.mapFromElement.call(this, 'quote_relating_to');
        this.quote_auto_renew = LineItem.prototype.mapFromElement.call(this, 'quote_auto_renew1');
        this.quote_unique_invoice = LineItem.prototype.mapFromElement.call(this, 'quote_unique_invoice1');
        this.quote_mandatory = LineItem.prototype.mapFromElement.call(this, 'quote_mandatory1');
        if (section_index) {
            QuoteLineItemsSection.prototype.setQuoteItemAccepted.call(line_items.sections[section_index], this.index, LineItem.prototype.mapFromElement.call(this, 'quote_item_accepted1'));
        } else {
            this.quote_item_accepted = LineItem.prototype.mapFromElement.call(this, 'quote_item_accepted1');
        }
        LineItem.prototype.mapFromElements.call(this);
    }
QuoteLineItem.prototype.mapToElements = function()
    {
        LineItem.prototype.mapToElement.call(this, 'quote_pay_freq', this.quote_pay_freq);
        LineItem.prototype.mapToElement.call(this, 'quote_relating_to', this.quote_relating_to);
        LineItem.prototype.mapToElement.call(this, 'quote_auto_renew0', !this.quote_auto_renew);
        LineItem.prototype.mapToElement.call(this, 'quote_auto_renew1', this.quote_auto_renew);
        LineItem.prototype.mapToElement.call(this, 'quote_unique_invoice0', !this.quote_unique_invoice);
        LineItem.prototype.mapToElement.call(this, 'quote_unique_invoice1', this.quote_unique_invoice);
        LineItem.prototype.mapToElement.call(this, 'quote_mandatory0', !this.quote_mandatory);
        LineItem.prototype.mapToElement.call(this, 'quote_mandatory1', this.quote_mandatory);
        LineItem.prototype.mapToElement.call(this, 'quote_item_accepted0', !this.quote_item_accepted);
        LineItem.prototype.mapToElement.call(this, 'quote_item_accepted1', this.quote_item_accepted);
        LineItem.prototype.mapToElements.call(this);
    }

function toggleAccepted(section_index, item_index, yes_text, no_text)
{
    old_value = line_items.sections[section_index].line_items[item_index].quote_item_accepted != 0;
    line_items.sections[section_index].setQuoteItemAccepted(item_index, !old_value);

    new_text = '';

    if (old_value) {
        new_src = document.getElementById('img_accepted_' + section_index + '_' + item_index).src.replace('tick', 'cross');
        new_text = no_text ? no_text : new_text;
    } else {
        new_src = document.getElementById('img_accepted_' + section_index + '_' + item_index).src.replace('cross', 'tick');
        new_text = yes_text ? yes_text : new_text;
    }

    if (!line_items.sections[section_index].quote_atomic) {
        img = document.getElementById('img_accepted_' + section_index + '_' + item_index)
        img.src = new_src;
        if (new_text) {
            img.parentNode.title = new_text;
            img.title = new_text;
            img.alt = new_text;
        }
    } else {
        for (var section_item_index=0; section_item_index < line_items.sections[section_index].line_items.length; ++section_item_index) {
            img = document.getElementById('img_accepted_' + section_index + '_' + section_item_index);
            img.src = new_src;
            if (new_text) {
                img.parentNode.title = new_text;
                img.title = new_text;
                img.alt = new_text;
            }
        }
    }

    setQuoteStatus();
    document.getElementById('line_items').value=JSON.stringify(line_items);
}

function setQuoteStatus()
{
    //Check whether quote is now fully or partially accepted (or revert to previous value if nothing accepted but it was new or quoted)
    var accepted_count = 0;
    var rejected_count = 0;
    var total_count = 0;
    var current_status = document.getElementById('qstatus').value;

    for (section_index = 0; section_index < line_items.sections.length; ++section_index)
    {
        for (item_index = 0; item_index < line_items.sections[section_index].line_items.length; ++item_index)
        {
            if (line_items.sections[section_index].line_items[item_index].quote_item_accepted != 0) {
                accepted_count++;
            } else {
                rejected_count++;
            }
            total_count++;
        }
    }

    var qstatus = document.getElementById('qstatus');
    if (total_count > 0 && accepted_count == total_count) {
        //All accepted
        qstatus.value='DD';
    } else if (accepted_count > 0) {
        //Part accepted
        qstatus.value='EE';
    } else if (qstatus.value == 'DD' || qstatus.value == 'EE') {
        //Revert
        qstatus.value=orig_status ? orig_status : current_status;
    }
}

function status_changed()
{
    var new_status = null;
    switch (document.getElementById('qstatus').value)
    {
        case 'AA':
        case 'CC':
        case 'FF':
            //No items accepted
            new_status = false;
            break;
        case 'DD':
            //All items accepted
            new_status = true;
            break;
    }
    if (new_status !== null && line_items.sections.length > 0 && line_items.sections[0].line_items.length > 0) {
        if (new_status != 0) {
            var new_src = document.getElementById('img_accepted_0_0').src.replace('cross', 'tick');
        } else {
            var new_src = document.getElementById('img_accepted_0_0').src.replace('tick', 'cross');
        }

        for (section_index = 0; section_index < line_items.sections.length; ++section_index)
        {
            for (item_index = 0; item_index < line_items.sections[section_index].line_items.length; ++item_index)
            {
                line_items.sections[section_index].line_items[item_index].quote_item_accepted = new_status;
                document.getElementById('img_accepted_' + section_index + '_' + item_index).src = new_src;
            }
        }
    }
    document.getElementById('line_items').value=JSON.stringify(line_items);
}