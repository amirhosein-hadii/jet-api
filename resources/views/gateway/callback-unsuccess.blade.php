<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    <title>رسید تراکش داپ اَپ</title>
    <style>
        *{
            margin: 0px;
            padding: 0px;
        }
        body {
            overflow-x: hidden;
            direction: rtl;
            text-align: right;
            background-color: #eee;
        }
        main{
            margin: 0px auto;
        }
        .payment{
            padding: 10px 0px;
            color: white;
            background-color: #bc3520;
            width: 100%;
            text-align: center;
            border-radius: 10px 10px 0px 0px;

        }
        .footer-text{
            text-align: center;
            color: #104633;
            font-size: 18px;
            font-weight: bold;
            padding: 0px 10px;

        }
        .logo-box{
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-box img{
            margin-left: 10px;
        }
        header{
            background-color: grey;
            color: white;
            width: 100%;
            text-align: center;
            padding: 10px 0px;
            margin-bottom: 20px;
        }
        .transaction{
            padding: 10px 0px;
            width: 100%;
            text-align: center;
            background-color: #e2ef14a6;
        }
        .button-style{
            display: flex;
            padding: 8px 15px;
            background-color: #858282;
            border: 1px #181817;
            color: white;
            margin:45px 5px;
        }
        .table-box{
            width: calc(100% - 30px);
            flex-direction: column;
            display: flex;
            margin: 0px auto;


        }
        .table .number{
            text-align: center
        }
        .box-shadow{
            box-shadow: 0px 7px 15px 0px rgba(0, 0, 0, 0.2);
        }


    </style>
</head>
<body>

<main>
    <div class="title-box">
        <header>
            <h5>جزئیات تراکنش شما</h5>
        </header>


        <div class="container">
            <div class="col-xs-6 col-sm-6 col-md-6 text-right">
                <!--      <p>-->
                <!--          <em><strong>تاریخ</strong> : 1398/05/06</em>-->
                <!--      </p>-->
                <!--      <p>-->
                <!--          <em> <strong> شماره سفارش</strong> :  34522677 </em>-->
                <!--      </p>-->
            </div>
            <div class="table-box">
                <div class="payment">
                    <h5>پرداخت ناموفق</h5>
                </div>
                <div class="transaction ">
                    <h5>رسید تراکنش داپ اَپی شما</h5>
                </div>
                <table class="table table-striped box-shadow" >
                    <tr>
                        <td><em><strong>آیدی مرجع</strong></em></td>
                        <td class="number">{{ $refId }}</td>
                    </tr>
                    <tr style="background-color: #c8e5263d;">
                        <td><em><strong>آیدی سفارش</strong></em></td>
                        <td class="number">{{ $orderId }}</td>
                    </tr>
                    <tr>
                        <td><em><strong>شماره مرجع خرید</strong></em></td>
                        <td class="number">{{ $saleReference }}</td>
                    </tr>
                    @if(isset($cardNumber) && !is_null($cardNumber))
                        <tr style="background-color: #c8e5263d;">
                            <td >  <em><strong>شماره کارت بانکی</strong></em></td>
                            <td class="number">{{ $cardNumber }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td >  <em><strong>مبلغ پرداخت شده</strong></em></td>
                        <td class="number">{{number_format($amount)}}  <span>تومان</span></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class='footer-text'>
                                <a href="{{ $deepLink }}">
                                    <button type='button' class='button-style' onclick="window.location.href='{{ $deepLink }}';">
                                        بازگشت به برنامه
                                    </button>
                                </a>
                                <P >شما پس از    <span id="t"></span> ثانیه به برنامه داپ اَپ باز خواهید گشت</P>


                                <div style="text-align: center;">
                                    <address>
                                        <strong>آدرس ما:</strong>
                                        <br>
                                        تهران ، خیابان ولیعصر
                                        <br>
                                        تقاطع فاطمی ، کوچه افتخاری نیا پلاک ۵۲
                                        <br>
                                        <abbr > تلفن : </abbr>  ۸۸۸۹۳۵۸۸ (۰۲۱)
                                    </address>
                                </div>

                            </div>
            </div>
            <div class="logo-box">

                <img src="https://images.daapapp.com/banks/logo/c8e3fbc4b940d4ad6d3eb4991b7305f5.png" width="42px" alt="">
                <img src="https://daapapp.com/Assets/Images/logo.png" width="25px" alt="">
            </div>
            </td>
            </tr>

            </table>

        </div>
    </div>


    <div class="container">

    </div>





</main>


<script>
    var count=16;
    function co(){
        count--;


        if(count<=0){
            $(location).attr("href",'{{ $deepLink }}');
            // $(window).attr('location','{{ $deepLink }}')
              //  window.location.href="https://www.google.com/";
            count=0;

        }
        document.getElementById("t").innerHTML=count;
    }
    setInterval(co,1500);
</script>

<script>
    function color(){
        var co=["green","blue",];
        var n1=Math.floor(Math.random()*2);

        document.getElementById("t").style.color=co[n1]

    }
    setInterval(color,100);
</script>

</body>
</html>
