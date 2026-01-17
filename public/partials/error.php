<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'Error - Contract Not Available', 'skinny-moo-contract-builder' ); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: #fff;
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .error-icon {
            font-size: 64px;
            color: #c41230;
            margin-bottom: 20px;
        }
        h1 {
            color: #1a1a1a;
            font-size: 24px;
            margin: 0 0 15px 0;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin: 0 0 20px 0;
        }
        a {
            color: #c41230;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">&#9888;</div>
        <h1><?php esc_html_e( 'Contract Not Available', 'skinny-moo-contract-builder' ); ?></h1>
        <p><?php echo esc_html( $message ); ?></p>
        <p><?php printf(
            esc_html__( 'If you believe this is an error, please contact us at %s.', 'skinny-moo-contract-builder' ),
            '<a href="mailto:' . esc_attr( SMCB_COMPANY_EMAIL ) . '">' . esc_html( SMCB_COMPANY_EMAIL ) . '</a>'
        ); ?></p>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
