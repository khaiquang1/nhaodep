<?php 
//error_reporting(0);	
session_start();
include '../connection.php'; 

if(!isset($_SESSION['username']))
{
		 			?>
					<script>window.location.href = "index.php";</script>
					<?php
					}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Báo cáo đăng ký</title>
    <!-- add the jQuery script -->
    <script src="//code.jquery.com/jquery-1.12.3.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script src="http://ajax.aspnetcdn.com/ajax/jquery.ui/1.9.2/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" type="text/css" />
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap/latest/css/bootstrap.css" />
     
    <!-- Include Date Range Picker -->
    <script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
	<script type="text/javascript">
        $(document).ready(function() {
            var table = $('#example').DataTable({
                "order": [[ 4, "desc" ]],
                dom: 'Brtip',// f : filter
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5'
                ]
            });
             // Apply the search
            table.columns().every( function () {
                var that = this;
                $( 'input', this.footer() ).on( 'keyup change', function () {
                
                    if(this.name != "daterange"){
                   
                        if ( that.search() !== this.value ) {
                            that
                                .search( this.value )
                                .draw();
                        }
                        else{
                            return false;
                    //     conresole.log(this.value);
                        }
                    }
                });
            });
            $('input[name="daterange"]').daterangepicker(
            {
                locale: {
                  format: 'YYYY-MM-DD',
                   "cancelLabel": "Clear",
                },
                startDate: '2018-01-01',
                endDate: '2018-12-30'
                //autoUpdateInput: false,
            }
            ); 
            $("#daterange").on('apply.daterangepicker', function(ev, picker) {

                  $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                  table.draw();
            });

            $("#daterange").on('cancel.daterangepicker', function(ev, picker) {
                  $(this).val('');
                  table.draw();
            });
            // $( "#min, #max" ).datepicker({
            //      dateFormat:'yy-mm-dd',
            // });
            // $('#min, #max').keyup( function() {
            //         table.draw();
            // });n
        });
        $.fn.dataTableExt.afnFiltering.push(
            function( oSettings, aData, iDataIndex ) {
                var grab_daterange = $("#daterange").val();
                if(typeof grab_daterange != 'undefined'){
                    var give_results_daterange = grab_daterange.split(" - ");
                    var filterstart = give_results_daterange[0];
                    var filterend = give_results_daterange[1];
                    var iStartDateCol = 4; //using column 2 in this instance
                    var iEndDateCol = 4;
                    var tabledatestart = aData[iStartDateCol].substring(0,10);
                    var tabledateend= aData[iEndDateCol].substring(0,10);
                    console.log(tabledatestart);
                    if ( !filterstart && !filterend )
                    {
                        return true;
                    }
                    else if ((moment(filterstart).isSame(tabledatestart) || moment(filterstart).isBefore(tabledatestart)) && filterend === "")
                    {
                        return true;
                    }
                    else if ((moment(filterstart).isSame(tabledatestart) || moment(filterstart).isAfter(tabledatestart)) && filterstart === "")
                    {
                        return true;
                    }
                    else if ((moment(filterstart).isSame(tabledatestart) || moment(filterstart).isBefore(tabledatestart)) && (moment(filterend).isSame(tabledateend) || moment(filterend).isAfter(tabledateend)))
                    {
                        return true;
                    }
                    return false;
                }
            }
        );
       
    </script>
	<style type="text/css">
	body{margin:0;padding:0}
	.header{
		font-family: Arial;
		background: #101010;
		color: #FFF;
		padding: 3px 5px;
		margin-bottom: 20px;
		box-shadow: 1px 6px 21px #999;
	}
	.export ul{
		list-style-type: none;
		margin:0;
		padding:10px;
		
	}
	.export li{
		display:inline;
		margin-right:5px;
	}
	.main{
        width:100%;
		padding:0px 10px;
	}
    table{
        width:100%;
    }
	</style>
</head>
<body>
<?php
	require_once("../connection.php");
	dbConnect();	
	$rows = getdata();
    
?>
	<div class="header">
		<h1>Control Panel</h1>
		<p>Thông tin đăng ký</p>
	</div>
	<div class="main">
        <div class="table-responsive">
		<table class="table table-striped table-bordered"  id="example" class="display" cellspacing="0">
        <thead>
            <tr class="heading">
                <th>Họ Và tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>utm_source</th>
                <!-- <th>utm_medium</th>
                <th>utm_campaign</th> -->
                <th>Ngày tạo</th>
            </tr>
            
        </thead>
        <tfoot>
            <tr class="filter">
	            
                <td><input type="text" placeholder="Tìm theo họ và tên " /></td>
                <td><input type="text" placeholder="Tìm theo Email " /></td>
                <td><input type="text" placeholder="Tìm theo Sđt " /></td>
                <td><input type="text" placeholder="Tìm theo utm_source " /></td>
                <!-- <td><input type="text" placeholder="Tìm theo utm_medium" /></td>
                <td><input type="text" placeholder="Tìm theo utm_campaign"></td> -->
                <td><input type="text" name="daterange" id="daterange"></td>
            </tr>
        </tfoot>
        <tbody>
        	<?php
            foreach($rows as $row)
            {
            ?>
            <tr>
                <td><?php echo $row['name'] ?></td>
                <td><?php echo $row['email'] ?></td>
                <td><?php echo $row['phone'] ?></td>
                <td><?php echo $row['utm_source'] ?></td>
                <td><?php echo $row['date_create'] ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>
	</div>
</body>

</html>