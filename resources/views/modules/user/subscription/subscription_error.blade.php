@extends('layouts.app2')
@section('title') Subscription @endsection
 
 
@section('content')

<!--app-content open-->
<div class="main-content app-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Subscription</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Subscription</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- Row -->
            <div class="row">
                <div class="col-md-12 col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Subscriptions</h4>
                        </div>
                        <div class="card-body"> 
                          <section class="content">
                            <div class="container-fluid"> 
                              @include('includes.message')
                              <div class="card"> 
                                  <div class="card-body">
                                    <div style="background: #ccfff5; ">
                                      <div style="text-align: center; font-size: 150px; color: #990099; font-weight: bold;">
                                        <i class="fa fa-ban" aria-hidden="true"></i>
                                      </div>
                                      
                                      <h2 style="text-align: center;">You have no subscription plan please subscribe.</h2> 
                                      <center><a href="{{route('subPlanList')}}" class="btn btn-info btnsubs">Subscribe</a></center>
                                    </div> 
                                                     
                                  </div> 
                                <!-- <div class="card-footer">Footer</div> -->
                              </div> 
                            </div><!-- /.container-fluid -->
                          </section> 
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
@endsection

 

 





 