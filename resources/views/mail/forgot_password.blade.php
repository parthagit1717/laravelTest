<!DOCTYPE html>
<html>
   <head>
      <title>Onepatch Connect :: Ebay EKM|Forgot Password</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
      <style>
         #customers {
           font-family: Arial, Helvetica, sans-serif;
           border-collapse: collapse;
           width: 150%;
         }

         #customers td, #customers th {
           border: 1px solid #ddd;
           padding: 8px;
         }

         #customers tr:nth-child(even){background-color: #f2f2f2;}

         #customers tr:hover {background-color: #ddd;}

         #customers th {
           padding-top: 12px;
           padding-bottom: 12px;
           text-align: left;
           background-color:#990099;
           color: white;
         }

         .card {
             margin-top: 16px;
             margin-left: 16px;
             width: 150%;
             /*height: 400px;*/
             /*margin-right: 16px;*/
             background: #FFFFFF 0% 0% no-repeat padding-box;
             box-shadow: 0px 3px 20px #BCBCCB47;
             /*border-radius: 8px;*/
             opacity: 1;
             /*border: 2px solid red;*/
         }

         .header {
             width: 150%;
             height: 40px;
             background: #ECF2F9 0% 0% no-repeat padding-box;
             border-radius: 8px 8px 0px 0px;
         }

         .header h1 {
             text-align: center;
             font-family: 'Noto Sans', sans-serif;
             font-size: 14px;
             letter-spacing: 0;
             color: #4D4F5C;
         }

         .card-table {
           word-break: break-all;
         }

         .column {
          float: left;
          width: 33.33%;
          }
          .footer_row{ 
          /*height: 60px;*/
          margin: 10px 0;
          display: block;
          padding-left: 20px;
           
          }
          .column
          {
          position: relative;
          top:50%;
          transform: translateY(-50%);
          }
         
      </style>
      
   </head>
   <body>
      <div style="max-width:720px; margin:0 auto;">
          <div style="/*width:620px;*/background-color:#ffffff; /*padding: 0px 10px;*/ border:1px solid #dcd7d7; height: 75px;">
            <div style="float: none; text-align: center; margin-top: 0px; background:url('#') repeat center center">              
               <img src="http://54.78.239.1:8084/assets/images/brand/logo-2.png" style="margin-top:10px;" width="270"height="60" alt="">
              <!-- <p>Onepatch Connect :: Ebay EKM</p> -->
            </div>
         </div>
         <div style="max-width:720px; border:1px solid #dcd7d7; margin:0 0; padding:15px; border-top:none;">
            <h1 style="font-family:Arial; font-size:16px; font-weight:500; /*color:#8ccd56;*/ margin:5px 0 12px 0;">Dear {{@$data['name']}},</h1>
            <div style="display:block; overflow:hidden; width:100%;">
               <p style="font-family:Arial; font-size:14px; font-weight:500; color:#000;margin-left: 5px;">
                 It seems that you’ve forgotten your password.
               </p>  
               <div style="display:block;overflow:hidden; width:100%; text-align:center; margin: 0px 0px 10px 0px;">

                   <a href="{{@$data['link']}}" style="font-family:Arial; border-radius:17px;font-size:15px; font-weight:500; color:#FFF; display:inline-block; padding: 7px 12px; background-color: #ffc107;background-image: linear-gradient(326deg, #ffc107 0%, #ffc107 74%); text-decoration:none;">Reset Password</a>
               </div>
               <p style="font-family:Arial; font-size:14px; font-weight:500; color:#000;margin-left: 5px;">
                  If you did not make this request, just ignore this email. Otherwise please click the button above to reset your password.
               </p>           
            </div> 
            
            <p style="font-family:Arial; font-size:14px; font-weight:500; color:#363839;margin: 0px 0px 10px 0px; margin-top: 20px;">Cheers,</p>
            <p style=" font-family:Arial; font-size:14px; font-weight:500; color:#363839;margin: 0px 0px 10px 0px;">Team Onepatch Connect :: Ebay EKM.</p>
            <!-- <p style=" font-family:Arial; font-size:14px; font-weight:500; color:#363839;margin: 0px 0px 10px 0px;">Contact: info@OpwebTracker.com</p> -->
         </div>

         <div style="/*width:620px;*/background-color: #ffffff; /*padding: 0px 10px;*/ border:1px solid #dcd7d7;color: #000;">
            <div id="sub-footer"">
                <div class="row footer_row">
                    <div class="column1" style=" font-family:Arial; font-size:14px; font-weight:500; color:#363839;margin: 0px 0px 10px 0px; text-align: center">Onepatch Connect :: Ebay EKMs © <?php echo date("Y"); ?>. All Rights Reserved.</div>
                    <!-- <div class="column">info@OPConnect.com</div>
                    <div class="column">22, Lorem ipsum dolor, consectetur adipiscing.<br>Mob:- (541) 754-3010</div> -->
                </div>
            </div>
         </div>
      </div>
   </body>
</html>
