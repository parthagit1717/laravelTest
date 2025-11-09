<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Partha Task"> 
     

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/images/brand/favicon.ico')}}" />

    <!-- TITLE -->
    <title>Reset Password</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="../assets/css/style.css" rel="stylesheet" />
    <link href="../assets/css/dark-style.css" rel="stylesheet" />
    <link href="../assets/css/transparent-style.css" rel="stylesheet">
    <link href="../assets/css/skin-modes.css" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="../assets/css/icons.css" rel="stylesheet" />

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="../assets/colors/color1.css" />
    <style type="text/css">
        .wrap-login100 {
                width: 400px;
            }
        
    </style>

</head>

<body class="app sidebar-mini ltr">

    <!-- BACKGROUND-IMAGE -->
    <div class="login-img">

        <!-- GLOABAL LOADER -->
        <div id="global-loader">
            <img src="../assets/images/loader.svg" class="loader-img" alt="Loader">
        </div>
        <!-- End GLOABAL LOADER -->

        <!-- PAGE -->
        <div class="page">
            <div class="">

                <!-- CONTAINER OPEN -->
                <div class="col col-login mx-auto">
                    <div class="text-center">
                        
                    </div>
                </div>

                @if(session()->has('success'))
                    <div class="col-lg-6 col-md-6 center" style="display: flex; justify-content: center; margin-top: 10px;">
                        <p class="alert alert-success col-lg-10" id="success-alert" style="font-size: 16px; background-color: #70db70; color: white;"><span class="badge badge-pill badge-success" style="background-color:  #1f7a1f;"> Success !</span>
                        <span id="msg">{{ session()->get('success') }} </span>
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" style="float: right;">×</a>
                        </p>
                    </div>
                 @endif
                
               

                @if(session()->has('error')) 
                <div class="col-lg-6 col-md-6 center" style="display: flex; justify-content: center; margin-top: 10px;">
                    <p class="alert alert-danger col-lg-5" id="danger-alert" style="font-size: 16px; background-color:#ff9999"><span class="badge badge-pill badge-danger" style="background-color: #cc0000"> Error !</span>
                        <span id="msg" >
                        {{ session()->get('error') }}</span>
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" style="float: right;">×</a>
                    </p> 
                </div>
                @endif

                <!-- CONTAINER OPEN -->
                <div class="container-login100">
                    <div class="wrap-login100 p-6">
                         <form class="login100-form validate-form" method="POST" action="{{ route('user.password.update') }}">
                            @csrf
                            <span class="login100-form-title">
                                Reset Password
                            </span>
                            <input type="hidden" name="token" value="{{ $token }}">
                             
                            <div class="wrap-input100 validate-input input-group" data-bs-validate="Valid email is required: ex@abc.xyz">
                                <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                    <i class="zmdi zmdi-email" aria-hidden="true"></i>
                                </a>
                                <input class="input100 border-start-0 ms-0 form-control" type="email" placeholder="Email" name="email" value="{{$email}}" readonly autocomplete="email">                                 
                            </div>

                            <div class="wrap-input100 validate-input input-group" id="Password-toggle">
                                <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                    <i class="zmdi zmdi-eye" aria-hidden="true"></i>
                                </a>
                                <input class="input100 border-start-0 ms-0 form-control @error('password') is-invalid @enderror" type="password" placeholder="Password" name="password" value="{{ old('password') }}">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="wrap-input100 validate-input input-group" id="Password-toggle2">
                                <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                    <i class="zmdi zmdi-eye" aria-hidden="true"></i>
                                </a>
                                <input class="input100 border-start-0 ms-0 form-control @error('password_confirm') is-invalid @enderror" type="password" placeholder="Confirm Password" name="password_confirm" value="{{ old('password_confirm') }}">
                                @error('password_confirm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="container-login100-form-btn">
                                <button  type="submit" class="login100-form-btn btn-primary">Submit</button>
                            </div>
                             
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--END PAGE -->

    </div>
    <!-- BACKGROUND-IMAGE CLOSED -->

    <!-- JQUERY JS -->
    <script src="../assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="../assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- SHOW PASSWORD JS -->
    <script src="../assets/js/show-password.min.js"></script>

    <!-- Perfect SCROLLBAR JS-->
    <script src="../assets/plugins/p-scroll/perfect-scrollbar.js"></script>

    <!-- Color Theme js -->
    <script src="../assets/js/themeColors.js"></script>

    <!-- CUSTOM JS -->
    <script src="../assets/js/custom.js"></script>

</body>

</html>