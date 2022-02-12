jQuery(document).ready(function ($) {
    var el = $('#jqGrid1');
    if (!el.length) {
        return;
    }

    el.jqGrid({
        //styleUI      : 'Bootstrap',
        url: SimpleAffiliateHistoryGrid.history_grid_url,
        datatype: 'json',
        colModel: [
            {
                label: 'ID',
                name: 'id',
                key: true,
                editable: false,
                editrules: {
                    required: true
                },
                hidden: true
            },
            {
                label: SimpleAffiliateHistoryGrid.text_order_total,
                name: 'order_amount',
                editable: false,
                sortable: false
            },
            {
                label: SimpleAffiliateHistoryGrid.text_status,
                name: 'order_status',
                editable: false,
                sortable: false
            },
            {
                label: SimpleAffiliateHistoryGrid.text_ordered_at,
                name: 'ordered_at',
                editable: false,
                sortable: false
            },
            {
                label: SimpleAffiliateHistoryGrid.text_credited,
                name: 'total',
                editable: false,
                sortable: false
            }
        ],
        sortname: 'ordered_at',
        sortorder: 'asc',
        loadonce: false,
        viewrecords: true,
        height: 200,
        rowNum: 10,
        pager: '#jqGridPager1'
    });

    el.navGrid('#jqGridPager1',
        {
            edit: false,
            add: false,
            del: false,
            search: false,
            refresh: true,
            view: false,
            position: "left",
            cloneToTop: false
        },
        // options for the Edit Dialog
        {
            editCaption: "The Edit Dialog",
            recreateForm: true,
            checkOnUpdate: true,
            checkOnSubmit: true,
            closeAfterEdit: true,
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            }
        },
        // options for the Add Dialog
        {
            closeAfterAdd: true,
            recreateForm: true,
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            }
        },
        // options for the Delete Dialog
        {
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            }
        }
    );


    $(window).bind('resize', function () {
        el.setGridWidth($('.woocommerce-MyAccount-content').width());
    });

    el.setGridWidth($('.woocommerce-MyAccount-content').width());
});