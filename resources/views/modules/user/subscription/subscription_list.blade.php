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
                          @include('includes.message')
                          @if($user->subs_id==null)
                            <h4 style="color: #ff00ff;">You have no subscription please choose your plan .</h4>
                          @elseif($subsend < $today)
                            <h4 style="color: #ff661a;">Your subscription plan is over please choose your plan .</h4>
                          @endif
                          <div class="row">
                            @foreach($subdata as $displaysub)
                              <div class="col-md-4" style="margin-bottom: 20px;">
                                  <div class="card-header cardsty">
                                    @if($user->subs_id == $displaysub->id && $subsend > $today)
                                    <span class="badge bg-info badge-sm  me-1 mb-1 mt-1">Active</span>
                                    @endif
                                    {{@$displaysub->sub_title}}
                                  </div>
                                  <div class="card-body" style="background:#f2f2f2">
                                    <p>{{@$displaysub->sub_desc}}</p>
                                    <p>{{@$displaysub->sub_vali}} Days</p>
                                    <p>{{@$displaysub->sub_price}} INR</p>
                                  </div> 
                                  <div class="card-footer cardsty">
                                    @if($user->subs_id == $displaysub->id && $subsend > $today)
                                      <small style="color:#0000ff;">Subscription end in {{ @$subendtate->days }} days.</small>

                                    @elseif($today >= $subsend || $user->subs_id==null)
                                      <center><a href="{{route('add_subs',['user_id'=>$user->id,'sub_id'=>$displaysub->id])}}" class="btn btn-sm btn-secondary bubsbtn">Go with {{@$displaysub->sub_title}}</a></center>
                                    @else
                                      <center><a href="javascript:void(0)" class="btn bubsbtn" style=" pointer-events: auto! important; cursor: not-allowed! important;">Go with {{@$displaysub->sub_title}}</a></center>    
                                    @endif
                                  </div>
                              </div>
                            @endforeach                
                          </div> 
                             
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

 
 





 