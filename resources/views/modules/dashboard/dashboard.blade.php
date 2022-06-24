@extends('layouts.app2')
@section('title') Dashboard @endsection
 
 
@section('content')

<!--app-content open-->
<div class="main-content app-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-1 -->
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 ">
                    <div class="row">
                        @if(Auth::user()->id==1)
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                <div class="card overflow-hidden">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="mt-2">
                                                <h6 class="">Total Users</h6>
                                                <h2 class="mb-0 number-font">{{ $user->count() }}</h2>
                                            </div>
                                            <div class="ms-auto">
                                                <div class="chart-wrapper mt-1">
                                                    <canvas id="saleschart"
                                                        class="h-8 w-9 chart-dropshadow"></canvas>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            </div> 
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                <div class="card overflow-hidden">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="mt-2">
                                               <h6 class="">Unverified Users</h6>
                                                <h2 class="mb-0 number-font">{{ $unveriuser->count() }}</h2>
                                            </div>
                                            <div class="ms-auto">
                                                <div class="chart-wrapper mt-1">
                                                    <canvas id="saleschart"
                                                        class="h-8 w-9 chart-dropshadow"></canvas>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            </div> 
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                <div class="card overflow-hidden">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="mt-2">
                                                <h6 class="">Active Users</h6>
                                                <h2 class="mb-0 number-font">{{ $activeuser->count() }}</h2>
                                            </div>
                                            <div class="ms-auto">
                                                <div class="chart-wrapper mt-1">
                                                    <canvas id="saleschart"
                                                        class="h-8 w-9 chart-dropshadow"></canvas>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            </div> 
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                <div class="card overflow-hidden">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="mt-2">
                                                <h6 class="">Inactive Users</h6>
                                                <h2 class="mb-0 number-font">{{ $inactiveuser->count() }}</h2>
                                            </div>
                                            <div class="ms-auto">
                                                <div class="chart-wrapper mt-1">
                                                    <canvas id="saleschart"
                                                        class="h-8 w-9 chart-dropshadow"></canvas>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            </div> 
                        @else
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="card overflow-hidden">
                                <div class="card-body">
                                    <section class="content">
                                      <div class="container-fluid"> 
                                        @include('includes.message') 
                                        <div class="card">
                                          <div class="card-header"> 
                                            <div class="row" style="width: 100%;">
                                              <div class="col-sm-6">
                                                <h3>Welcome <span>{{ Auth::user() ? Auth::user()->name : '' }}</span></h3>
                                              </div>
                                              <div class="col-sm-6">
                                                <a href="{{ route('edit_profile') }}" class="btn btn-success" id="add-new-post">Edit Profile <i class="bi bi-pencil-square"></i></a> 
                                              </div>
                                            </div> 
                                          </div>
                                              
                                          <!-- <div class="card-footer">Footer</div> -->
                                        </div>
                                          
                                      </div><!-- /.container-fluid -->
                                    </section>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
            <!-- ROW-1 END --> 
        </div>
        <!-- CONTAINER END -->
    </div>
</div>
<!--app-content close-->



@endsection

 





 