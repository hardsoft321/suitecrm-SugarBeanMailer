/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author Evgeny Pervushin <pea@lab321.ru>
 * @package SugarBeanMailer
 */

if(!lab321) var lab321 = {};
if(!lab321.email) lab321.email = {};

lab321.email.refreshNotifyTo = function(item) {
  a = $('.relate_id', item);
  if (a !== undefined) 
  $('[name=notify_to]', item).val(a.toArray().map(function(i) { return '^'+trim(i.value)+'^'; }).filter(function(i) { return i != '^^'; }).join(','));
}

lab321.email.cloneRecipientField = function(item) {
    var module = 'notification_copy';
    if(typeof lab321.email.cloneRecipientCount === "undefined") {
        lab321.email.cloneRecipientCount = $('.editlistitem', $(item).closest('#NotificationCopy_span')).length;
    }
    if(item.siblings('.editlistitem').length >= 20) {
        return;
    }
    var newId = lab321.email.cloneRecipientCount++;
    return $('<div class="editlistitem">').html(item.html())
    .find('[name]').each(function() {
        var name = this.name;
        this.name = this.name.replace(new RegExp(module+'\\[((new[0-9]+)|(template)|([a-f0-9\-]{36}))\\]'), module+'[new'+newId+']');
        this.id = this.name;

        var onclick = $(this).attr('onclick');
        if(onclick && (name.match(/\[btn_clr_.*\]/) || name.match(/btn_clr_.*/))) {
            $(this).attr('onclick', onclick.replace(/SUGAR\.clearRelateField\(this\.form,\s*'([^']+)',\s*'([^']+)'\)/, function(str, name, id) {
                name = name.replace(new RegExp(module+'\\[((new[0-9]+)|(template)|([a-f0-9\-]{36}))\\]'), module+'[new'+newId+']');
                id = id.replace(new RegExp(module+'\\[((new[0-9]+)|(template)|([a-f0-9\-]{36}))\\]'), module+'[new'+newId+']');
                lab321.email.refreshNotifyTo(this);
                return "SUGAR.clearRelateField(this.form, '"+name+"', '"+id+"')";
            }));
        }
        else if(onclick && (name.match(/\[btn_.*\]/) || name.match(/btn_.*/))) {
            $(this).attr('onclick', onclick.replace(/"field_to_name_array":{"id":"([^"]+)","name":"([^"]+)"}/, function(str, id, name) {
                name = name.replace(new RegExp(module+'\\[((new[0-9]+)|(template)|([a-f0-9\-]{36}))\\]'), module+'[new'+newId+']');
                id = id.replace(new RegExp(module+'\\[((new[0-9]+)|(template)|([a-f0-9\-]{36}))\\]'), module+'[new'+newId+']');
                return '"field_to_name_array":{"id":"'+id+'","name":"'+name+'"}';
            }));
        }
    })
    .end()
    .insertAfter(item.hasClass('item_template') ? item.siblings(':last') : item)
    .hide().slideDown({duration:200})
    .get(0);
}

lab321.email.set_return = function(popup_reply_data) {
    if(!popup_reply_data.selection_list) { //single mode
        set_return(popup_reply_data);
        lab321.email.refreshNotifyTo($('form[name="'+popup_reply_data.form_name+'"]'));
        return;
    }
    var first_name_index = -1;
    var last_name_index = -1;
    var viewList = $(lab321.email.popupWindow.document).find('body.popupBody form#MassUpdate table.list.view');
    viewList.find('td.selectCol').closest('tr').children('td, th').each(function(iCol) {
        $(this).find('a').each(function() {
            if(this.href && this.href.match(/ORDER_BY=first_name&?/)) {
                first_name_index = iCol;
            }
            else if(this.href && this.href.match(/ORDER_BY=last_name&?/)) {
                last_name_index = iCol;
            }
        });
    });
    $('form[name="'+popup_reply_data.form_name+'"] .editlistitem > input.relate_id[value=""]').closest('.editlistitem').remove();
    for(var i in popup_reply_data.selection_list) {
        var user_id = popup_reply_data.selection_list[i];
        var full_name = user_id;
        if(first_name_index >= 0 && last_name_index >= 0) {
            var first_name = '';
            var last_name = '';
            var cols = viewList.find('input.checkbox[value="'+user_id+'"]').closest('tr').children('td, th');
            if(!cols.length) {
                continue;
            }
            if(cols.get(first_name_index)) {
                first_name = $(cols.get(first_name_index)).text().trim();
            }
            if(cols.get(last_name_index)) {
                last_name = $(cols.get(last_name_index)).text().trim();
            }
            if(first_name || last_name) {
                full_name = first_name + ' ' + last_name;
            }
        }
        var item = lab321.email.cloneRecipientField($('form[name="'+popup_reply_data.form_name+'"] .item_template'));
        $(item).find('.relate_id').val(user_id).end().find('.relate_name').val(full_name);
        lab321.email.refreshNotifyTo(form[name="'+popup_reply_data.form_name+'"]);
    }
}
