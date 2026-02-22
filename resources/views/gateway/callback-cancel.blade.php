<html lang="en">
<head>
    <link href="https://db.onlinewebfonts.com/c/52ce4de2efeeb8b18dcbd379711224f3?family=B+Yekan" rel="stylesheet" type="text/css"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انصراف تراکش داپ اَپ</title>

    <style>
        @import url(https://db.onlinewebfonts.com/c/52ce4de2efeeb8b18dcbd379711224f3?family=B+Yekan);
        @font-face {font-family: "B Yekan"; src: url("https://db.onlinewebfonts.com/t/52ce4de2efeeb8b18dcbd379711224f3.eot"); src: url("https://db.onlinewebfonts.com/t/52ce4de2efeeb8b18dcbd379711224f3.eot?#iefix") format("embedded-opentype"), url("https://db.onlinewebfonts.com/t/52ce4de2efeeb8b18dcbd379711224f3.woff2") format("woff2"), url("https://db.onlinewebfonts.com/t/52ce4de2efeeb8b18dcbd379711224f3.woff") format("woff"), url("https://db.onlinewebfonts.com/t/52ce4de2efeeb8b18dcbd379711224f3.ttf") format("truetype"), url("//db.onlinewebfonts.com/t/52ce4de2efeeb8b18dcbd379711224f3.svg#B Yekan") format("svg"); }

        *{
            margin: 0px;
            padding: 0px;
        }
        body{
            background-color: rgb(245,249,252);
            font-family:"B Yekan" !important ;
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
            padding: 6px 36px;
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
            padding: 8px ;
            border: 1px solid red;
            color: red;
            font-size: 18px;
            border-radius: 8px;
            text-decoration: none;
            width: calc(100% - 50px);
            background-color: white;
        }



        form{
            width: 100%;
            text-align: center;
        }
        .button-holder{
            width: 100%;
            margin-top: 32px;
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

    </header>
    <div class="table-container">
        <table>
            <tr>
                <th>انصراف از تراکنش</th>
            </tr>
            <tr>
                <td style="background-color: white;">
                    <h4 > ! کاربر عزیز </h4>
                    <p>
                        شما از انجام تراکنش انصراف داده اید
                    </p>

                </td>
            </tr>
            <tr>
        </table>
    </div>

    <div class="button-holder">
        <a href="{{ $deepLink }}">
            <button class="btn-style" onclick="window.location.href='{{ $deepLink }}';">
                بازگشت به برنامه
            </button>
        </a>
    </div>
</div>
</div>
</body>
</html>
