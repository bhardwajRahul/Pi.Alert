<?php
  //------------------------------------------------------------------------------
  // check if authenticated
  require_once  $_SERVER['DOCUMENT_ROOT'] . '/php/templates/security.php';
?>

<!-- ----------------------------------------------------------------------- -->
 

<!-- Datatable Session -->
<table id="tableSessions" class="table table-bordered table-hover table-striped ">
    <thead>
    <tr>
    <th><?= lang('DevDetail_SessionTable_Order');?></th>
    <th><?= lang('DevDetail_SessionTable_Connection');?></th>
    <th><?= lang('DevDetail_SessionTable_Disconnection');?></th>
    <th><?= lang('DevDetail_SessionTable_Duration');?></th>
    <th><?= lang('DevDetail_SessionTable_IP');?></th>
    <th><?= lang('DevDetail_SessionTable_Additionalinfo');?></th>
    </tr>
    </thead>
</table>


<script>

  var parSessionsRows     = 'Front_Details_Sessions_Rows';
  var sessionsRows        = 10;
  var period              = '1 month';

  function initializeSessionsDatatable () {
    // Sessions datatable
    $('#tableSessions').DataTable({
      'paging'      : true,
      'lengthChange': true,
      'lengthMenu'   : [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']],
      'searching'   : true,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false,
      'order'       : [[0,'desc'], [1,'desc']],

      // Parameters
      'pageLength'  : sessionsRows,

      'columnDefs'  : [
          {visible:   false,  targets: [0]},

          // Replace HTML codes
          {targets: [1,2,3,5],
            'createdCell': function (td, cellData, rowData, row, col) {
              $(td).html (translateHTMLcodes (cellData));
          } }
      ],

      // Processing
      'processing'  : true,
      'language'    : {
        processing: '<table><td width="130px" align="middle"><?= lang("DevDetail_Loading");?></td>'+
                    '<td><i class="ion ion-ios-loop-strong fa-spin fa-2x fa-fw">'+
                    '</td></table>',
        emptyTable: 'No data',
        "lengthMenu": "<?= lang('Events_Tablelenght');?>",
        "search":     "<?= lang('Events_Searchbox');?>: ",
        "paginate": {
            "next":       "<?= lang('Events_Table_nav_next');?>",
            "previous":   "<?= lang('Events_Table_nav_prev');?>"
        },
        "info":           "<?= lang('Events_Table_info');?>",
      }
    });
  }

  function loadSessionsData(){
    $('#tableSessions').DataTable().ajax.url('php/server/events.php?action=getDeviceSessions&mac=' + getMac() +'&period='+ period).load();
  }

  initializeSessionsDatatable();
  loadSessionsData();

</script>