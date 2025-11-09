<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Partha Task"> 
    <meta name="keywords" content="admin,admin dashboard,admin panel,admin template,bootstrap,clean,dashboard,flat,jquery,modern,responsive,premium admin templates,responsive admin,ui,ui kit.">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/images/brand/favicon.ico')}}" />

    <!-- TITLE -->
    <title>Login</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/dark-style.css" rel="stylesheet" />
    <link href="assets/css/transparent-style.css" rel="stylesheet">
    <link href="assets/css/skin-modes.css" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="assets/css/icons.css" rel="stylesheet" />

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="assets/colors/color1.css" />
    <style type="text/css">
        .wrap-login100{
            width: 400px;
          }
          body {
          background-image:url({{url('assets/images/brand/backgound.png')}})
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
        <!-- /GLOABAL LOADER -->

        <!-- PAGE -->
        <div class="page">
            <div class="">

                <!-- CONTAINER OPEN -->
                <div class="col col-login mx-auto mt-7">
                    <div class="text-center"> 
                    </div> 
                </div>
                @if(session()->has('success'))
                    <div class="col-lg-12 col-md-6 center" style="display: flex; justify-content: center; margin-top: 10px;">
                        <p class="alert alert-success col-lg-10" id="success-alert" style="font-size: 16px; background-color: #70db70; color: white;"><span class="badge badge-pill badge-success" style="background-color:  #1f7a1f;"> Success !</span>
                        <span id="msg">{{ session()->get('success') }} </span>
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" style="float: right;">×</a>
                        </p>
                    </div>
                 @endif
                
               

                @if(session()->has('error')) 
                <div class="col-lg-12 col-md-6 center" style="display: flex; justify-content: center; margin-top: 10px;">
                    <p class="alert alert-danger col-lg-5" id="danger-alert" style="font-size: 16px; background-color:#ff9999"><span class="badge badge-pill badge-danger" style="background-color: #cc0000"> Error !</span>
                        <span id="msg" >
                        {{ session()->get('error') }}</span>
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" style="float: right;">×</a>
                    </p> 
                </div>
                @endif
                <div class="container-login100">

                    <div class="wrap-login100 p-6">
                        
                        <form class="login100-form validate-form" method="POST" action="{{ route('login') }}">
                        @csrf

                        <span class="login100-form-title pb-5">
                            Login
                        </span>
                        <div class="panel panel-primary">
                            <div class="panel-body tabs-menu-body p-0 pt-5">
                                <div class="tab-content">
                                    <div class="tab-pane active col-lg-12" id="tab5">
                                        <div class="wrap-input100 validate-input input-group" data-bs-validate="Valid email is required: ex@abc.xyz">
                                            <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                                <i class="zmdi zmdi-email text-muted" aria-hidden="true"></i>
                                            </a>
                                            <input class="input100 border-start-0 form-control ms-0 @error('email') is-invalid @enderror" type="email" placeholder="Email" name="email" value="{{ old('email') }}">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="wrap-input100 validate-input input-group" id="Password-toggle">
                                            <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                                <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                                            </a>
                                            <input class="input100 border-start-0 form-control ms-0 @error('password') is-invalid @enderror" name="password" type="password" placeholder="Password">
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="text-end pt-4">
                                            <p class="mb-0"><a href="{{route('user.password.request')}}" class="text-primary ms-1">Forgot Password?</a></p>
                                        </div>
                                        <div class="container-login100-form-btn">
                                            <button type="submit" class="login100-form-btn btn-primary">Login</button>
                                        </div>
                                        <div class="text-center pt-3">
                                            <p class="text-dark mb-0">Not a member?<a href="{{ route('register') }}" class="text-primary ms-1">Sign UP </a></p>
                                        </div>

                                        <div class="form-group row mt-3">
                                        <div class="col-md-12">
                                            <a href="{{ route('google.redirect') }}" 
                                               style="display: block; text-align: center; padding: 10px 15px; 
                                                      border-radius: 4px; text-decoration: none; 
                                                      color: #fff; background-color: #DB4437; border: 1px solid #DB4437; 
                                                      font-weight: bold;">
                                                <svg style="width:16px; height:16px; vertical-align: middle; margin-right: 8px;" 
                                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                                                    <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 7.999-11.303 7.999c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.158 8.067 3.03l5.657-5.657C34.046 6.091 29.051 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                                                    <path fill="#FF3D00" d="M6.306 14.691L1.139 9.809C3.263 5.922 7.749 3.535 12.875 3.535c3.059 0 5.842 1.158 8.067 3.03l5.657-5.657C34.046 6.091 29.051 4 24 4C16.318 4 9.658 7.391 6.306 14.691z"/>
                                                    <path fill="#4CAF50" d="M24 44c5.15 0 9.844-1.921 13.386-5.021l-5.657-5.657c-2.225 1.872-5.008 3.03-8.067 3.03c-5.223 0-9.654-3.342-11.303-7.999H4.389C6.429 37.957 14.639 44 24 44z"/>
                                                    <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-1.092 3.111-2.883 5.824-5.064 7.712l-5.657 5.657C36.88 34.092 42.133 28.534 43.611 20.083z"/>
                                                </svg>
                                                Continue with Google
                                            </a>
                                        </div>
                                    </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
                <!-- CONTAINER CLOSED -->
            </div>
        </div>
        <!-- End PAGE -->

    </div>
    <!-- BACKGROUND-IMAGE CLOSED -->

    <!-- JQUERY JS -->
    <script src="assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <!-- <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script> -->

    <!-- SHOW PASSWORD JS -->
    <script src="assets/js/show-password.min.js"></script>

    <!-- GENERATE OTP JS -->
    <script src="assets/js/generate-otp.js"></script>

    <!-- Perfect SCROLLBAR JS-->
    <script src="assets/plugins/p-scroll/perfect-scrollbar.js"></script>

    <!-- Color Theme js -->
    <script src="assets/js/themeColors.js"></script>

    <!-- CUSTOM JS -->
    <script src="assets/js/custom.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>

</html>