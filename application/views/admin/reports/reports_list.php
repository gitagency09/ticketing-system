<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/dist-assets/css/plugins/datatables.min.css') ?>" />
<link href="<?php echo base_url('assets/libs/jquery-ui/jquery-ui.min.css'); ?>" rel="stylesheet" type="text/css">


<div class="">
    <div class="float-left breadcrumb">
        <h1 class="mr-2">Monthly Report</h1>
    </div>
    <div class="float-right">
        <a class="btn btn-primary export" type="button" href="<?php echo site_url('reports/export'); ?>">Export</a>
    </div>
    <div class="clearfix"></div>
</div>

<div class="separator-breadcrumb border-top"></div>


<?php $this->load->view('common/flashmsg'); ?>


<div class="row mb-12 search_div">

    <div class="col-md-12 ">
        <p>Search</p>
    </div>
    <!-- <div class="col-md-3 mb-3"><input class="form-control" id=" " type="text" placeholder="Company Name"></div> -->
<input class="form-control" id="ticket_no" type="hidden" placeholder="Ticket No.">
    <!-- <div class="col-md-3 mb-3"><input class="form-control" id="ga_no" type="text" placeholder="GA Number"></div> -->
    <!-- <div class="col-md-3 mb-3"><input class="form-control" id="company" type="text" placeholder="Client Name"><input class="form-control" id="company_id" type="hidden"></div> -->
    <div class="col-md-3 mb-3">
        <select class="form-control" id="company" >
            <option value="">Select Company</option>
            <?php
                foreach ($company as $ckey => $cvalue) {
                    echo '<option value="'.$cvalue['id'].'">'.$cvalue['name'].'</option>';
                }
            ?>
        </select>
    </div>
    <div class="col-md-3 mb-3">
    <select class="form-control" id="year">
        <option value="">Select Year</option>
        <?php
        $currentYear = date('Y');
        for ($year = $currentYear; $year >= 2000; $year--) { // Adjust as needed
            echo '<option value="' . $year . '">' . $year . '</option>';
        }
        ?>
    </select>
</div>

<div class="col-md-3 mb-3">
    <select class="form-control" id="month">
        <option value="">Select Month</option>
        <?php
        for ($month = 1; $month <= 12; $month++) {
            echo '<option value="' . $month . '">' . date('F', mktime(0, 0, 0, $month, 1)) . '</option>'; // Month names
        }
        ?>
    </select>
</div>
<div class="col-md-3 mb-3">
    <input type="text" class="form-control datepicker" id="from_date" name="from_date" placeholder="From Date">
</div>
<div class="col-md-3 mb-3">
    <input type="text" class="form-control datepicker" id="to_date" name="to_date" placeholder="To Date">
</div>


    <!-- <div class="col-md-3 mb-3">
        <select class="form-control" id="complaint_type">
            <option value="">Select Ticket Type</option>
            <?php
        	//$complaint_types = complaint_types();
        	//foreach ($complaint_types as $key => $value) {
        		//echo '<option value="'.$key.'">'.$value.'</option>';
        	//}
        ?>
        </select>
    </div> -->

    <!-- <div class="col-md-3 mb-3">
        <select class="form-control" id="status">
            <option value="">Select Status</option>
            <?php
		$url_status = (isset($_GET['status'])) ? $_GET['status'] :'';
        $status_list = complaint_status_list();
        	foreach ($status_list as $key => $value) {
        		if($key == $url_status && $url_status != ''){
        			echo '<option value="'.$key.'" selected>'.$value.'</option>';
        		}else{
	        		echo '<option value="'.$key.'">'.$value.'</option>';
        		}
        	}
        ?>
        </select>
    </div> -->
    <div class="col-md-12"><button class="btn btn-primary float-right" id="search_filter">Search</button></div>

</div>


<!--row-->
<div class="row mb-12">
    <div class="col-md-12 mb-3">
        <div class="">
            <div class="">
                <div class="table-responsive">
                    <table class="table table-striped" id="zero_configuration_table" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col">Sr. No.</th>
                                <th scope="col">Ticket No.</th>
                                <th scope="col">Client Name</th>
                                <th scope="col">Ticket Type</th>
                                <th scope="col">Description</th>
                                <?php if($this->role == 'employee') { ?>
                                <th scope="col abc">Action Taken</th>
                                <?php } ?>
                                <th scope="col">Status</th>
                                <!-- <th scope="col">Assigned By</th> -->
                                <!-- <th scope="col">Created By</th> -->
                                <th scope="col">Created Date</th>
                                <th scope="col">Update on Ticket</th>
                                <th scope="col">Completed Date</th>
                                <th scope="col">Days</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>




<?php $this->load->view('common/footer');  ?>
<script src="<?php echo base_url('assets/dist-assets/js/plugins/datatables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/libs/jquery-ui/jquery-ui.min.js') ?>"></script>
<script src="<?php echo base_url('assets/custom.js'); ?>"></script>

<script type="text/javascript">
$(document).ready(function() {

    var status_list = <?php echo json_encode($status_list );?>;

    //   	var ga_no_list =<?php echo json_encode($projects );?>;
    // $( "#ga_no" ).autocomplete({
    //      		source: ga_no_list,
    //      		 focus: function( event, ui ) {
    //                  $( "#ga_no" ).val( ui.item.value );
    //                    return false;
    //               },
    //      		select: function( event, ui ) {
    //      			console.log(ui.item);
    //      			$( "#ga_no" ).val( ui.item.value );
    //                return false;
    //            }
    //    });

    // Autocomplete for Company Name
    // $('#company').autocomplete({
    //     source: function(request, response) {
    //         $.ajax({
    //             url: "<?php echo site_url('reports/get_companies'); ?>", // Endpoint to fetch company names
    //             dataType: "json",
    //             data: {
    //                 term: request.term // Query term
    //             },
    //             success: function(data) {
    //                 response(data); // Pass the data to autocomplete
    //             }
    //         });
    //     },
    //     minLength: 1, // Minimum characters to trigger the autocomplete
    //     select: function(event, ui) {
    //         $('#company').val(ui.item.label); // Set the selected company
    //         $('#company_id').val(ui.item.value); // Set the selected company
    //         return false;
    //     }
    // });

    var table = $('#zero_configuration_table').DataTable({
        'processing': true,
        "oLanguage": {
            'sProcessing': '<div class="dt_spinner"> <span class="spinner spinner-primary mr-3"></span></div>'
        },
        "stripeClasses": [],
        "lengthMenu": [5, 10, 15, 20, 25],
        "pageLength": 5,
        "sDom": 'lrtip',
        "bInfo": true,
        "searching": true,
        "ordering": false,
        "columnDefs": [{
            "defaultContent": "-",
            "targets": "_all"
        }],
        serverSide: true,
        ajax: {
            url: "<?php echo site_url('reports/list') ?>",
            // dataSrc :'data',
            dataFilter: function(data) {
                console.log(data);
                var json = jQuery.parseJSON(data);
                json.recordsTotal = json.data.totalRecords;
                json.recordsFiltered = json.data.totalRecordwithFilter;

                json.data = json.data.aaData;

                return JSON.stringify(json); // return JSON string
            },
            data: function(d) {
                return $.extend({}, d, {
                    "company_id": 	$("#company").val().toLowerCase(),
                    "ticket_no": $("#ticket_no").val().toLowerCase(),
                    "complaint_type": $('#complaint_type').find(':selected').val(),
                    "status": $("#status").val(),
                    //"classification": $("#classification").val(),
                    "action": $("#action").val(),
                    "year": $("#year").val(),   // Send Year filter
                    "month": $("#month").val(), // Send Month filter
                    "from_date": $("#from_date").val(),
                    "to_date": $("#to_date").val(),
                });
            }
        },

        columns: [{
                "render": function(data, type, full, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                data: 'ticket_no'
            },
            //{ data: 'ga_no' },
            {
                data: 'company'
            },
            {
                data: 'complaint_type'
            },
            {
                data: 'description',
                    "render": function(data, type, full, meta) {
                        if (!data) return ''; // Handle empty data

                        let shortText = data.length > 50 ? data.substring(0, 50) + '...' : data;
                        return `<span title="${data}" style="cursor: pointer;" data-toggle="tooltip">${shortText}</span>`;
                }
            },
            //{ data: 'classification' },
            <?php if($this->role == 'employee') { ?> {
                data: 'action_taken'
            },
            <?php } ?> {
                "render": function(data, type, full, meta) {
                    switch (full.status) {
                        case '0':
                            $class = 'badge-danger ';
                            break; //deleted
                        case '1':
                            $class = 'badge-success ';
                            break; //completed
                        case '2':
                            $class = 'badge-info ';
                            break; //open
                        case '3':
                            $class = 'badge-warning ';
                            break; //ongoing
                        case '4':
                            $class = 'badge-success ';
                            break; //closed
                        default:
                            $class = '';
                            break;
                    }

                    $html = '<span class="badge ' + $class + '">' + status_list[full.status] +
                        '</span>';
                    return $html;
                }
            },

            // {
            //     data: 'assigned_emp'
            // },
            // {
            //     data: 'created_by'
            // },
            {
                data: 'created_at'
            },
            {
                data: 'updated_at'
            },
            {
                data: 'completed_at'
            },
            {
                data: 'days',
                    "render": function(data, type, full, meta) {
                        let colorClass = full.completed_at ? '' : 'text-danger'; // Apply red color if completed_at is empty
                        return '<span class="' + colorClass + '">' + data + '</span>';
                }
            },
        ],
        "drawCallback": function(settings) {
            stopLoading($('#search_filter'));
        },
    });

    $('#search_filter').click(function() {
        showLoading($(this));
        table.draw();
    });

    $('.export').click(function(e) {
        e.preventDefault();
        $url = $(this).attr('href');
        var params = {
            //ga_no : $("#ga_no").val().toLowerCase(), 
            ticket_no: $("#ticket_no").val().toLowerCase(),
            complaint_type: $('#complaint_type').val(),
            status: $("#status").val(),
            company_id: $("#company").val().toLowerCase(),
            //classification : $("#classification").val(),
            action: $("#action").val(),
            year: $("#year").val(),   // Send Year filter
            month: $("#month").val(), // Send Month filter
        };

        $url = $url + '?' + $.param(params);
        window.location.href = $url;
    });
});

$(".datepicker").datepicker({
    dateFormat: 'yy-mm-dd'
});
</script>