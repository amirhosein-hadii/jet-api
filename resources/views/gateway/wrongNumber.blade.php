<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسید تراکش داپ اَپ</title>
<script>

function goBack () {
  window.history.back()
}
function goForward () {
  window.history.forward()
}

</script>
    <style>
        *{
            margin: 0px;
            padding: 0px;
        }
      body{
          background-color: rgb(245,249,252);
      }
      header{
          text-align: center;
      }
      .main{
          margin: 10% auto;

      }
    .exclaim{
        font-size: 50px;
    color: white;
    background-color: rgb(208,51,44);
    border-radius: 50%;
    padding: 10px 30px;
    }
    #p1{
        margin-top: 35px;
    }

    th {
        background-color: rgb(208,51,44);
        color: white;
        padding: 8px;
        border-radius: 8px 8px 0px 0px;
        font-size: 22px;
    }
    .table-container p{
        direction: rtl;
        text-align: center;
        padding: 14px;
        font-size: 16px;
        color: #4E525A;

    }
    .table-container{
        width: calc(100% - 40px);
        margin: 25px auto;
        padding: 10px;
        display: flex;
    justify-content: center;
    }
    h4{
        text-align: center;
        padding-top: 10px;
    }
    .btn-style{
        padding: 8px 0px;
        border: 1px solid red;
        color: red;
        width: calc(100% - 50px );
        margin: 0px auto;
        font-size: 18px;
        border-radius: 8px;

    }
    form{
        width: 100%;
        text-align: center;
    }
.button-holder{
    width: 100%;
    margin-top: 10%;
    text-align: center;
}
table{
    box-shadow: 0px 1px 1px 2px rgba(0,0,0,.2);
    border-radius: 8px;

}
    </style>
</head>
<body>
    <div class="main">
        <header>
            <span class="exclaim">!</span><br>
            <p id="p1">شماره کارت وارد شده صحیح نمی باشد </p>
        </header>
        <div class="table-container">
<table>
    <tr>
      <th>رسید تراکش داپ اَپ</th>
    </tr>
    <tr>
            <td style="background-color: white;">
                <h4 > ! کاربر عزیز </h4>
             <p>
               شماره کارتی که برای احراز هویت وارد کرده اید صحیح نمی باشد.
                لطفا مجدد تلاش کنید.
             </p>
             <p>
                مبلغ کسر شده تا دقایقی دیگر به حساب شما باز خواهد گشت
             </p>
             </td>
    </tr>
    <tr>
  </table>
        </div>

   <div class="button-holder">
      
            <button class="btn-style" onclick="goBack()">
                بازگشت به برنامه
            </button>
      
   </div>
        </div>
    </div>
</body>
</html>
