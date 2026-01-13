<style>
    @page {
        margin: 0;
    }

    html,
    body {
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #1C1B1B;
        background-color: #FFFFFF;
    }

    .page {
        padding: 30px 40px;
        box-sizing: border-box;
        position: relative;
    }

    .page-break {
        page-break-after: always;
    }

    /* Typography */
    h1 {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 32px;
        font-weight: normal;
        font-style: italic;
        color: #C29C75;
        margin: 0 0 5px 0;
        line-height: 1.1;
    }

    h2 {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 18px;
        font-weight: normal;
        text-transform: uppercase;
        letter-spacing: 3px;
        color: #1C1B1B;
        margin: 0 0 8px 0;
    }

    .header-title {
        margin-bottom: 20px;
    }

    .hotel-address {
        font-size: 10px;
        color: #777;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
        text-transform: uppercase;
    }

    .section-title {
        font-family: Georgia, serif;
        font-size: 18px;
        color: #1C1B1B;
        margin-bottom: 12px;
        border-bottom: 1px solid #EAEAEA;
        padding-bottom: 6px;
    }

    /* Images */
    .hotel-image-main {
        width: 100%;
        height: auto;
        max-height: 300px;
        object-fit: cover;
        display: block;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .logo-tm {
        width: 110px;
        height: auto;
        display: block;
    }

    /* Sidebar Components */
    .sidebar {
        padding-left: 20px;
    }

    .confirm-block {
        background-color: #C29C75;
        padding: 12px;
        margin-bottom: 15px;
        color: #FFFFFF;
        text-align: center;
    }

    .confirm-label {
        display: block;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 3px;
        opacity: 0.9;
    }

    .confirm-value {
        display: block;
        font-family: Georgia, serif;
        font-size: 16px;
        font-weight: bold;
    }

    .info-block {
        background-color: #F9F7F3;
        padding: 12px;
        margin-bottom: 10px;
        border-left: 2px solid #C29C75;
    }

    .info-label {
        display: block;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #C29C75;
        margin-bottom: 3px;
        font-weight: bold;
    }

    .info-value {
        display: block;
        font-family: Georgia, serif;
        font-size: 13px;
        color: #1C1B1B;
    }

    .date-container {
        margin-bottom: 15px;
    }

    .date-box {
        padding: 8px 0;
        border-bottom: 1px solid #EAEAEA;
    }

    .date-label {
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #888;
        margin-bottom: 2px;
        display: block;
    }

    .date-value {
        font-family: Georgia, serif;
        font-size: 14px;
        color: #1C1B1B;
        display: block;
    }

    /* Lists and Perks */
    ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    ul li {
        padding: 4px 0;
        border-bottom: 1px solid #F0F0F0;
        font-size: 11px;
        color: #444;
        position: relative;
        padding-left: 12px;
    }

    ul li:before {
        content: "—";
        position: absolute;
        left: 0;
        color: #C29C75;
    }

    .perks-container {
        margin-top: 20px;
    }

    /* Pricing Section */
    .pricing-section {
        margin-top: 20px;
        background-color: #F9F7F3;
        padding: 15px;
    }

    .pricing-table {
        width: 100%;
    }

    .pricing-header {
        font-family: Georgia, serif;
        font-size: 20px;
        color: #C29C75;
        margin-bottom: 10px;
        font-style: italic;
    }

    .pricing-row td {
        padding: 4px 0;
        font-size: 12px;
    }

    .pricing-label {
        color: #777;
    }

    .pricing-value {
        text-align: right;
        color: #1C1B1B;
        font-weight: bold;
    }

    .total-row td {
        padding-top: 12px;
        padding-bottom: 12px;
        /* Extra spacing for commission line */
        border-top: 1px solid #E5E0D8;
        font-size: 16px;
        font-family: Georgia, serif;
    }

    .total-label {
        color: #1C1B1B;
    }

    .total-value {
        color: #C29C75;
        text-align: right;
    }

    .commission-row td {
        padding: 12px 0 4px 0;
        /* Extra spacing from total price */
        color: #C29C75;
        font-weight: bold;
        font-style: italic;
        border-top: 1px dashed #C29C75;
    }

    /* Contact and Footer */
    .contact-info {
        margin-top: 20px;
        font-size: 11px;
        color: #777;
        line-height: 1.4;
    }

    .contact-title {
        font-weight: bold;
        color: #1C1B1B;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    .footer-logo {
        margin-top: 10px;
    }

    /* Welcome Page Styles (Used in Client Confirmation) */
    .welcome-title {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 36px;
        font-weight: normal;
        font-style: italic;
        color: #C29C75;
        margin-bottom: 20px;
    }

    .welcome-text {
        font-family: Georgia, serif;
        font-size: 14px;
        line-height: 1.6;
        color: #444;
    }

    .welcome-signature {
        margin-top: 30px;
        font-family: Georgia, serif;
        font-size: 14px;
        color: #C29C75;
        font-style: italic;
    }

    .welcome-image-cell {
        padding-left: 30px;
        vertical-align: middle;
    }

    .welcome-image {
        width: 100%;
        height: auto;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
</style>