jQuery(function()
 {
    var mk_pages_list = jQuery('#mk_pages_list');
    mk_pages_list.multiselect({
        listWidth: 1000,
        searchBoxText: appparams.searchBoxText,
        checkAllText: appparams.checkAllText,
        uncheckAllText: appparams.uncheckAllText,
        invertSelectText: appparams.invertSelectText
    });
    jQuery('#logged_allowed_operations').multiselect({
        listWidth: 1000,
        searchBoxText: appparams.searchBoxText,
        checkAllText: appparams.checkAllText,
        uncheckAllText: appparams.uncheckAllText,
        invertSelectText: appparams.invertSelectText
    });
    jQuery('#ban_user_ids').multiselect({
        listWidth: 1000,
        searchBoxText: appparams.searchBoxText,
        checkAllText: appparams.checkAllText,
        uncheckAllText: appparams.uncheckAllText,
        invertSelectText: appparams.invertSelectText
    });
    jQuery('#nonlogged_allowed_operations').multiselect({
        listWidth: 1000,
        searchBoxText: appparams.searchBoxText,
        checkAllText: appparams.checkAllText,
        uncheckAllText: appparams.uncheckAllText,
        invertSelectText: appparams.invertSelectText
    });
 });