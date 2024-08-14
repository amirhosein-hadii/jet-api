<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    {{--{!! $res !!}--}}
    <style>
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 120px;
            height: 120px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <title>در حال انتقال به درگاه بانک</title>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-4"></div>
        <div class="col-sm-4 text-center">
            <img src="https://daapapp.com/Assets/Images/logo.png" style="width: 15%" alt="">

            <h3 style="color: #1c7430" class="mt-4">در حال انتقال به درگاه بانک</h3>
        </div>
        <div class="col-sm-4"></div>
    </div>
    <div class="row">
        <div class="col-sm-4"></div>
        <div class="col-sm-4 text-center">
            <div class=" loader" style="margin-left: 35%;margin-top: 10rem"></div>

        </div>
        <div class="col-sm-4"></div>
    </div>
</div>

<form id="b1" name="myform" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" method="POST">
    <input type="hidden" id="RefId" name="RefId" value="{{$refId}}">
</form>
<script>
    document.getElementById("b1").submit();

</script>
</body>
</html>
