@extends('layouts.app2')
@section('title') Manage Transaction @endsection
@section('links')
@include('includes.links') 
<style>
@media only screen and (min-width:576px) {
    a#add-new-post {float: right;}
   }
 </style>
 <style type="text/css">
    .paginate_button {
    background-color: blue;
    }
 </style>
@endsection
 
@section('content')

<!--app-content open-->
<div class="main-content app-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Manage Transaction</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manage Transaction</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                          <div class="row">
                            <div class="col-sm-12">
                              <h3 class="card-title">Transaction List</h3>
                            </div>
                            
                            <!-- <div class="col-sm-6" style="float: right;">
                              <a href="javascript:void(0)" class="btn btn-info" id="add-new-post">Add Subscription <i class="fa fa-plus-circle"></i></a>  
                            </div> -->
                          </div>
                        </div>
                        <div class="card-body">
                            <p class="alert alert-success" id="msgdiv" style="display: none; font-size: 16px;"><span class="badge badge-pill badge-secondary mr-2" style="background-color: #00e673"> Success ! </span> <span id="msg" style="color:black;">sss</span><a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>

                            <p class="alert alert-primary" id="msgdiv2" style="display: none; background:#ffb3d9; font-size: 16px;"><span class="badge badge-pill badge-secondary mr-2" style="background-color: #cc0066;"> Status ! </span><span id="msg2" style="color: black;"></span><a href="#" class="close" data-dismiss="alert" aria-label="close">×</a></p>

                            <!-- For User only.... -->  
                            <table class="table table-striped table-bordered dt-responsive nowrap" style="width:100%; font-size: 15px;" id="laravel_datatable">
                                <thead>
                                    <tr>
                                       <th>ID</th>
        				                       <th>Payment Id</th> 
        				                       <th>User Email</th>
                                       <th>Amount (INR)</th>
        				                       <th>Card Brand</th>
                                       <th>Date & Time</th> 
        				                       <th>Status</th> 
                                    </tr>
                                </thead>
                          </table>
                        </div>
                    </div>
                </div> 
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER END -->
    </div>
</div>
<!--app-content close-->



<div class="modal fade" id="ajax-crud-modal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="postCrudModal"></h4>
            <span  id="modelclose" style="cursor: pointer; color: red;"><i class="fa fa-times-circle" aria-hidden="true"></i></span>

        </div>
        <div class="modal-body">
            <form id="postForm" name="postForm" class="form-horizontal" enctype="multipart/form-data">
               {{ csrf_field() }}
               <input type="hidden" name="subs_id" id="subs_id">
                <div class="form-group">
                    <label for="name" class="col-sm-4 control-label">Subscription Name</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="name" id="name" placeholder="Subscription Name" value="">
                      <span class="text-danger" id="nameError"></span>   
                    </div>
                                 
                </div>
     
                <div class="form-group">
                    <label class="col-sm-6 control-label">Subscription Description</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="sub_desc" id="sub_desc" placeholder="Subscription Description" aria-label="clarice@example.com" value="">
                      <span class="text-danger" id="subdescError"></span>
                    </div>
                </div>

                 <div class="form-group">
                    <label class="col-sm-6 control-label">Subscription Validity(Days)</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="sub_vali" id="sub_vali" placeholder="30 or 90 etc..." aria-label="clarice@example.com" value="">
                      <span class="text-danger" id="subvaliError"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-6 control-label">Subscription Price(INR)</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="sub_price" id="sub_price" placeholder="Subscription Price(INR)" aria-label="clarice@example.com" value="">
                      <span class="text-danger" id="subpriceError"></span>
                    </div>
                </div>

                   

                <div class="col-sm-offset-2 col-sm-10">
                 <button type="submit" class="btn btn-primary add_tb_row" id="btn-save" value="create">Save
                 </button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            
        </div>
    </div>
  </div>
</div>




@endsection

@section('script')
 <script>
$(document).ready( function () {
   $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $('#laravel_datatable').DataTable({
      "processing": true,
      "serverSide": true,
        "ajax":{
                 "url": "{{ url('alltransaction') }}",
                 "dataType": "json",
                 "type": "get",
                 "data":{ _token: "{{csrf_token()}}"}
               },
               
        "columns": [
            { "data": "id" },
            { "data": "payment_id" }, 
            { "data": "user_email" },
            { "data": "amount" },
            { "data": "card_brand" },
            { "data": "created_at" },
            { "data": "payment_status" }
            
        ] 
  });  


  $('#add-new-post').click(function () {
    $('#btn-save').val("create-post");
    $('#post_id').val('');
    $('#postForm').trigger("reset");
    $('#postCrudModal').html("Add New Subscription");
    $('#ajax-crud-modal').modal('show');
    var url= 'public/asset/dist/img/avatar5.png';
      $('#imagefields').attr('src',url);
  });

  $('#modelclose').click(function (a) {
    $('#postForm').trigger("reset");
    $('#ajax-crud-modal').modal('hide'); 
  });

  $('#ajax-crud-modal').on('hide.bs.modal', function() { 
        $('#nameError').text('');
        $('#emailError').text('');
        $('#passwordError').text('');
        $('#conpasswordError').text('');
        $('#imageError').text('');
        $('.add_tb_row').html('Save');
    }); 
   

  $('body').on('click', '.edit-post', function () {
  var post_id = $(this).data('id');
  $.get('editsubscription/'+post_id, function (data) {
      $('#name-error').hide();
      $('#email-error').hide();
      $('#postCrudModal').html("Edit Subscription");
      $('#btn-save').val("edit-post");
      $('#ajax-crud-modal').modal('show');
      $('#subs_id').val(data.id);
      $('#name').val(data.sub_title); 
      $('#sub_desc').val(data.sub_desc); 
      $('#sub_vali').val(data.sub_vali); 
      $('#sub_price').val(data.sub_price); 
        
  })
});

   
 
});
  
</script> 

 
</script> 
 
@endsection

 





 