<?php
include 'connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Oswald:wght@200..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Slab:wght@100..900&display=swap');
        /* font-family: "Kanit", sans-serif; */
        :root {
            --bg1: #18150d;  /* Primary dark background */
            --bg2: #eae3c2;  /* Light secondary background */
            --body: #a39623; /* Body accent */
            --brand: #cbb90f; /* Brand gold */
            --white: #fff;    /* White for text */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Kanit", sans-serif;
            background: var(--bg2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 1.5s ease;
        }

        .popup {
            background: var(--bg1);
            border-radius: 20px;
            border: 3px solid var(--bg1);
            padding-top:60px;
            width:35%;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            animation: popupAnimation 1.2s ease;
            position: relative;
        }

        .popup h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 20px;
        }

        .popup p {
            font-size: 20px;
            font-weight: 500;
            color: var(--body);
            margin-bottom: 50px;
        }

        .popup .button {
            display: inline-block;
            padding: 15px 40px;
            border-radius: 30px;
            background: linear-gradient(145deg, var(--brand), var(--body));
            color: var(--white);
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.3s ease, background 0.3s ease;
            box-shadow: 0 5px 20px rgba(203, 185, 15, 0.4);
        }

        .popup .button:hover {
            background: linear-gradient(145deg, var(--body), var(--brand));
            transform: translateY(-3px);
        }

        .popup .button:active {
            transform: translateY(0);
        }

        /* Golden Checkmark */
        .checkmark {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--body), var(--brand));
            margin: 0 auto 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 30px rgba(203, 185, 15, 0.3);
            position: relative;
        }

        .checkmark i {
            font-size: 50px;
            color: var(--white);
            line-height: 1;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes popupAnimation {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .popup {
                padding: 40px 30px;
            }

            .popup h1 {
                font-size: 24px;
            }

            .popup p {
                font-size: 15px;
            }

            .popup .button {
                padding: 12px 30px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .popup {
                padding: 30px 20px;
            }

            .popup h1 {
                font-size: 20px;
            }

            .popup p {
                font-size: 14px;
            }

            .popup .button {
                padding: 10px 25px;
                font-size: 14px;
            }

            .checkmark {
                width: 80px;
                height: 80px;
            }

            .checkmark i {
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="overlay">
        <div class="popup">
            <div class="checkmark">
                <i>✔</i>
            </div>
            <h1>Order Placed Successful</h1>
            <p>Your Order Has Been Placed Successfully. Thank you for Shooping With us..!!!</p>
            <!-- <a href="../eshop.php" class="button">Continue Shopping</a> -->
        </div>
    </div>

    <script>
        setTimeout(function(){
            window.location.href = 'order.php';
        }, 3000);
    </script>
</body>
</html>