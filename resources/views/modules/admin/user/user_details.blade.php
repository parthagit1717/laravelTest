@extends('layouts.app2')
@section('title') User Details @endsection
 
 
@section('content')

<!--app-content open-->
<div class="main-content app-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">User Details</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Details</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-1 -->
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 ">
                    <div class="row">
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
                                                <h3>User Name:- <span style="color: blue;">{{ $user->name }}</span></h3>
                                              </div>
                                              <div class="col-sm-6">
                                                <a href="{{ route('manage.user') }}" class="btn btn-primary" id="add-new-post"><i class="bi bi-arrow-left"></i> Back </a> 
                                              </div>
                                            </div> 
                                          </div>
                                            <div class="card-body">
                                              <div class="row">
                                                <div class="col-sm-6">
                                                  <p><strong>Name:-</strong> {{ $user->name }} </p>
                                                  <p><strong>Email:-</strong> {{ $user->email }} </p>
                                                  <p><strong>Subscription Plan:-</strong> 
                                                    @if(@$user->subs_id != null)
                                                    {{ $user->getUserSubscription->sub_title }} 
                                                    @else 
                                                      <span style="color:#e600ac;">No subscription plan active.</span>
                                                    @endif
                                                  </p>
                                                  @if(@$user->subs_id && @$user->subs_end != null)
                                                    <p><strong>Subscription Plan End On :-</strong> {{ \Carbon\Carbon::parse($user->subs_end)->format('d-M-Y')}} </p>
                                                  @endif
                                                </div>

                                                <div class="col-sm-6"> 
                                                  <span> <strong> Profile Image :- </strong></span><img src="{{$user->image ? asset('storage/images/user_image/'.$user->image) : asset('public/asset/dist/img/avatar5.png') }}" class="brround" alt="User" />
                                                </div>

                                              </div> 
                                              
                                            </div> 
                                          <!-- <div class="card-footer">Footer</div> -->
                                        </div>
                                        <input type="hidden" name="user_id" value="{{$user->id}}" id="user_id"> 
                                      </div><!-- /.container-fluid -->
                                    </section>
                                </div>
                            </div>
                        </div>
                         
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

 





 