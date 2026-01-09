<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', env('APP_NAME'))</title>

    {{-- Web Fonts --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&family=Montserrat:wght@300;400;500&display=swap"
        rel="stylesheet">

    <style>
        /* RESET STYLES */
        body {
            margin: 0;
            padding: 0;
            min-width: 100%;
            width: 100% !important;
            height: 100% !important;
            background-color: #F7F7F7;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0 auto;
        }

        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            display: block;
        }

        a {
            text-decoration: none;
            color: #C29C75;
        }

        p {
            margin: 0;
            font-family: 'Montserrat', Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 24px;
            color: #1C1B1B;
            font-weight: 300;
        }

        h1,
        h2,
        h3 {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 500;
            color: #1C1B1B;
            margin: 0;
        }

        /* GMAIL COMPATIBILITY */
        .ExternalClass {
            width: 100%;
        }

        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
            line-height: 100%;
        }

        #outlook a {
            padding: 0;
        }

        /* MOBILE STYLES */
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
            }

            .content-padding {
                padding: 20px !important;
            }

            .mobile-stack {
                display: block !important;
                width: 100% !important;
            }

            .mobile-center {
                text-align: center !important;
            }

            .h1-mobile {
                font-size: 28px !important;
                line-height: 36px !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #F7F7F7;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#F7F7F7">
        <tr>
            <td align="center" style="padding: 40px 0;">

                {{-- MAIN CONTAINER --}}
                <table role="presentation" class="container" width="600" border="0" cellspacing="0" cellpadding="0"
                    bgcolor="#FFFFFF" style="width: 600px; max-width: 600px;">

                    {{-- HEADER --}}
                    <tr>
                        <td align="center" style="padding: 40px 0 30px 0; border-bottom: 1px solid #EAEAEA;">
                            <img src="{{ asset('images/firm-logo.png') }}" alt="{{ env('APP_NAME') }}" width="180"
                                style="width: 180px; height: auto;">
                        </td>
                    </tr>

                    {{-- BRONZE ACCENT LINE --}}
                    <tr>
                        <td height="4" bgcolor="#C29C75" style="line-height: 1px; font-size: 1px;">&nbsp;</td>
                    </tr>

                    {{-- CONTENT AREA --}}
                    <tr>
                        <td class="content-padding" style="padding: 50px 40px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td bgcolor="#1C1B1B" style="padding: 40px; text-align: center;">
                            <p
                                style="color: #FFFFFF; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px;">
                                Stay Connected
                            </p>
                            <table role="presentation" align="center" border="0" cellspacing="0" cellpadding="0"
                                style="margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 0 10px;">
                                        <a href="#" style="color: #C29C75; font-size: 12px;">INSTAGRAM</a>
                                    </td>
                                    <td style="padding: 0 10px; color: #444;">|</td>
                                    <td style="padding: 0 10px;">
                                        <a href="#" style="color: #C29C75; font-size: 12px;">FACEBOOK</a>
                                    </td>
                                    <td style="padding: 0 10px; color: #444;">|</td>
                                    <td style="padding: 0 10px;">
                                        <a href="#" style="color: #C29C75; font-size: 12px;">WEBSITE</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="color: #888888; font-size: 11px; line-height: 18px;">
                                &copy; {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.<br>
                                Luxurious escapes curated for the modern traveler.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>
