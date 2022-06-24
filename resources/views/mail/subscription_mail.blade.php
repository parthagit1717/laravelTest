<!DOCTYPE html>
<html>
   <head>
      <title>ONEPATCH CONNECT : EKM EBAY | Subscription Mail</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
       
      <style type="text/css">
        /*table, td, th {  
          border: 1px solid #ddd;
          text-align: left;
        }

        table {
          border-collapse: collapse;
          width: 100%;
        }

        th, td {
          font-size: 15px;
          padding: 10px;
        }
*/

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
          height: 60px;
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
        <div style="/*width:620px;*/background-color: #a4508b;background-image: linear-gradient(326deg, #a4508b 0%, #5f0a87 74%); /*padding: 0px 10px;*/ border:1px solid #dcd7d7; height:75px;">
            <div style="float: none; text-align: center; margin-top: 0px; background:url('{{ URL::to('#') }}') repeat center center">              
              <img src="http://54.78.239.1:8084/assets/images/brand/logo-2.png" style="margin-top:margin-top:15px;" width="120"height="40px" alt="">
             <!--  <p>Onepatch Connect :: Ebay EKM</p> -->
            </div>
            <div style="float: right;   text-align: center; margin-top: 0px;">
              <p style="margin-right: 5px;"> {{@$data['today']}}</p>
            </div>
          </div>
          
          
         <div style="max-width:700px; border:1px solid #dcd7d7; margin:0 0; padding:15px;  ">
            <h1 style="font-family:Arial; font-size:16px; font-weight:500; /*color:#8ccd56;*/ margin:5px 0 12px 0;">Dear {{@$data['name']}},</h1>
            <div style="display:block; overflow:hidden; width:100%; margin-left: 25px;  margin-bottom: 10px;">
              <p style="font-family:Arial; font-size:14px; font-weight:500; color:#000;">
                Welcome to Onepatch Connect :: Ebay EKM , You have successfully subscribe <span style="color:#ff5500">{{@$data['subname']}}</span>.<br> <a href="{{@$data['receipt_url']}}" style="text-decoration: none;" target="_blank">View receipt </a>
              </p> 

              <div style="width:40%"> 
                <div class="card"> 
                  <div style="width: 608px; background-color: #990099;padding: 2px 5px;color: white; text-align: center;font-size: 17px;">
                    <P>Subscription details</P>
                  </div>
                  <table id="customers">
                    <!-- <tr>
                      <th>Subscription details</th> 
                      <th></th>
                    </tr> --> 
                       
                    <tr style="background-color: #f2f2f2;">
                      <td>Subscription Name </td>
                      <td>{{@$data['subname']}}</td>
                    </tr>
                    <tr>
                      <td>Subscription End Date</td>
                      <td>{{ date('d-M-Y', strtotime($data['subs_end'])) }}</td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                      <td>Payment ID</td> 
                      <td>{{@$data['payment_id']}} </td>
                    </tr>
                    <tr>
                      <td>Subscription Price</td> 
                      <td>{{@$data['amount']}} {{@$data['currency']}}</td>
                    </tr> 
                       
                  </table>
                </div>  
               </div>
              
            </div> 
            
            
            <p style=" font-family:Arial; font-size:14px; font-weight:500; color:#363839;margin: 0px 0px 10px 0px;">Cheers,</p>
            <p style=" font-family:Arial; font-size:14px; font-weight:500; color:#363839;margin: 0px 0px 10px 0px;">Team Onepatch Connect :: Ebay EKM.</p>
             
         </div>
         <div style="/*width:620px;*/background-color: #a4508b;background-image: linear-gradient(326deg, #a4508b 0%, #5f0a87 74%); /*padding: 0px 10px;*/ border:1px solid #dcd7d7;color: white;">
            <div id="sub-footer">
                <div class="row footer_row">
                    <div class="column">Onepatch Connect :: Ebay EKMs© 2021. All Rights Reserved.</div>
                    <div class="column">info@OPConnect.com</div>
                    <div class="column">22, Lorem ipsum dolor, consectetur adipiscing.<br>Mob:- (541) 754-3010</div>
                </div>
            </div>
         </div>
      </div>
   </body>
</html>
