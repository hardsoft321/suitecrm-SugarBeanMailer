{**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author Evgeny Pervushin <pea@lab321.ru>
 * @package SugarBeanMailer
 *}

{literal}
<style>
#NotificationCopy .editlistitem {margin-top: 3px}
#NotificationCopy > #notify_recipient_add {margin-top: 3px}
</style>
{/literal}

<script type="text/javascript" src="{sugar_getjspath file="custom/include/NotificationCopy/NotificationCopy.js"}"></script>

{assign var=idname value="notification_copy"}
<span id="NotificationCopy">
<p>{sugar_translate label="LBL_NOTIFY_ON_CREATE"}</p>
<button type="button" id="notify_recipient_add"
    title="{sugar_translate label="LBL_ID_FF_ADD"}"
    onclick="lab321.email.cloneRecipientField($(this).closest('td').find('.item_template'))">
  <img src="{sugar_getimagepath file="id-ff-add.png"}">
</button>

<script type="text/template" class="item_template">
    <input type="text" name="{$idname}[template][name]" value="" autocomplete="off" class="relate_name" readonly="readonly">
    <input type="hidden" name="{$idname}[template][id]" value="" class="relate_id">
    <span class="id-ff multiple">
    <button type="button" name="btn_{$idname}[template][name]" class="button firstChild"
      title="{sugar_translate label="LBL_SELECT_BUTTON_TITLE"}"
      onclick='lab321.email.popupWindow = open_popup("Users", 600, 400, "&email_advanced=%", true, false,
        {ldelim}"call_back_function":"lab321.email.set_return","form_name":"{$formName}","field_to_name_array":{ldelim}"id":"{$idname}[template][id]","name":"{$idname}[template][name]"{rdelim}{rdelim},
        "MultiSelect",true);'><img src="{sugar_getimagepath file="id-ff-select.png"}"></button>
    <button class="id-ff-remove button lastChild" type="button"
      title="{sugar_translate label="LBL_ID_FF_REMOVE"}"
      onclick="$(this).closest('.editlistitem').remove()"><img src="{sugar_getimagepath file="id-ff-remove-nobg.png"}"></button>
    </span>
</script>
</span>

<script type="text/javascript">
SUGAR.util.doWhen("document.readyState == 'complete' && typeof lab321 != 'undefined' && typeof lab321.email != 'undefined' && typeof lab321.email.cloneRecipientField != 'undefined'", function() {ldelim}
    lab321.email.cloneRecipientField($('form[name="{$formName}"] .item_template'));
{rdelim});
</script>
